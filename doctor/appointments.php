<?php
/**
 * CARE – Doctor: Manage My Appointments
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('doctor', '../login.php');

$base = '../';
$pageTitle = 'My Appointments';

$userId = (int)$_SESSION['user_id'];
$doctor = $conn->query("SELECT id FROM doctors WHERE user_id=$userId")->fetch_assoc();
$docId  = $doctor['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'] ?? '';
    $allowed = ['pending','approved','cancelled','completed'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=? AND doctor_id=?");
        $stmt->bind_param('sii', $status, $id, $docId);
        $stmt->execute() ? setFlash('success', 'Status updated.') : setFlash('error', 'Failed.');
    }
    header('Location: appointments.php'); exit();
}

$statusFilter = $_GET['status'] ?? '';
$whereStatus  = $statusFilter ? "AND a.status='$statusFilter'" : '';

$appointments = $conn->query("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone, p.gender
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = $docId $whereStatus
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-calendar-check me-2"></i>My Appointments</h1>
        <p class="mb-0 opacity-75">Manage and update your patient appointments</p>
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

    <div class="care-card">
        <div class="card-header"><i class="bi bi-list me-2"></i>Appointments (<?= $appointments->num_rows ?>)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table care-table mb-0">
                    <thead>
                        <tr><th>#</th><th>Patient</th><th>Date & Time</th><th>Problem</th><th>Status</th><th>Update Status</th></tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($a = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="fw-600"><?= e($a['patient_name']) ?></div>
                                <small class="text-muted"><?= e($a['patient_phone']) ?> &bull; <?= ucfirst($a['gender']) ?></small>
                            </td>
                            <td><?= formatDate($a['appointment_date']) ?><br><small><?= formatTime($a['appointment_time']) ?></small></td>
                            <td><small><?= $a['problem'] ? e($a['problem']) : '-' ?></small></td>
                            <td><?= statusBadge($a['status']) ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:120px;">
                                        <?php foreach (['pending','approved','cancelled','completed'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-care btn-sm">Update</button>
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
