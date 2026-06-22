<?php
/**
 * CARE – Reset Password
 * Validates the reset token and allows the user to set a new password.
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$base      = '';
$pageTitle = 'Reset Password';

date_default_timezone_set('Asia/Karachi');

// ── Validate token from URL ───────────────────────────────────────────────────
$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    setFlash('error', 'No reset token provided. Please request a new reset link.');
    header('Location: forgot_password.php');
    exit();
}

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()"
);
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    $stmt->close();
    setFlash('error', 'This reset link is invalid or has expired. Please request a new one.');
    header('Location: forgot_password.php');
    exit();
}
$stmt->close();

// ── Handle new password submission ────────────────────────────────────────────
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $upd = $conn->prepare(
            "UPDATE users
             SET password=?, reset_token=NULL, token_expiry=NULL
             WHERE reset_token=?"
        );
        $upd->bind_param('ss', $hashed, $token);
        $upd->execute();

        setFlash('success', 'Password updated successfully! Please log in with your new password.');
        header('Location: login.php');
        exit();
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-shield-lock me-2"></i>Reset Password</h1>
        <p class="mb-0 opacity-75">Set a new secure password for your account</p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-sm-8">

            <div class="care-card">
                <div class="card-header">
                    <i class="bi bi-lock-fill me-2"></i>Enter New Password
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                        <ul class="mb-0 ps-2">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <p class="text-muted small mb-4">
                        Choose a strong password. It must be at least 6 characters long.
                    </p>

                    <form method="POST">
                        <!-- Token is carried via GET in the URL, no hidden field needed since
                             the form action posts to the same URL which still has ?token=… -->

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password"
                                       class="form-control" placeholder="Min. 6 characters"
                                       minlength="6" required autofocus>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('password')">
                                    <i class="bi bi-eye" id="password_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password"
                                       class="form-control" placeholder="Re-enter password" required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('confirm_password')">
                                    <i class="bi bi-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                            <div id="pwdMatchMsg" class="form-text mt-1"></div>
                        </div>

                        <button type="submit" class="btn btn-care w-100">
                            <i class="bi bi-check-circle me-2"></i>Update Password
                        </button>
                    </form>

                    <hr class="my-3">
                    <div class="text-center">
                        <a href="login.php" class="text-muted small">
                            <i class="bi bi-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function togglePwd(id) {
    const f = document.getElementById(id);
    const i = document.getElementById(id + '_icon');
    f.type = (f.type === 'password') ? 'text' : 'password';
    i.className = (f.type === 'text') ? 'bi bi-eye-slash' : 'bi bi-eye';
}

document.getElementById('confirm_password').addEventListener('input', function () {
    const pwd  = document.getElementById('password').value;
    const msg  = document.getElementById('pwdMatchMsg');
    msg.innerHTML = (this.value === pwd)
        ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</span>'
        : '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</span>';
});
</script>

<?php include 'includes/footer.php'; ?>