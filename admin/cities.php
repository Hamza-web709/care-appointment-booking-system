<?php
/**
 * CARE – Admin: Manage Cities (CRUD)
 * Fixed: replaced per-row modal (caused flickering) with a single shared modal
 * populated via JavaScript data-attributes (same pattern as doctors.php / patients.php).
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base      = '../';
$pageTitle = 'Manage Cities';

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $city_name = trim($_POST['city_name'] ?? '');
        $status    = $_POST['status'] ?? 'active';
        if (!empty($city_name)) {
            $stmt = $conn->prepare("INSERT INTO cities (city_name, status) VALUES (?, ?)");
            $stmt->bind_param('ss', $city_name, $status);
            if ($stmt->execute()) setFlash('success', "City '$city_name' added successfully.");
            else setFlash('error', 'City already exists or failed to add.');
        }

    } elseif ($action === 'edit') {
        $id        = (int)$_POST['id'];
        $city_name = trim($_POST['city_name'] ?? '');
        $status    = $_POST['status'] ?? 'active';
        if ($id > 0 && !empty($city_name)) {
            $stmt = $conn->prepare("UPDATE cities SET city_name=?, status=? WHERE id=?");
            $stmt->bind_param('ssi', $city_name, $status, $id);
            $stmt->execute()
                ? setFlash('success', 'City updated.')
                : setFlash('error', 'Update failed.');
        }

    } elseif ($action === 'delete') {
        $id    = (int)$_POST['id'];
        $inUse = $conn->query("SELECT COUNT(*) FROM doctors  WHERE city_id=$id")->fetch_row()[0]
               + $conn->query("SELECT COUNT(*) FROM patients WHERE city_id=$id")->fetch_row()[0];
        if ($inUse > 0) {
            setFlash('error', 'Cannot delete: City is assigned to doctors/patients.');
        } else {
            $stmt = $conn->prepare("DELETE FROM cities WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute()
                ? setFlash('success', 'City deleted.')
                : setFlash('error', 'Delete failed.');
        }
    }

    header('Location: cities.php');
    exit();
}

// ── Fetch all cities with usage counts ───────────────────────────────────────
$cities = $conn->query("
    SELECT c.*,
        (SELECT COUNT(*) FROM doctors  d WHERE d.city_id = c.id) AS doctor_count,
        (SELECT COUNT(*) FROM patients p WHERE p.city_id = c.id) AS patient_count
    FROM cities c ORDER BY c.city_name
");

include '../includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-geo-alt me-2"></i>Manage Cities</h1>
        <p class="mb-0 opacity-75">Add, edit, and manage cities for doctor and patient profiles</p>
    </div>
</div>

<div class="container py-4">
    <?php showFlash(); ?>

    <div class="row g-4">

        <!-- ── Add City Form ─────────────────────────────────────────────── -->
        <div class="col-lg-4">
            <div class="care-card">
                <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Add New City</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">City Name <span class="text-danger">*</span></label>
                            <input type="text" name="city_name" class="form-control"
                                   placeholder="e.g. Karachi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-care w-100">
                            <i class="bi bi-plus me-1"></i>Add City
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Cities Table ──────────────────────────────────────────────── -->
        <div class="col-lg-8">
            <div class="care-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list me-2"></i>All Cities (<?= $cities->num_rows ?>)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table care-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th><th>City Name</th><th>Doctors</th>
                                    <th>Patients</th><th>Status</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while ($c = $cities->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td class="fw-500"><?= e($c['city_name']) ?></td>
                                    <td><span class="badge bg-care"><?= $c['doctor_count'] ?></span></td>
                                    <td><span class="badge bg-teal"><?= $c['patient_count'] ?></span></td>
                                    <td><?= statusBadge($c['status']) ?></td>
                                    <td>
                                        <!-- Edit button – data stored in data-* attributes, no per-row modal -->
                                        <button class="btn btn-outline-primary btn-sm edit-city-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCityModal"
                                                data-id="<?= $c['id'] ?>"
                                                data-name="<?= e($c['city_name']) ?>"
                                                data-status="<?= e($c['status']) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                    data-confirm="Delete '<?= e($c['city_name']) ?>'?">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

    </div><!-- /row -->
</div>

<!-- ════════════════════════════════════════════════════════════════════════════
     SINGLE Shared Edit Modal – populated by JS (fixes flickering bug)
════════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editCityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header"
                 style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title" id="editCityModalTitle">Edit City</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id"     id="edit_city_id" value="">

                    <div class="mb-3">
                        <label class="form-label">City Name <span class="text-danger">*</span></label>
                        <input type="text" name="city_name" id="edit_city_name"
                               class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_city_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Single listener populates the shared edit modal from data-* attributes.
 * Replaces the old per-row modal pattern which caused Bootstrap flickering.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-city-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_city_id').value     = this.dataset.id;
            document.getElementById('edit_city_name').value   = this.dataset.name;
            document.getElementById('edit_city_status').value = this.dataset.status;
            document.getElementById('editCityModalTitle').textContent =
                'Edit City: ' + this.dataset.name;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
