<?php
/**
 * CARE – Admin: Add User (Dynamic Role-Based Form)
 * Supports creating Admin, Doctor, and Patient accounts.
 * Uses prepared statements and transactions for data integrity.
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('admin', '../login.php');

$base      = '../';
$pageTitle = 'Add User';

// ─────────────────────────────────────────────────────────────────────────────
// Fetch cities for Doctor & Patient dropdowns
// ─────────────────────────────────────────────────────────────────────────────
$citiesRaw  = $conn->query("SELECT id, city_name FROM cities WHERE status='active' ORDER BY city_name");
$citiesList = $citiesRaw ? $citiesRaw->fetch_all(MYSQLI_ASSOC) : [];

// ─────────────────────────────────────────────────────────────────────────────
// POST Handler
// ─────────────────────────────────────────────────────────────────────────────
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Common fields ─────────────────────────────────────────────────────────
    $role      = $_POST['role']             ?? '';
    $full_name = trim($_POST['full_name']   ?? '');
    $username  = trim($_POST['username']    ?? '');
    $password  = $_POST['password']         ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // ── Common validation ─────────────────────────────────────────────────────
    if (!in_array($role, ['admin', 'doctor', 'patient'])) {
        $errors[] = 'Please select a valid user role.';
    }
    if (empty($full_name))       $errors[] = 'Full name is required.';
    if (empty($username))        $errors[] = 'Username is required.';
    if (strlen($password) < 6)   $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';

    // ── Duplicate username check ──────────────────────────────────────────────
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $chk->bind_param('s', $username);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = "Username '$username' is already taken.";
        $chk->close();
    }

    // ── Role-specific input (each role uses its own prefixed field names) ─────
    // This prevents duplicate-name conflicts when both doctor/patient sections
    // are rendered in the same HTML form simultaneously.
    $email   = '';
    $phone   = '';
    $address = '';
    $city_id = 0;

    if ($role === 'doctor' || $role === 'patient') {
        $prefix  = ($role === 'doctor') ? 'doc_' : 'pat_';
        $email   = trim($_POST[$prefix . 'email']   ?? '');
        $phone   = trim($_POST[$prefix . 'phone']   ?? '');
        $address = trim($_POST[$prefix . 'address'] ?? '');
        $city_id = $_POST[$prefix . 'city_id'] ?? '';

if (empty($city_id) || !is_numeric($city_id)) {
    $errors[] = 'Please select a city.';
} else {
    $city_id = (int)$city_id;
}

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        // Duplicate email check (only if email looks valid)
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $chkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $chkEmail->bind_param('s', $email);
            $chkEmail->execute();
            $chkEmail->store_result();
            if ($chkEmail->num_rows > 0) $errors[] = "Email '$email' is already registered.";
            $chkEmail->close();
        }
    }

    // ── DB insert inside a transaction ────────────────────────────────────────
    if (empty($errors)) {
        $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
        $conn->begin_transaction();

        try {
            // 1. Insert into users
            if ($role === 'admin') {
                $emailVal = ''; // admins don't need email
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                $stmt->bind_param('sss', $username, $emailVal, $hashedPwd);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $username, $email, $hashedPwd, $role);
            }
            $stmt->execute();
            $userId = $conn->insert_id;
            $stmt->close();

            // 2. Insert role-specific profile
            if ($role === 'admin') {

                $stmt2 = $conn->prepare("INSERT INTO admins (user_id, full_name) VALUES (?, ?)");
                $stmt2->bind_param('is', $userId, $full_name);
                $stmt2->execute();
                $stmt2->close();

            } elseif ($role === 'doctor') {

                $specialization = trim($_POST['doc_specialization'] ?? '');
                $qualification  = trim($_POST['doc_qualification']  ?? '');
                $experience     = (int)($_POST['doc_experience']    ?? 0);

                if (empty($specialization)) {
                    throw new Exception('Specialization is required for Doctor.');
                }

                // Optional profile image
                $profile_image = null;
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadImage($_FILES['profile_image'], 'profiles');
                    if ($uploadResult['success']) {
                        $profile_image = $uploadResult['filename'];
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }

                // Types: i=user_id, s=full_name, s=specialization, s=qualification,
                //        i=experience, s=phone, s=address, i=city_id, s=profile_image
                $stmt2 = $conn->prepare(
                    "INSERT INTO doctors
                    (user_id, full_name, specialization, qualification, experience, phone, address, city_id, profile_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt2->bind_param('isssiissi',
                    $userId, $full_name, $specialization, $qualification,
                    $experience, $phone, $address, $city_id, $profile_image
                );
                $stmt2->execute();
                $stmt2->close();

            } elseif ($role === 'patient') {

                $dob    = $_POST['pat_date_of_birth'] ?? null;
                $gender = $_POST['pat_gender']         ?? '';
                if (empty($dob))    $dob    = null;
                if (empty($gender)) $gender = null;

                // Types: i=user_id, s=full_name, s=phone, s=address, i=city_id, s=dob, s=gender
                $stmt2 = $conn->prepare(
                    "INSERT INTO patients
                    (user_id, full_name, phone, address, city_id, date_of_birth, gender)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt2->bind_param('isssiss',
                    $userId, $full_name, $phone, $address, $city_id, $dob, $gender
                );
                $stmt2->execute();
                $stmt2->close();
            }

            $conn->commit();
            setFlash('success', ucfirst($role) . " '$full_name' was created successfully.");
            header('Location: users.php');
            exit();

        } catch (Exception $ex) {
            $conn->rollback();
            $errors[] = 'Failed to create user: ' . $ex->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<!-- PAGE HERO ───────────────────────────────────────────────────────────────── -->
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-person-plus-fill me-2"></i>Add New User</h1>
        <p class="mb-0 opacity-75">Create Admin, Doctor, or Patient accounts</p>
    </div>
</div>

<div class="container py-4">

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <!-- ROLE SELECTOR ──────────────────────────────────────────────── -->
            <div class="care-card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-gear me-2"></i>Select User Type
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        Choose the role for the new user. The form fields will update automatically.
                    </p>
                    <div class="d-flex flex-wrap gap-3" id="roleToggleGroup">

                        <button type="button" id="roleBtn-admin"
                                class="role-toggle-btn role-btn-admin <?= (($_POST['role'] ?? 'admin') === 'admin') ? 'active' : '' ?>"
                                onclick="switchRole('admin')">
                            <span class="role-icon"><i class="bi bi-shield-lock-fill"></i></span>
                            <span class="role-label">Admin</span>
                            <span class="role-desc">System administrator with full access</span>
                        </button>

                        <button type="button" id="roleBtn-doctor"
                                class="role-toggle-btn role-btn-doctor <?= (($_POST['role'] ?? '') === 'doctor') ? 'active' : '' ?>"
                                onclick="switchRole('doctor')">
                            <span class="role-icon"><i class="bi bi-capsule-pill"></i></span>
                            <span class="role-label">Doctor</span>
                            <span class="role-desc">Medical professional &amp; specialist</span>
                        </button>

                        <button type="button" id="roleBtn-patient"
                                class="role-toggle-btn role-btn-patient <?= (($_POST['role'] ?? '') === 'patient') ? 'active' : '' ?>"
                                onclick="switchRole('patient')">
                            <span class="role-icon"><i class="bi bi-person-heart"></i></span>
                            <span class="role-label">Patient</span>
                            <span class="role-desc">Registered patient seeking care</span>
                        </button>

                    </div>
                </div>
            </div>

            <!-- MAIN FORM ──────────────────────────────────────────────────── -->
            <form method="POST" enctype="multipart/form-data" id="addUserForm" novalidate>
                <input type="hidden" name="role" id="selectedRole" value="<?= e($_POST['role'] ?? 'admin') ?>">

                <!-- ─── SECTION 1: CREDENTIALS (always visible) ─────────────── -->
                <div class="care-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-key-fill me-2"></i>
                        Account Credentials
                        <span id="roleHeaderBadge" class="badge ms-2"></span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-sm-6">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="full_name" id="full_name"
                                           class="form-control" placeholder="Full Name"
                                           value="<?= e($_POST['full_name'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                                    <input type="text" name="username" id="username"
                                           class="form-control" placeholder="Unique username"
                                           value="<?= e($_POST['username'] ?? '') ?>"
                                           autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password"
                                           class="form-control" placeholder="Min. 6 characters"
                                           minlength="6" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePwd('password')" tabindex="-1">
                                        <i class="bi bi-eye" id="password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="form-control" placeholder="Re-enter password"
                                           autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePwd('confirm_password')" tabindex="-1">
                                        <i class="bi bi-eye" id="confirm_password_icon"></i>
                                    </button>
                                </div>
                                <div id="pwdMatchMsg" class="form-text mt-1"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ─── SECTION 2: DOCTOR FIELDS ────────────────────────────── -->
                <!-- All doctor fields use 'doc_' prefix to avoid name conflicts -->
                <div class="care-card mb-4 role-section" id="section-doctor" style="display:none;">
                    <div class="card-header">
                        <i class="bi bi-capsule-pill me-2 text-white"></i>Doctor Profile
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-sm-6">
                                <label for="doc_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="doc_email" id="doc_email"
                                           class="form-control" placeholder="doctor@example.com"
                                           value="<?= e($_POST['doc_email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="doc_phone" class="form-label">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="doc_phone" id="doc_phone"
                                           class="form-control" placeholder="+92 300 0000000"
                                           value="<?= e($_POST['doc_phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="doc_specialization" class="form-label">Specialization <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-heart-pulse"></i></span>
                                    <input type="text" name="doc_specialization" id="doc_specialization"
                                           class="form-control" placeholder="e.g. Cardiologist"
                                           value="<?= e($_POST['doc_specialization'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="doc_qualification" class="form-label">Qualification</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                                    <input type="text" name="doc_qualification" id="doc_qualification"
                                           class="form-control" placeholder="e.g. MBBS, FCPS"
                                           value="<?= e($_POST['doc_qualification'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="doc_experience" class="form-label">Experience (years)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-award"></i></span>
                                    <input type="number" name="doc_experience" id="doc_experience"
                                           class="form-control" min="0" max="60"
                                           value="<?= (int)($_POST['doc_experience'] ?? 0) ?>">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="doc_city_id" class="form-label">City <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <select name="doc_city_id" id="doc_city_id" class="form-select">
                                        <option value="">-- Select City --</option>
                                        <?php foreach ($citiesList as $city): ?>
                                        <option value="<?= $city['id'] ?>"
                                            <?= (($_POST['doc_city_id'] ?? 0) == $city['id']) ? 'selected' : '' ?>>
                                            <?= e($city['city_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="doc_profile_image" class="form-label">
                                    Profile Photo <small class="text-muted">(Optional, max 2MB)</small>
                                </label>
                                <input type="file" name="profile_image" id="doc_profile_image"
                                       class="form-control" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG, PNG or WebP only.</div>
                            </div>

                            <div class="col-12">
                                <label for="doc_address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <textarea name="doc_address" id="doc_address"
                                              class="form-control" rows="2"
                                              placeholder="Clinic or hospital address"><?= e($_POST['doc_address'] ?? '') ?></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ─── SECTION 3: PATIENT FIELDS ───────────────────────────── -->
                <!-- All patient fields use 'pat_' prefix to avoid name conflicts -->
                <div class="care-card mb-4 role-section" id="section-patient" style="display:none;">
                    <div class="card-header" style="background:linear-gradient(135deg,#19875422,#20c99722);">
                        <i class="bi bi-person-heart me-2 text-success"></i>Patient Profile
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">

                            <div class="col-sm-6">
                                <label for="pat_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="pat_email" id="pat_email"
                                           class="form-control" placeholder="patient@example.com"
                                           value="<?= e($_POST['pat_email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="pat_phone" class="form-label">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="pat_phone" id="pat_phone"
                                           class="form-control" placeholder="+92 300 0000000"
                                           value="<?= e($_POST['pat_phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="pat_dob" class="form-label">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-heart"></i></span>
                                    <input type="date" name="pat_date_of_birth" id="pat_dob"
                                           class="form-control" max="<?= date('Y-m-d') ?>"
                                           value="<?= e($_POST['pat_date_of_birth'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="pat_gender" class="form-label">Gender</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                                    <select name="pat_gender" id="pat_gender" class="form-select">
                                        <option value="">-- Select --</option>
                                        <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l): ?>
                                        <option value="<?= $v ?>"
                                            <?= ($_POST['pat_gender'] ?? '') === $v ? 'selected' : '' ?>>
                                            <?= $l ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="pat_city_id" class="form-label">City <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <select name="pat_city_id" id="pat_city_id" class="form-select">
                                        <option value="">-- Select City --</option>
                                        <?php foreach ($citiesList as $city): ?>
                                        <option value="<?= $city['id'] ?>"
                                            <?= (($_POST['pat_city_id'] ?? 0) == $city['id']) ? 'selected' : '' ?>>
                                            <?= e($city['city_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="pat_address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                    <textarea name="pat_address" id="pat_address"
                                              class="form-control" rows="2"
                                              placeholder="Home / residential address"><?= e($_POST['pat_address'] ?? '') ?></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- ─── FORM ACTIONS ─────────────────────────────────────────── -->
                <div class="d-flex gap-3 justify-content-end mb-5">
                    <a href="users.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-care px-5" id="submitBtn">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        <span id="submitBtnText">Create Admin</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- STYLES ──────────────────────────────────────────────────────────────────── -->
<style>
.role-toggle-btn {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    padding: 14px 20px;
    border-radius: 12px;
    border: 2px solid var(--bs-border-color, #dee2e6);
    background: transparent;
    cursor: pointer;
    transition: all 0.22s ease;
    min-width: 165px;
    flex: 1;
    text-align: left;
    color: inherit;
}
.role-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.role-toggle-btn .role-icon { font-size: 1.5rem; line-height: 1; }
.role-toggle-btn .role-label { font-weight: 700; font-size: 1rem; }
.role-toggle-btn .role-desc  { font-size: 0.75rem; color: #888; line-height: 1.3; }

.role-btn-admin.active  { border-color:#dc3545; background:linear-gradient(135deg,#dc354515,#fd7e1408); box-shadow:0 4px 16px rgba(220,53,69,.18);  color:#dc3545; }
.role-btn-admin.active .role-desc { color:#dc354599; }
.role-btn-admin  .role-icon { color:#dc3545; }

.role-btn-doctor.active { border-color:#0d6efd; background:linear-gradient(135deg,#0d6efd15,#0dcaf008); box-shadow:0 4px 16px rgba(13,110,253,.18); color:#0d6efd; }
.role-btn-doctor.active .role-desc { color:#0d6efd99; }
.role-btn-doctor .role-icon { color:#0d6efd; }

.role-btn-patient.active{ border-color:#198754; background:linear-gradient(135deg,#19875415,#20c99708); box-shadow:0 4px 16px rgba(25,135,84,.18);  color:#198754; }
.role-btn-patient.active .role-desc { color:#19875499; }
.role-btn-patient .role-icon { color:#198754; }

.role-section { animation: fadeSlideIn 0.3s ease forwards; }
@keyframes fadeSlideIn {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}
</style>

<!-- JAVASCRIPT ───────────────────────────────────────────────────────────────── -->
<script>
const roleConfig = {
    admin: {
        label: 'Admin', badgeClass: 'bg-danger', submitText: 'Create Admin',
        show: ['section-admin'], hide: ['section-doctor','section-patient']
    },
    doctor: {
        label: 'Doctor', badgeClass: 'bg-primary', submitText: 'Create Doctor',
        show: ['section-doctor'], hide: ['section-admin','section-patient']
    },
    patient: {
        label: 'Patient', badgeClass: 'bg-success', submitText: 'Create Patient',
        show: ['section-patient'], hide: ['section-admin','section-doctor']
    }
};

function switchRole(role) {
    const cfg = roleConfig[role];
    if (!cfg) return;

    // Update hidden input
    document.getElementById('selectedRole').value = role;

    // Toggle button active states
    document.querySelectorAll('.role-toggle-btn').forEach(b => b.classList.remove('active'));
    const activeBtn = document.getElementById('roleBtn-' + role);
    if (activeBtn) activeBtn.classList.add('active');

    // Show / hide sections
    cfg.hide.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    cfg.show.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = '';
            el.style.animation = 'none';
            void el.offsetHeight; // reflow to restart animation
            el.style.animation = '';
        }
    });

    // Update badge and submit text
    const badge = document.getElementById('roleHeaderBadge');
    if (badge) { badge.className = 'badge ms-2 ' + cfg.badgeClass; badge.textContent = cfg.label; }
    const submitTxt = document.getElementById('submitBtnText');
    if (submitTxt) submitTxt.textContent = cfg.submitText;
}

// Password toggle
function togglePwd(id) {
    const f = document.getElementById(id);
    const i = document.getElementById(id + '_icon');
    if (!f || !i) return;
    f.type = (f.type === 'password') ? 'text' : 'password';
    i.className = (f.type === 'text') ? 'bi bi-eye-slash' : 'bi bi-eye';
}

// Live password match indicator
function checkPwdMatch() {
    const pwd  = document.getElementById('password').value;
    const conf = document.getElementById('confirm_password').value;
    const msg  = document.getElementById('pwdMatchMsg');
    if (!conf) { msg.textContent = ''; return; }
    msg.innerHTML = (pwd === conf)
        ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</span>'
        : '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</span>';
}

// Client-side validation on submit
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    const role = document.getElementById('selectedRole').value;
    const pwd  = document.getElementById('password').value;
    const conf = document.getElementById('confirm_password').value;
    const errs = [];

    if (pwd.length < 6)  errs.push('Password must be at least 6 characters.');
    if (pwd !== conf)    errs.push('Passwords do not match.');

    if (role === 'doctor') {
        if (!document.getElementById('doc_specialization').value.trim()) errs.push('Specialization is required.');
        if (!document.getElementById('doc_email').value.trim())          errs.push('Email is required for Doctor.');
        if (!document.getElementById('doc_city_id').value)               errs.push('City is required for Doctor.');
    }
    if (role === 'patient') {
        if (!document.getElementById('pat_email').value.trim()) errs.push('Email is required for Patient.');
        if (!document.getElementById('pat_city_id').value)      errs.push('City is required for Patient.');
    }

    if (errs.length > 0) {
        e.preventDefault();
        const old = document.getElementById('clientAlert');
        if (old) old.remove();
        const div = document.createElement('div');
        div.id = 'clientAlert';
        div.innerHTML = '<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Please fix:</strong><ul class="mb-0 mt-1">'
            + errs.map(m => '<li>'+m+'</li>').join('')
            + '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.querySelector('.container.py-4').prepend(div);
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
});

// Init on page load
document.addEventListener('DOMContentLoaded', function() {
    switchRole(document.getElementById('selectedRole').value || 'admin');
    document.getElementById('password').addEventListener('input', checkPwdMatch);
    document.getElementById('confirm_password').addEventListener('input', checkPwdMatch);
});
</script>

<?php include '../includes/footer.php'; ?>
