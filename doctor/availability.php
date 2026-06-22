<?php
/**
 * CARE – Doctor: Manage Availability
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('doctor', '../login.php');

$base = '../';
$pageTitle = 'My Availability';

$userId = (int)$_SESSION['user_id'];
$doctor = $conn->query("SELECT id FROM doctors WHERE user_id=$userId")->fetch_assoc();
if (!$doctor) { header('Location: ../logout.php'); exit(); }
$docId = $doctor['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $avail_date = $_POST['available_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time   = $_POST['end_time'] ?? '';
        if ($avail_date && $start_time && $end_time) {
            $stmt = $conn->prepare("INSERT INTO availability (doctor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $docId, $avail_date, $start_time, $end_time);
            $stmt->execute() ? setFlash('success', 'Availability slot added.') : setFlash('error', 'Failed to add slot.');
        } else {
            setFlash('error', 'Please fill all fields.');
        }
    }

    if ($action === 'add_week') {
        // Add for entire week (Mon-Sun starting from the provided date)
        $start_date = $_POST['week_start'] ?? '';
        $start_time = $_POST['week_start_time'] ?? '';
        $end_time   = $_POST['week_end_time'] ?? '';
        if ($start_date && $start_time && $end_time) {
            $dt = new DateTime($start_date);
            $added = 0;
            for ($i = 0; $i < 7; $i++) {
                $dateStr = $dt->format('Y-m-d');
                $stmt = $conn->prepare("INSERT IGNORE INTO availability (doctor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('isss', $docId, $dateStr, $start_time, $end_time);
                $stmt->execute();
                $added++;
                $dt->modify('+1 day');
            }
            setFlash('success', "Added $added availability slots for the week.");
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM availability WHERE id=? AND doctor_id=?");
        $stmt->bind_param('ii', $id, $docId);
        $stmt->execute() ? setFlash('success', 'Slot removed.') : setFlash('error', 'Delete failed.');
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $current = $_POST['current_status'] ?? 'available';
        $new = $current === 'available' ? 'booked' : 'available';
        $stmt = $conn->prepare("UPDATE availability SET status=? WHERE id=? AND doctor_id=?");
        $stmt->bind_param('sii', $new, $id, $docId);
        $stmt->execute();
        setFlash('success', 'Slot status updated.');
    }

    header('Location: availability.php'); exit();
}

// Get availability slots
$filter = $_GET['filter'] ?? 'upcoming';
$whereClause = $filter === 'past' ? "AND available_date < CURDATE()" : "AND available_date >= CURDATE()";
$slots = $conn->query("
    SELECT * FROM availability
    WHERE doctor_id=$docId $whereClause
    ORDER BY available_date ASC, start_time ASC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-clock me-2"></i>Manage Availability</h1>
        <p class="mb-0 opacity-75">Set your available dates and time slots for patient bookings</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="row g-4">
        <!-- Add Forms -->
        <div class="col-lg-4">
            <div class="care-card mb-4">
                <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Add Single Slot</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="available_date" class="form-control min-today" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="09:00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="17:00" required>
                        </div>
                        <button type="submit" class="btn btn-care w-100">Add Slot</button>
                    </form>
                </div>
            </div>

            <div class="care-card">
                <div class="card-header"><i class="bi bi-calendar-week me-2"></i>Add Full Week</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_week">
                        <div class="mb-3">
                            <label class="form-label">Week Start Date</label>
                            <input type="date" name="week_start" class="form-control min-today" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daily Start Time</label>
                            <input type="time" name="week_start_time" class="form-control" value="09:00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daily End Time</label>
                            <input type="time" name="week_end_time" class="form-control" value="17:00" required>
                        </div>
                        <button type="submit" class="btn btn-teal w-100">Add 7 Days</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Slots Table -->
        <div class="col-lg-8">
            <div class="care-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-check me-2"></i>My Availability Slots</span>
                    <div class="d-flex gap-2">
                        <a href="?filter=upcoming" class="btn btn-sm <?= $filter==='upcoming'?'btn-care':'btn-outline-light' ?>">Upcoming</a>
                        <a href="?filter=past"     class="btn btn-sm <?= $filter==='past'?'btn-care':'btn-outline-light' ?>">Past</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table care-table mb-0">
                            <thead>
                                <tr><th>Date</th><th>Day</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($slots->num_rows > 0): while ($s = $slots->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-500"><?= formatDate($s['available_date']) ?></td>
                                    <td class="text-muted small"><?= date('l', strtotime($s['available_date'])) ?></td>
                                    <td><?= formatTime($s['start_time']) ?></td>
                                    <td><?= formatTime($s['end_time']) ?></td>
                                    <td>
                                        <span class="slot-badge <?= $s['status'] ?>">
                                            <i class="bi bi-<?= $s['status']==='available'?'check-circle':'x-circle' ?>"></i>
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $s['status'] ?>">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Toggle">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                        <?php if ($s['status'] === 'available'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Remove this slot?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No <?= $filter ?> slots. Add some above!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
