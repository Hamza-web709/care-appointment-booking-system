<?php
/**
 * CARE – Patient Dashboard
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('patient', '../login.php');

$base = '../';
$pageTitle = 'Patient Dashboard';

$userId = (int)$_SESSION['user_id'];
$patient = $conn->query("
    SELECT p.*, c.city_name, u.email, u.username
    FROM patients p
    JOIN cities c ON p.city_id = c.id
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = $userId
")->fetch_assoc();
if (!$patient) {
    setFlash('error', 'Patient profile not found. Please complete registration or contact an administrator.');
    header('Location: ../login.php'); exit();
}
$patId = $patient['id'];

$totalAppts     = $conn->query("SELECT COUNT(*) FROM appointments WHERE patient_id=$patId")->fetch_row()[0];
$pendingAppts   = $conn->query("SELECT COUNT(*) FROM appointments WHERE patient_id=$patId AND status='pending'")->fetch_row()[0];
$approvedAppts  = $conn->query("SELECT COUNT(*) FROM appointments WHERE patient_id=$patId AND status='approved'")->fetch_row()[0];
$completedAppts = $conn->query("SELECT COUNT(*) FROM appointments WHERE patient_id=$patId AND status='completed'")->fetch_row()[0];

// Recent appointments
$recentAppts = $conn->query("
    SELECT a.*, d.full_name as doctor_name, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = $patId
    ORDER BY a.appointment_date DESC
    LIMIT 5
");

include '../includes/header.php';
?>
<div class="container-fluid py-4" style="max-width:1200px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div  class="d-flex flex-column">
            <?php if ($patient['profile_image']): ?>
            <img src="../uploads/profiles/<?= e($patient['profile_image']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid var(--care-primary);">
            <?php else: ?>
            <div style="width:56px;height:56px;border-radius:50%;background:var(--care-blue-light);color:var(--care-primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;border:3px solid var(--care-primary);">
                <i class="bi bi-person"></i>
            </div>
            <?php endif; ?>
            <h3 class="fw-800 mb-0 text-white">Welcome, <?= e($patient['full_name']) ?>!</h3>
            
            <p class="text-white small mb-0"><?= e($patient['email']) ?> &bull; <?= e($patient['city_name']) ?></p>
        </div>
        <a href="search_doctors.php" class="btn btn-care">
            <i class="bi bi-search me-1"></i>Find a Doctor
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card primary animate-in">
                <div class="stat-icon primary"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value"><?= $totalAppts ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card orange animate-in">
                <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value text-warning"><?= $pendingAppts ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card teal animate-in">
                <div class="stat-icon teal"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value"><?= $approvedAppts ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card purple animate-in">
                <div class="stat-icon purple"><i class="bi bi-journal-check"></i></div>
                <div class="stat-value"><?= $completedAppts ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="care-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-event me-2"></i>Recent Appointments</span>
                    <a href="appointments.php" class="btn btn-outline-light btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table care-table mb-0">
                            <thead>
                                <tr><th>Doctor</th><th>Specialization</th><th>Date & Time</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($recentAppts->num_rows > 0): while ($a = $recentAppts->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-500"> <?= e($a['doctor_name']) ?></td>
                                    <td><?= e($a['specialization']) ?></td>
                                    <td><?= formatDate($a['appointment_date']) ?><br><small><?= formatTime($a['appointment_time']) ?></small></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No appointments yet. <a href="search_doctors.php">Book one now!</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Links -->
        <div class="col-xl-4">
            <div class="care-card h-100">
                <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
                <div class="card-body d-flex flex-column gap-3 p-3">
                    <?php $links = [
                        ['search_doctors.php','bi-search','Find a Doctor'],
                        ['appointments.php','bi-calendar-check','My Appointments'],
                        ['profile.php','bi-person-circle','My Profile'],
                        ['../doctors.php','bi-people','Browse All Doctors'],
                        ['../diseases.php','bi-virus','Disease Info'],
                        ['../news.php','bi-newspaper','Health News'],
                    ]; foreach ($links as [$url,$icon,$label]): ?>
                    <a href="<?= $url ?>" class="quick-action">
                        <i class="bi <?= $icon ?>"></i>
                        <span><?= $label ?></span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
