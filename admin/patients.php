<?php
/**
 * CARE – Admin: Manage Patients (CRUD)
 * Fixed: replaced per-row modal (caused flickering) with a single shared modal
 * populated via JavaScript data-attributes (same pattern as doctors.php / users.php).
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base      = '../';
$pageTitle = 'Manage Patients';

$citiesRaw  = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name");
$citiesList = $citiesRaw->fetch_all(MYSQLI_ASSOC);



// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $id        = (int)($_POST['id']            ?? 0);
        $full_name = trim($_POST['full_name']       ?? '');
        $phone     = trim($_POST['phone']           ?? '');
        $address   = trim($_POST['address']         ?? '');
        $city_id   = (int)($_POST['city_id']        ?? 0);
        $dob       = $_POST['date_of_birth']        ?? null;
        $gender    = $_POST['gender']               ?? '';
        $email     = trim($_POST['email']           ?? '');

        if (empty($dob)) $dob = null;

        // Update patient profile
        $stmt = $conn->prepare(
            "UPDATE patients SET full_name=?, phone=?, address=?, city_id=?, date_of_birth=?, gender=? WHERE id=?"
        );
        $stmt->bind_param('sssissi', $full_name, $phone, $address, $city_id, $dob, $gender, $id);

        // Update user email
        $ustmt = $conn->prepare(
            "UPDATE users SET email=? WHERE id=(SELECT user_id FROM patients WHERE id=?)"
        );
        $ustmt->bind_param('si', $email, $id);
        $ustmt->execute();

        $stmt->execute()
            ? setFlash('success', 'Patient updated successfully.')
            : setFlash('error', 'Update failed. Please try again.');
    }

    if ($action === 'delete') {
        $id  = (int)$_POST['id'];
        $row = $conn->query("SELECT user_id FROM patients WHERE id=$id")->fetch_assoc();
        if ($row) {
            $conn->query("DELETE FROM users WHERE id=" . (int)$row['user_id']);
            setFlash('success', 'Patient deleted.');
        }
    }

    header('Location: patients.php');
    exit();
}

// ── Fetch all patients ────────────────────────────────────────────────────────
$patients = $conn->query("
    SELECT p.*, c.city_name, u.username, u.email, u.status AS user_status, p.profile_image
FROM patients p
JOIN cities c ON p.city_id = c.id
JOIN users  u ON p.user_id = u.id
ORDER BY p.created_at DESC
");

include '../includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-people me-2 text-white"></i>Manage Patients</h1>
        <p class="mb-0 opacity-75 text-white-50">View and manage patient records</p>
    </div>
</div>

<div class="container py-4">
    <?php showFlash(); ?>

    <div class="care-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people me-2"></i>All Patients (<?= $patients->num_rows ?>)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table care-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Patient</th><th>Phone</th><th>City</th>
                            <th>Gender</th><th>DOB</th><th>Registered</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($p = $patients->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($p['profile_image']): ?>
                                    <img src="../uploads/profiles/<?= e($p['profile_image']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--care-blue-light);display:flex;align-items:center;justify-content:center;color:var(--care-primary);"><i class="bi bi-person"></i></div>
                                    <?php endif; ?>
                                <div class="fw-600"><?= e($p['full_name']) ?></div>
                                <small class="text-muted"><?= e($p['email']) ?></small>
                            </td>
                            <td><?= e($p['phone']) ?></td>
                            <td><?= e($p['city_name']) ?></td>
                            <td class="text-capitalize"><?= e($p['gender']) ?></td>
                            <td><?= $p['date_of_birth'] ? formatDate($p['date_of_birth']) : '-' ?></td>
                            <td class="small text-muted"><?= formatDate($p['created_at']) ?></td>
                            <td><?= statusBadge($p['user_status']) ?></td>
                            <td>
                                <!-- Edit button – all patient data stored in data-* attributes -->
                                <button class="btn btn-outline-primary btn-sm edit-patient-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPatientModal"
                                        data-id="<?= $p['id'] ?>"
                                        data-fullname="<?= e($p['full_name']) ?>"
                                        data-email="<?= e($p['email']) ?>"
                                        data-phone="<?= e($p['phone']) ?>"
                                        data-address="<?= e($p['address']) ?>"
                                        data-city="<?= $p['city_id'] ?>"
                                        data-gender="<?= e($p['gender']) ?>"
                                        data-dob="<?= e($p['date_of_birth']) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <!-- Delete form -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id"     value="<?= $p['id'] ?>">
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm"
                                            data-confirm="Delete patient <?= e($p['full_name']) ?>?">
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

<!-- ════════════════════════════════════════════════════════════════════════════
     SINGLE Shared Edit Modal – populated by JS (fixes the flickering bug
     caused by rendering one modal per row inside the loop)
════════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title" id="editPatientModalTitle">Edit Patient</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id"     id="edit_pat_id" value="">

                    <div class="row g-3">

                        <div class="col-sm-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="edit_pat_fullname"
                                   class="form-control" required>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_pat_email"
                                   class="form-control">
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_pat_phone"
                                   class="form-control">
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">City</label>
                            <select name="city_id" id="edit_pat_city" class="form-select">
                                <?php foreach ($citiesList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['city_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="edit_pat_gender" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="edit_pat_dob"
                                   class="form-control" max="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_pat_address"
                                      class="form-control" rows="2"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Single event-delegation listener to populate the shared edit modal.
 * This replaces the old pattern of embedding a modal per row, which
 * caused Bootstrap to flicker because multiple modals fired simultaneously.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-patient-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Populate modal fields from data-* attributes
            document.getElementById('edit_pat_id').value       = this.dataset.id;
            document.getElementById('edit_pat_fullname').value = this.dataset.fullname;
            document.getElementById('edit_pat_email').value    = this.dataset.email;
            document.getElementById('edit_pat_phone').value    = this.dataset.phone;
            document.getElementById('edit_pat_address').value  = this.dataset.address;
            document.getElementById('edit_pat_dob').value      = this.dataset.dob;

            // Set city dropdown
            const citySelect = document.getElementById('edit_pat_city');
            citySelect.value = this.dataset.city;

            // Set gender dropdown
            const genderSelect = document.getElementById('edit_pat_gender');
            genderSelect.value = this.dataset.gender;

            // Update modal title
            document.getElementById('editPatientModalTitle').textContent =
                'Edit Patient: ' + this.dataset.fullname;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
