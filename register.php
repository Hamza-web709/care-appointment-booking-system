<?php
/**
 * CARE – Patient & Doctor Registration Page
 */
require_once 'config/db.php';
require_once 'includes/auth.php';
redirectIfLoggedIn();

$base = '';
$pageTitle = 'Register';
$errors = [];

// Get cities for dropdown
$cities = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name");
$citiesList = $cities->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine role from form submission
    $role = $_POST['role_type'] ?? 'patient';
    
    // Sanitize common inputs
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city_id   = (int)($_POST['city_id'] ?? 0);

    // Common Validation
    if (empty($username))   $errors[] = 'Username is required.';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username can only contain letters, numbers, and underscores.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($full_name))  $errors[] = 'Full name is required.';
    if (empty($phone))      $errors[] = 'Phone number is required.';
    if (empty($address))    $errors[] = 'Address is required.';
    if ($city_id <= 0)      $errors[] = 'Please select a city.';

    // Role specific validation & data prep
    if ($role === 'patient') {
        $dob    = trim($_POST['date_of_birth'] ?? '');
        $gender = $_POST['gender'] ?? '';
        // Optional DOB & Gender
    } elseif ($role === 'doctor') {
        $spec = trim($_POST['specialization'] ?? '');
        $qual = trim($_POST['qualification'] ?? '');
        $exp  = (int)($_POST['experience'] ?? 0);
        
        if (empty($spec)) $errors[] = 'Specialization is required.';
        if (empty($qual)) $errors[] = 'Qualification is required.';
        if ($exp < 0)     $errors[] = 'Invalid experience years.';
        
        // Handle optional profile image
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $res = uploadImage($_FILES['profile_image'], 'profiles');
            if ($res['success']) {
                $profile_image = $res['filename'];
            } else {
                $errors[] = $res['error'];
            }
        }
    } else {
        $errors[] = 'Invalid registration type.';
    }

    // Check username/email uniqueness
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $chk->bind_param('ss', $username, $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Username or email is already taken. Please choose another.';
        }
    }

    // Execute insertion
    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

            // 1. Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $username, $email, $hashedPwd, $role);
            $stmt->execute();
            $userId = $conn->insert_id;

            // 2. Insert into role-specific table
            if ($role === 'patient') {
                $dobVal = !empty($dob) ? $dob : null;
                $genderVal = !empty($gender) ? $gender : null;
                $stmt2 = $conn->prepare("INSERT INTO patients (user_id, full_name, phone, address, city_id, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param('isssiss', $userId, $full_name, $phone, $address, $city_id, $dobVal, $genderVal);
                $stmt2->execute();
            } else {
                $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, full_name, specialization, qualification, experience, phone, address, city_id, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param('isssissis', $userId, $full_name, $spec, $qual, $exp, $phone, $address, $city_id, $profile_image);
                $stmt2->execute();
            }

            $conn->commit();
            setFlash('success', 'Registration successful! Please log in.');
            header('Location: login.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            setFlash('error', 'Registration failed: ' . $e->getMessage());
            header("Location: register.php?tab=$role"); exit();
        }
    } else {
        setFlash('error', $errors);
        header("Location: register.php?tab=$role"); exit();
    }
}

// Preserve chosen tab via GET
$activeTab = $_GET['tab'] ?? 'patient';

include 'includes/header.php';
?>
<div class="auth-wrapper" style="min-height:auto;padding:3rem 0;">
    <div class="container py-4">
        <div class="auth-card mx-auto" style="max-width:800px;">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus-fill text-care" style="font-size:3rem;"></i>
                <h3 class="fw-bold mb-1">Create an Account</h3>
                <p class="small opacity-75 mb-0">Join CARE as a Patient or Doctor</p>
            </div>
            
            <div class="auth-body px-md-4">
                <?php showFlash(); ?>

                <!-- Toggle Navigation -->
                <ul class="nav nav-pills nav-fill mb-4 custom-nav-pills" id="registerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'patient' ? 'active' : '' ?>" id="patient-tab" data-bs-toggle="pill" data-bs-target="#patient" type="button" role="tab" aria-controls="patient" aria-selected="<?= $activeTab === 'patient' ? 'true' : 'false' ?>">
                            <i class="bi bi-person me-2"></i>Register as Patient
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeTab === 'doctor' ? 'active' : '' ?>" id="doctor-tab" data-bs-toggle="pill" data-bs-target="#doctor" type="button" role="tab" aria-controls="doctor" aria-selected="<?= $activeTab === 'doctor' ? 'true' : 'false' ?>">
                            <i class="bi bi-heart-pulse me-2"></i>Register as Doctor
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="registerTabsContent">
                    <!-- PATIENT FORM -->
                    <div class="tab-pane fade <?= $activeTab === 'patient' ? 'show active' : '' ?>" id="patient" role="tabpanel" aria-labelledby="patient-tab">
                        <form method="POST" action="register.php" autocomplete="off" novalidate>
                            <input type="hidden" name="role_type" value="patient">
                            
                            <!-- Account Info -->
                            <h6 class="fw-600 text-care mb-3 border-bottom pb-2"><i class="bi bi-shield-lock me-1"></i>Account Credentials</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" placeholder="e.g. hamza_00" autocomplete="off" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="your@email.com" autocomplete="off" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="pwP1" class="form-control" placeholder="Min. 6 characters" autocomplete="new-password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('pwP1',this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" autocomplete="new-password" required>
                                </div>
                            </div>

                            <!-- Personal Info -->
                            <h6 class="fw-600 text-care mb-3 border-bottom pb-2"><i class="bi bi-person me-1"></i>Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Your full name" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+92 300 1234567" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Your home address" required></textarea>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city_id" class="form-select" required>
                                        <option value="">-- Select City --</option>
                                        <?php foreach ($citiesList as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e($c['city_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Gender (Optional)</label>
                                    <select name="gender" class="form-select">
                                        <option value="">-- Select Gender --</option>
                                        <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $val=>$lbl): ?>
                                        <option value="<?= $val ?>"><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Date of Birth (Optional)</label>
                                    <input type="date" name="date_of_birth" class="form-control max-today">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-care w-100 py-3 mt-4 fw-bold">
                                <i class="bi bi-person-check me-2"></i>Register Patient Account
                            </button>
                        </form>
                    </div>

                    <!-- DOCTOR FORM -->
                    <div class="tab-pane fade <?= $activeTab === 'doctor' ? 'show active' : '' ?>" id="doctor" role="tabpanel" aria-labelledby="doctor-tab">
                        <form method="POST" action="register.php" enctype="multipart/form-data" autocomplete="off" novalidate>
                            <input type="hidden" name="role_type" value="doctor">
                            
                            <!-- Account Info -->
                            <h6 class="fw-600 text-teal mb-3 border-bottom pb-2"><i class="bi bi-shield-lock me-1"></i>Account Credentials</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" placeholder="e.g. dr_name" autocomplete="off" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="doctor@clinic.com" autocomplete="off" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="pwD1" class="form-control" placeholder="Min. 6 characters" autocomplete="new-password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('pwD1',this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" autocomplete="new-password" required>
                                </div>
                            </div>

                            <!-- Professional Info -->
                            <h6 class="fw-600 text-teal mb-3 border-bottom pb-2"><i class="bi bi-briefcase me-1"></i>Professional Information</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Your full name" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+92 300 1234567" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Specialization <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" class="form-control" placeholder="e.g. Cardiologist" required>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Qualification <span class="text-danger">*</span></label>
                                    <input type="text" name="qualification" class="form-control" placeholder="e.g. MBBS, FCPS" required>
                                </div>
                                <div class="col-sm-2">
                                    <label class="form-label">Exp (Yrs) <span class="text-danger">*</span></label>
                                    <input type="number" name="experience" class="form-control" min="0" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city_id" class="form-select" required>
                                        <option value="">-- Select City --</option>
                                        <?php foreach ($citiesList as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e($c['city_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Profile Image (Optional)</label>
                                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Clinic/Hospital Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Where do you practice?" required></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-care w-100 py-3 mt-4 fw-bold">
                                <i class="bi bi-heart-pulse me-2"></i>Register Doctor Account
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Already have an account? <a href="login.php" class="text-care fw-600">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Nav Pills for Toggle */
.custom-nav-pills {
    background-color: #f1f5f9;
    border-radius: 10px;
    padding: 5px;
}
.custom-nav-pills .nav-link {
    color: var(--care-text);
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.custom-nav-pills .nav-link:hover {
    color: var(--care-primary);
}
.custom-nav-pills .nav-link.active {
    background-color: white;
    color: var(--care-primary);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.custom-nav-pills .nav-link#doctor-tab.active {
    color: var(--care-teal);
}
.btn-teal {
    background-color: var(--care-teal);
    color: white;
    border: none;
}
.btn-teal:hover {
    background-color: #1b9c9c;
    color: white;
}
.text-teal {
    color: var(--care-teal);
}
</style>

<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
    else { inp.type = 'password'; btn.innerHTML = '<i class="bi bi-eye"></i>'; }
}
</script>
<?php include 'includes/footer.php'; ?>
