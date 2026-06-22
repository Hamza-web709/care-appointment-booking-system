<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base = '../';
$pageTitle = 'Manage Doctors';

// Get active cities
$citiesList = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name")->fetch_all(MYSQLI_ASSOC);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $spec      = trim($_POST['specialization'] ?? '');
        $qual      = trim($_POST['qualification'] ?? '');
        $exp       = (int)($_POST['experience'] ?? 0);
        $phone     = trim($_POST['phone'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $city_id   = (int)($_POST['city_id'] ?? 0);

        // Handle image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $result = uploadImage($_FILES['profile_image'], 'profiles');
            if ($result['success']) $profile_image = $result['filename'];
        }

        $conn->begin_transaction();
        try {
            // Insert into users
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'doctor')");
            $stmt->bind_param('sss', $username, $email, $hashedPwd);
            $stmt->execute();
            $userId = $conn->insert_id;

            // Insert into doctors
            $stmt2 = $conn->prepare("
                INSERT INTO doctors 
                (user_id, full_name, specialization, qualification, experience, phone, address, city_id, profile_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt2->bind_param('isssissis', $userId, $full_name, $spec, $qual, $exp, $phone, $address, $city_id, $profile_image);
            $stmt2->execute();

            $conn->commit();
            setFlash('success', "Doctor '$full_name' added successfully.");
        } catch (Exception $e) {
            $conn->rollback();
            setFlash('error', 'Failed to add doctor. Username/email may already exist.');
        }
        header('Location: doctors.php'); exit();
    }

    if ($action === 'edit') {
        $id        = (int)($_POST['id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $spec      = trim($_POST['specialization'] ?? '');
        $qual      = trim($_POST['qualification'] ?? '');
        $exp       = (int)($_POST['experience'] ?? 0);
        $phone     = trim($_POST['phone'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $city_id   = (int)($_POST['city_id'] ?? 0);
        $email     = trim($_POST['email'] ?? '');

        // Handle image upload
        $imgUpdate = '';
        $imgParams = [];
        $imgTypes  = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $result = uploadImage($_FILES['profile_image'], 'profiles');
            if ($result['success']) {
                $imgUpdate = ', profile_image = ?';
                $imgParams[] = $result['filename'];
                $imgTypes  = 's';
            }
        }

        $conn->begin_transaction();
        try {
            // Update user email
            $ustmt = $conn->prepare("UPDATE users SET email=? WHERE id=(SELECT user_id FROM doctors WHERE id=?)");
            $ustmt->bind_param('si', $email, $id);
            $ustmt->execute();

            // Update doctor
            $sql = "UPDATE doctors SET full_name=?, specialization=?, qualification=?, experience=?, phone=?, address=?, city_id=? $imgUpdate WHERE id=?";
            $params = [$full_name, $spec, $qual, $exp, $phone, $address, $city_id];
            if ($imgTypes) $params[] = $imgParams[0];
            $params[] = $id;

            $stmt = $conn->prepare($sql);
            if ($imgUpdate) {
                $stmt->bind_param('sssissisi', ...$params);
            } else {
                $stmt->bind_param('sssisssi', ...$params);
            }
            $stmt->execute();
            $conn->commit();
            setFlash('success', 'Doctor updated successfully.');
        } catch (Exception $e) {
            $conn->rollback();
            setFlash('error', 'Failed to update doctor.');
        }

        header('Location: doctors.php'); exit();
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $row = $conn->query("SELECT user_id FROM doctors WHERE id=$id")->fetch_assoc();
        if ($row) {
            $uid = $row['user_id'];
            $conn->query("DELETE FROM users WHERE id=$uid");
            setFlash('success', 'Doctor deleted successfully.');
        }
        header('Location: doctors.php'); exit();
    }
}

// Fetch all doctors safely
$doctors = $conn->query("
    SELECT d.*, c.city_name, u.username, u.email, u.status as user_status
    FROM doctors d
    LEFT JOIN cities c ON d.city_id = c.id
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC
");

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-person-badge me-2 text-white"></i>Manage Doctors</h1>
        <p class="mb-0 opacity-75 text-white-50">Add, view, and manage doctor records</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>

    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-white">All Doctors</h5>
        <button class="btn btn-care" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
            <i class="bi bi-plus me-1"></i>Add Doctor
        </button>
    </div>

    <div class="care-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table care-table mb-0">
                    <thead>
                        <tr><th>#</th><th>Doctor</th><th>Specialization</th><th>City</th><th>Phone</th><th>Experience</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($d = $doctors->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($d['profile_image']): ?>
                                    <img src="../uploads/profiles/<?= e($d['profile_image']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--care-blue-light);display:flex;align-items:center;justify-content:center;color:var(--care-primary);"><i class="bi bi-person"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-600"><?= e($d['full_name']) ?></div>
                                        <small class="text-muted"><?= e($d['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($d['specialization']) ?></td>
                            <td><?= e($d['city_name']) ?></td>
                            <td><?= e($d['phone']) ?></td>
                            <td><?= $d['experience'] ? $d['experience'].' yrs' : '-' ?></td>
                            <td><?= statusBadge($d['user_status'] ?? 'inactive') ?></td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm edit-doc-btn" 
                                    data-id="<?= $d['id'] ?>"
                                    data-fullname="<?= e($d['full_name']) ?>"
                                    data-email="<?= e($d['email']) ?>"
                                    data-specialization="<?= e($d['specialization']) ?>"
                                    data-qualification="<?= e($d['qualification']) ?>"
                                    data-experience="<?= $d['experience'] ?>"
                                    data-phone="<?= e($d['phone']) ?>"
                                    data-city="<?= $d['city_id'] ?>"
                                    data-address="<?= e($d['address']) ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editDoctorModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Delete Dr. <?= e($d['full_name']) ?>?"><i class="bi bi-trash"></i></button>
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

<!-- SINGLE Dynamic Edit Doctor Modal (Fixed Flickering) -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title" id="editModalTitle">Edit Doctor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id" value="">
                    
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Specialization <span class="text-danger">*</span></label>
                            <input type="text" name="specialization" id="edit_specialization" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" id="edit_qualification" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Experience (years)</label>
                            <input type="number" name="experience" id="edit_experience" class="form-control" min="0">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city_id" id="edit_city_id" class="form-select" required>
                                <?php foreach ($citiesList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['city_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><label class="form-label">Clinic/Hospital Address <span class="text-danger">*</span></label></label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Profile Photo <small class="text-muted">(Leave empty to keep current)</small></label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
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
document.addEventListener('DOMContentLoaded', function() {
    // Single Event Listener using Event Delegation to populate the modal 
    // Fixes the Bootstrap modal flickering bug caused by multiple redundant modals
    document.querySelectorAll('.edit-doc-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_full_name').value = this.dataset.fullname;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_specialization').value = this.dataset.specialization;
            document.getElementById('edit_qualification').value = this.dataset.qualification;
            document.getElementById('edit_experience').value = this.dataset.experience;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_city_id').value = this.dataset.city;
            document.getElementById('edit_address').value = this.dataset.address;
            
            document.getElementById('editModalTitle').textContent = 'Edit Dr. ' + this.dataset.fullname;
        });
    });
});
</script>

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:var(--radius-lg);">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--care-primary),var(--care-teal));color:#fff;">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Doctor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <h6 class="fw-600 text-care mb-3">Account Credentials</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-600 text-care mb-3">Doctor Profile</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Specialization *</label>
                            <input type="text" name="specialization" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g. MBBS, MD">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Experience (yrs)</label>
                            <input type="number" name="experience" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">City *</label>
                            <select name="city_id" class="form-select" required>
                                <option value="">-- Select City --</option>
                                <?php foreach ($citiesList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['city_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Clinic/Hospital Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-care">Add Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
