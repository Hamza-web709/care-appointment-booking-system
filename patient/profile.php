<?php
/**
 * CARE – Patient: Profile Edit
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('patient', '../login.php');

$base = '../';
$pageTitle = 'My Profile';

$userId = (int)$_SESSION['user_id'];
$patient = $conn->query("
    SELECT p.*, c.city_name, u.email, u.username
    FROM patients p
    JOIN cities c ON p.city_id = c.id
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = $userId
")->fetch_assoc();
if (!$patient) {
    setFlash('error', 'Patient profile not found. Please contact an administrator.');
    header('Location: ../login.php'); exit();
}
$patId = $patient['id'];

$citiesRaw  = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name");
$citiesList = $citiesRaw->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city_id   = (int)($_POST['city_id'] ?? 0);
    $dob = $_POST['date_of_birth'] ?? '';

if (empty($dob)) {
    $dob = null;
} else {
    $d = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$d || $d->format('Y-m-d') !== $dob) {
        $dob = null;
    }
}
    $gender    = $_POST['gender'] ?? '';
    $email     = trim($_POST['email'] ?? '');

    // Update email
    $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();

    // Handle image upload / replace
    $profile_image = $patient['profile_image']; 
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $res = uploadImage($_FILES['profile_image'], 'profiles');
        if ($res['success']) { 
            // Delete old image if exists
            if ($patient['profile_image']) {
                deleteOldImage($patient['profile_image'], 'profiles');
            }
            $profile_image = $res['filename']; 
        } else {
            setFlash('error', $res['message']);
            header('Location: profile.php'); exit();
        }
    }

    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($patient['profile_image']) {
            deleteOldImage($patient['profile_image'], 'profiles');
            $profile_image = null;
        }
    }

    // Update patient
    $stmt2 = $conn->prepare("UPDATE patients SET full_name=?, phone=?, address=?, city_id=?, date_of_birth=?, gender=?, profile_image=? WHERE id=?");
    $stmt2->bind_param('sssisssi', $full_name, $phone, $address, $city_id, $dob, $gender, $profile_image, $patId);

    // Change password?
    if (!empty($_POST['new_password']) && strlen($_POST['new_password']) >= 6) {
        $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pstmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $pstmt->bind_param('si', $hash, $userId);
        $pstmt->execute();
    }

    $stmt2->execute() ? setFlash('success', 'Profile updated successfully!') : setFlash('error', 'Update failed.');
    header('Location: profile.php'); exit();
}

include '../includes/header.php';
?>
<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-person-circle me-2"></i>My Profile</h1>
        <p class="mb-0 opacity-75">Update your personal information</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="row g-4">
        <!-- Patient Info Card -->
        <div class="col-lg-4">
            <div class="care-card p-4 text-center">
                <div id="previewContainer">
                    <?php if ($patient['profile_image']): ?>
                    <img id="previewImg" src="../uploads/profiles/<?= e($patient['profile_image']) ?>" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;border:4px solid var(--care-primary);" alt="">
                    <?php else: ?>
                    <div id="previewImg" class="mx-auto mb-3" style="width:120px;height:120px;border-radius:50%;background:var(--care-blue-light);color:var(--care-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;border:4px solid var(--care-primary);">
                        <i class="bi bi-person"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?= e($patient['full_name']) ?></h5>
                <p class="text-muted small mb-2"><?= e($patient['email']) ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt me-1"></i><?= e($patient['city_name']) ?></span>
                    <span class="badge bg-light text-dark border text-capitalize"><i class="bi bi-gender-ambiguous me-1"></i><?= e($patient['gender']) ?></span>
                </div>
                <hr>
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i><?= e($patient['phone']) ?></li>
                    <li class="mb-2"><i class="bi bi-calendar3 text-muted me-2"></i><?= $patient['date_of_birth'] ? formatDate($patient['date_of_birth']) : 'Not set' ?></li>
                    <li><i class="bi bi-house text-muted me-2"></i><?= e($patient['address']) ?></li>
                </ul>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="care-card">
                <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Edit Information</div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= e($patient['full_name']) ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= e($patient['email']) ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($patient['phone']) ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">City</label>
                                <select name="city_id" class="form-select" required>
                                    <?php foreach ($citiesList as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id']==$patient['city_id']?'selected':'' ?>><?= e($c['city_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= $patient['gender']===$v?'selected':'' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control max-today" value="<?= e($patient['date_of_birth']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2" required><?= e($patient['address']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <div class="input-group">
                                    <input type="file" name="profile_image" id="profile_image_input" class="form-control" accept="image/*">
                                    <?php if ($patient['profile_image']): ?>
                                    <button type="submit" name="remove_image" value="1" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to remove your profile photo?')">
                                        <i class="bi bi-trash me-1"></i>Remove
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">Max size: 2MB. Format: JPG, PNG, WEBP.</small>
                            </div>
                            <div class="col-12"><hr></div>
                            <div class="col-sm-6">
                                <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" name="new_password" class="form-control" placeholder="New password (min. 6 chars)" minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-care mt-4 px-5">
                            <i class="bi bi-check-lg me-2"></i>Save Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Image Preview Logic
document.getElementById('profile_image_input').onchange = function (evt) {
    const [file] = evt.target.files;
    if (file) {
        const preview = document.getElementById('previewImg');
        
        if (preview.tagName === 'DIV') {
            const img = document.createElement('img');
            img.id = 'previewImg';
            img.className = 'rounded-circle mb-3';
            img.style = 'width:120px;height:120px;object-fit:cover;border:4px solid var(--care-primary);';
            preview.replaceWith(img);
            img.src = URL.createObjectURL(file);
        } else {
            preview.src = URL.createObjectURL(file);
        }
    }
}
</script>
<?php include '../includes/footer.php'; ?>
