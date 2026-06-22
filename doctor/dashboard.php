<?php
/**
 * CARE – Doctor Dashboard
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('doctor', '../login.php');

$base = '../';
$pageTitle = 'Doctor Dashboard';

// Get doctor record
$doctor = $conn->query("
    SELECT d.*, c.city_name, u.email, u.username
    FROM doctors d
    JOIN cities c ON d.city_id = c.id
    JOIN users u ON d.user_id = u.id
    WHERE d.user_id = " . (int)$_SESSION['user_id']
)->fetch_assoc();

if (!$doctor) {
    setFlash('error', 'Doctor profile not found. Please contact an administrator.');
    header('Location: ../login.php'); exit();
}

$docId = $doctor['id'];

// Appointment stats
$totalAppts    = $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id=$docId")->fetch_row()[0];
$pendingAppts  = $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id=$docId AND status='pending'")->fetch_row()[0];
$completedAppts= $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id=$docId AND status='completed'")->fetch_row()[0];
$availSlots    = $conn->query("SELECT COUNT(*) FROM availability WHERE doctor_id=$docId AND status='available' AND available_date >= CURDATE()")->fetch_row()[0];

// Recent appointments
$recentAppts = $conn->query("
    SELECT a.*, p.full_name as patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = $docId
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 6
");

include '../includes/header.php';
?>
<div class="container-fluid py-4" style="max-width:1200px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3"">
            <?php if ($doctor['profile_image']): ?>
            <img src="../uploads/profiles/<?= e($doctor['profile_image']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid var(--care-primary);">
            <?php else: ?>
            <div style="width:56px;height:56px;border-radius:50%;background:var(--care-blue-light);color:var(--care-primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;border:3px solid var(--care-primary);">
                <i class="bi bi-person"></i>
            </div>
            <?php endif; ?>
            <div>
                <h4 class="fw-bold mb-0 text-white"> <?= e($doctor['full_name']) ?></h4>
                <p class="text-white small mb-0"><?= e($doctor['specialization']) ?> &bull; <?= e($doctor['city_name']) ?></p>
            </div>
        </div>
        <a href="availability.php" class="btn btn-care btn-sm"><i class="bi bi-calendar-plus me-1"></i>Set Availability</a>
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
                <div class="stat-value"><?= $completedAppts ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card purple animate-in">
                <div class="stat-icon purple"><i class="bi bi-clock"></i></div>
                <div class="stat-value"><?= $availSlots ?></div>
                <div class="stat-label">Open Slots</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Appointments -->
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
                                <tr><th>Patient</th><th>Date & Time</th><th>Problem</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($recentAppts && $recentAppts->num_rows > 0): while ($a = $recentAppts->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-500"><?= e($a['patient_name']) ?></td>
                                    <td><?= formatDate($a['appointment_date']) ?><br><small><?= formatTime($a['appointment_time']) ?></small></td>
                                    <td><small><?= $a['problem'] ? e(substr($a['problem'],0,50)) : '-' ?></small></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                    <td>
                                        <form method="POST" action="appointments.php" class="d-flex gap-1">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width:110px;">
                                                <?php foreach (['pending','approved','cancelled','completed'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-care btn-sm">✓</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No appointments yet.</td></tr>
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
                <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Links</div>
                <div class="card-body d-flex flex-column gap-3 p-3">
                    <?php $links = [
                        ['appointments.php','bi-calendar-check','My Appointments'],
                        ['availability.php','bi-clock','Manage Availability'],
                        ['profile.php','bi-person-circle','Edit Profile'],
                    ]; foreach ($links as [$url,$icon,$label]): ?>
                    <a href="<?= $url ?>" class="quick-action">
                        <i class="bi <?= $icon ?>"></i>
                        <span class="fw-500"><?= $label ?></span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
