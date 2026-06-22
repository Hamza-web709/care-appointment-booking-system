<?php
/**
 * CARE – Patient: My Appointments
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('patient', '../login.php');

$base = '../';
$pageTitle = 'My Appointments';

$userId = (int)$_SESSION['user_id'];
$patient = $conn->query("SELECT id FROM patients WHERE user_id=$userId")->fetch_assoc();
if (!$patient) {
    setFlash('error', 'Patient profile not found. Please contact an administrator.');
    header('Location: ../login.php'); exit();
}
$patId = $patient['id'];

// Cancel appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $id = (int)$_POST['id'];
    // Only allow cancelling if pending or approved (though approved might have policies, keep it simple here)
    $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=? AND patient_id=? AND status IN ('pending', 'approved')");
    $stmt->bind_param('ii', $id, $patId);
    $stmt->execute() ? setFlash('success', 'Appointment cancelled successfully.') : setFlash('error', 'Could not cancel appointment.');
    
    // Optional: free up the slot in availability (complex if we don't store slot_id in appointments, 
    // but in this design we matched by date/time/doctor, so we can find it)
    $appt = $conn->query("SELECT doctor_id, appointment_date, appointment_time FROM appointments WHERE id=$id")->fetch_assoc();
    if ($appt) {
        $upd = $conn->prepare("UPDATE availability SET status='available' WHERE doctor_id=? AND available_date=? AND start_time=?");
        $upd->bind_param('iss', $appt['doctor_id'], $appt['appointment_date'], $appt['appointment_time']);
        $upd->execute();
    }
    header('Location: appointments.php'); exit();
}

$statusFilter = $_GET['status'] ?? '';
$whereClause = $statusFilter ? "AND a.status='$statusFilter'" : '';

$appointments = $conn->query("
    SELECT a.*, d.full_name as doctor_name, d.specialization, c.city_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN cities c ON d.city_id = c.id
    WHERE a.patient_id = $patId $whereClause
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-journal-medical me-2"></i>My Appointments</h1>
        <p class="mb-0 opacity-75">View and manage your bookings</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>

    <!-- Filters -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="appointments.php" class="btn <?= $statusFilter===''?'btn-care':'btn-outline-secondary' ?> btn-sm">All</a>
        <?php foreach (['pending'=>'warning','approved'=>'success','cancelled'=>'danger','completed'=>'primary'] as $s=>$c): ?>
        <a href="?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter===$s?'btn-'.$c:'btn-outline-'.$c ?>">
            <?= ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Appointments List -->
    <div class="row g-4">
        <?php if ($appointments->num_rows > 0): while ($a = $appointments->fetch_assoc()): ?>
        <div class="col-lg-6">
            <div class="care-card h-100 position-relative animate-in <?= $a['status']==='cancelled'?'opacity-75':'' ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><?= e($a['doctor_name']) ?></h5>
                            <span class="badge bg-care"><?= e($a['specialization']) ?></span>
                            <span class="text-muted small ms-2"><i class="bi bi-geo-alt me-1"></i><?= e($a['city_name']) ?></span>
                        </div>
                        <div><?= statusBadge($a['status']) ?></div>
                    </div>
                    
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <span class="text-muted small d-block mb-1"><i class="bi bi-calendar3 me-1"></i>Date</span>
                                <span class="fw-600"><?= formatDate($a['appointment_date']) ?></span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block mb-1"><i class="bi bi-clock me-1"></i>Time</span>
                                <span class="fw-600"><?= formatTime($a['appointment_time']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Problem Description:</span>
                        <p class="mb-0 small"><?= e($a['problem']) ?></p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <small class="text-muted">Booked: <?= formatDate($a['created_at']) ?></small>
                        <?php if (in_array($a['status'], ['pending', 'approved']) && $a['appointment_date'] >= date('Y-m-d')): ?>
                        <form method="POST" class="m-0">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Are you sure you want to cancel this appointment?">
                                <i class="bi bi-x-circle me-1"></i>Cancel Appointment
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="col-12 text-center py-5">
            <div style="font-size:4rem;color:var(--care-border);"><i class="bi bi-calendar-x"></i></div>
            <h5 class="text-white mt-3">No appointments found.</h5>
            <p class="text-white small">You don't have any appointments matching this status.</p>
            <a href="search_doctors.php" class="btn btn-care mt-2">Book an Appointment</a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
