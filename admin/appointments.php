<?php
/**
 * CARE – Admin: Manage Appointments
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Manage Appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $id     = (int)$_POST['id'];
        $status = $_POST['status'] ?? '';
        $allowed = ['pending','approved','cancelled','completed'];
        if (in_array($status, $allowed)) {
            $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
            $stmt->bind_param('si', $status, $id);
            $stmt->execute() ? setFlash('success', 'Appointment status updated.') : setFlash('error', 'Update failed.');
        }
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM appointments WHERE id=$id");
        setFlash('success', 'Appointment deleted.');
    }
    header('Location: appointments.php'); exit();
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE a.status='$statusFilter'" : '';

$appointments = $conn->query("
    SELECT a.*,
           d.full_name as doctor_name, d.specialization,
           p.full_name as patient_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN patients p ON a.patient_id = p.id
    $where
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-calendar-check me-2 text-white"></i>Manage Appointments</h1>
        <p class="mb-0 opacity-75 text-white-50">View and update all appointment records</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>

    <!-- Filters -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="appointments.php" class="btn <?= $statusFilter===''?'btn-care':'btn-outline-secondary' ?> btn-sm">All</a>
        <?php foreach (['pending'=>'warning','approved'=>'success','cancelled'=>'danger','completed'=>'primary'] as $s=>$c): ?>
        <a href="appointments.php?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter===$s?'btn-'.$c:'btn-outline-'.$c ?>">
            <?= ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="care-card">
        <div class="card-header"><i class="bi bi-calendar-event me-2"></i>Appointments (<?= $appointments->num_rows ?>)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table care-table mb-0">
                    <thead>
                        <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date & Time</th><th>Problem</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($a = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="fw-500"><?= e($a['patient_name']) ?></td>
                            <td><?= e($a['doctor_name']) ?><br><small class="text-muted"><?= e($a['specialization']) ?></small></td>
                            <td><?= formatDate($a['appointment_date']) ?><br><small><?= formatTime($a['appointment_time']) ?></small></td>
                            <td><small><?= $a['problem'] ? e(substr($a['problem'],0,60)).'...' : '-' ?></small></td>
                            <td><?= statusBadge($a['status']) ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-1 flex-wrap">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:120px;">
                                        <?php foreach (['pending','approved','cancelled','completed'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-care btn-sm mb-2">Update</button>
                                </form>
                                <form method="POST" class="d-inline mt-1">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete this appointment?"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
