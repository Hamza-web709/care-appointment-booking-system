<?php
/**
 * CARE – Admin Dashboard
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Admin Dashboard';

// Stats
$totalDoctors  = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0];
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
$totalAppts    = $conn->query("SELECT COUNT(*) FROM appointments")->fetch_row()[0];
$totalCities   = $conn->query("SELECT COUNT(*) FROM cities WHERE status='active'")->fetch_row()[0];
$pendingAppts  = $conn->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetch_row()[0];
$totalDiseases = $conn->query("SELECT COUNT(*) FROM diseases")->fetch_row()[0];
$totalNews     = $conn->query("SELECT COUNT(*) FROM medical_news")->fetch_row()[0];
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

// Recent appointments
$recentAppts = $conn->query("
    SELECT a.*, 
           d.full_name as doctor_name, d.specialization,
           p.full_name as patient_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN patients p ON a.patient_id = p.id
    ORDER BY a.created_at DESC
    LIMIT 8
");

include '../includes/header.php';
?>
<div class="container-fluid py-4" style="max-width:1400px;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-800 mb-0 text-white">Admin Dashboard</h3>
            <p class="text-white-50 small mb-0">Welcome back, <?= e($_SESSION['username']) ?> &mdash; <?= date('l, d M Y') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="users.php" class="btn btn-care btn-sm"><i class="bi bi-person-plus me-1"></i>Add User</a>
            <a href="doctors.php" class="btn btn-teal btn-sm"><i class="bi bi-plus me-1"></i>Add Doctor</a>
        </div>
    </div>

    <!-- Stat Cards Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card primary animate-in">
                <div class="stat-icon primary"><i class="bi bi-person-badge"></i></div>
                <div class="stat-value"><?= $totalDoctors ?></div>
                <div class="stat-label">Total Doctors</div>
                <a href="doctors.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card teal animate-in">
                <div class="stat-icon teal"><i class="bi bi-people"></i></div>
                <div class="stat-value"><?= $totalPatients ?></div>
                <div class="stat-label">Total Patients</div>
                <a href="patients.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card orange animate-in">
                <div class="stat-icon orange"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value"><?= $totalAppts ?></div>
                <div class="stat-label">Total Appointments</div>
                <a href="appointments.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card purple animate-in">
                <div class="stat-icon purple"><i class="bi bi-geo-alt"></i></div>
                <div class="stat-value"><?= $totalCities ?></div>
                <div class="stat-label">Active Cities</div>
                <a href="cities.php" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <!-- Stat Cards Row 2 -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card primary animate-in">
                <div class="stat-icon primary"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value text-warning"><?= $pendingAppts ?></div>
                <div class="stat-label">Pending Appointments</div>
                <a href="appointments.php?status=pending" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card teal animate-in">
                <div class="stat-icon teal"><i class="bi bi-virus"></i></div>
                <div class="stat-value"><?= $totalDiseases ?></div>
                <div class="stat-label">Diseases Listed</div>
                <a href="diseases.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card orange animate-in">
                <div class="stat-icon orange"><i class="bi bi-newspaper"></i></div>
                <div class="stat-value"><?= $totalNews ?></div>
                <div class="stat-label">Medical News</div>
                <a href="news.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card purple animate-in">
                <div class="stat-icon purple"><i class="bi bi-person-lock"></i></div>
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
                <a href="users.php" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-xl-8">
            <div class="care-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-event me-2"></i>Recent Appointments</span>
                    <a href="appointments.php" class="btn btn-outline-light btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table care-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentAppts && $recentAppts->num_rows > 0):
                                      $i = 1;
                                      while ($a = $recentAppts->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted small"><?= $i++ ?></td>
                                    <td><?= e($a['patient_name']) ?></td>
                                    <td><?= e($a['doctor_name']) ?><br><small class="text-muted"><?= e($a['specialization']) ?></small></td>
                                    <td class="small"><?= formatDate($a['appointment_date']) ?><br><?= formatTime($a['appointment_time']) ?></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No appointments yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="care-card h-100">
                <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
                <div class="card-body d-flex flex-column gap-3 p-3">
                    <?php $actions = [
                        ['cities.php','bi-geo-alt','Manage Cities'],
                        ['doctors.php','bi-person-badge','Manage Doctors'],
                        ['patients.php','bi-people','Manage Patients'],
                        ['appointments.php','bi-calendar-check','Manage Appointments'],
                        ['diseases.php','bi-virus','Manage Diseases'],
                        ['news.php','bi-newspaper','Manage News'],
                        ['users.php','bi-person-lock','Manage Users'],
                    ];
                    foreach ($actions as [$url, $icon, $label]): ?>
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
