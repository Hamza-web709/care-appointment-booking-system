<?php
/**
 * CARE – Doctor: Profile Edit
 */
require_once '../config/db.php';
require_once '../includes/auth.php';
requireRole('doctor', '../login.php');

$base = '../';
$pageTitle = 'My Profile';

$userId = (int)$_SESSION['user_id'];
$doctor = $conn->query("
    SELECT d.*, c.city_name, u.email, u.username
    FROM doctors d
    JOIN cities c ON d.city_id = c.id
    JOIN users u ON d.user_id = u.id
    WHERE d.user_id = $userId
")->fetch_assoc();
if (!$doctor) {
    setFlash('error', 'Doctor profile not found. Please contact an administrator.');
    header('Location: ../login.php'); exit();
}
$docId = $doctor['id'];

$citiesRaw  = $conn->query("SELECT * FROM cities WHERE status='active' ORDER BY city_name");
$citiesList = $citiesRaw->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $spec      = trim($_POST['specialization'] ?? '');
    $qual      = trim($_POST['qualification'] ?? '');
    $exp       = (int)($_POST['experience'] ?? 0);
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city_id   = (int)($_POST['city_id'] ?? 0);
    $email     = trim($_POST['email'] ?? '');

    // Handle image upload / replace
    $profile_image = $doctor['profile_image']; 
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $res = uploadImage($_FILES['profile_image'], 'profiles');
        if ($res['success']) { 
            // Delete old image if exists
            if ($doctor['profile_image']) {
                deleteOldImage($doctor['profile_image'], 'profiles');
            }
            $profile_image = $res['filename']; 
        } else {
            setFlash('error', $res['message']);
            header('Location: profile.php'); exit();
        }
    }

    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($doctor['profile_image']) {
            deleteOldImage($doctor['profile_image'], 'profiles');
            $profile_image = null;
        }
    }

    // Update email
    $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();

    // Update doctor
    $stmt2 = $conn->prepare("UPDATE doctors SET full_name=?, specialization=?, qualification=?, experience=?, phone=?, address=?, city_id=?, profile_image=? WHERE id=?");
    $stmt2->bind_param('sssissssi', $full_name, $spec, $qual, $exp, $phone, $address, $city_id, $profile_image, $docId);

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
        <p class="mb-0 opacity-75">Update your professional information</p>
    </div>
</div>
<div class="container py-4">
    <?php showFlash(); ?>
    <div class="row g-4">
        <!-- Profile photo card -->
        <div class="col-lg-3">
            <div class="care-card p-4 text-center">
                <div id="previewContainer">
                    <?php if ($doctor['profile_image']): ?>
                    <img id="previewImg" src="../uploads/profiles/<?= e($doctor['profile_image']) ?>" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;border:4px solid var(--care-primary);" alt="">
                    <?php else: ?>
                    <div id="previewImg" class="mx-auto mb-3" style="width:120px;height:120px;border-radius:50%;background:var(--care-blue-light);color:var(--care-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;border:4px solid var(--care-primary);">
                        <i class="bi bi-person"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold">Dr. <?= e($doctor['full_name']) ?></h6>
                <span class="badge bg-care"><?= e($doctor['specialization']) ?></span>
                <p class="text-muted small mt-2 mb-0"><i class="bi bi-geo-alt me-1"></i><?= e($doctor['city_name']) ?></p>
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-care-outline btn-sm w-100">← Dashboard</a>
                </div>
            </div>
        </div>
        <!-- Edit form -->
        <div class="col-lg-9">
            <div class="care-card">
                <div class="card-header"><i class="bi bi-pencil me-2"></i>Edit Profile</div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= e($doctor['full_name']) ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= e($doctor['email']) ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" class="form-control" value="<?= e($doctor['specialization']) ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="<?= e($doctor['qualification']) ?>">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Experience (years)</label>
                                <input type="number" name="experience" class="form-control" value="<?= $doctor['experience'] ?>" min="0">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($doctor['phone']) ?>">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">City</label>
                                <select name="city_id" class="form-select">
                                    <?php foreach ($citiesList as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id']==$doctor['city_id']?'selected':'' ?>><?= e($c['city_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?= e($doctor['address']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <div class="input-group">
                                    <input type="file" name="profile_image" id="profile_image_input" class="form-control" accept="image/*">
                                    <?php if ($doctor['profile_image']): ?>
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
                            <i class="bi bi-check-lg me-2"></i>Save Changes
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
        const previewContainer = document.getElementById('previewContainer');
        
        // If current preview is an icon/placeholder, replace it with an image tag
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
