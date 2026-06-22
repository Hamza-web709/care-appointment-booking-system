<?php
/**
 * CARE – Forgot Password
 * Generates a password reset token and shows the reset link on-screen
 * (no SMTP needed – link is displayed directly since this is a local XAMPP project).
 */
require_once 'config/db.php';
require_once 'includes/auth.php';

$base      = '';
$pageTitle = 'Forgot Password';

date_default_timezone_set('Asia/Karachi');

$successLink = '';
$errorMsg    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // Generate secure token
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $upd = $conn->prepare(
                "UPDATE users SET reset_token=?, token_expiry=? WHERE email=?"
            );
            $upd->bind_param('sss', $token, $expiry, $email);
            $upd->execute();

            // Build reset link using the configured BASE_URL
            $successLink = BASE_URL . 'reset_password.php?token=' . $token;
        } else {
            // Show generic message to prevent email enumeration
            $errorMsg = 'If that email exists in our system, a reset link has been generated below.';
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1><i class="bi bi-key me-2"></i>Forgot Password</h1>
        <p class="mb-0 opacity-75">Generate a password reset link for your account</p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-sm-8">

            <?php if ($successLink): ?>
            <!-- Reset link generated successfully -->
            <div class="care-card">
                <div class="card-header" style="background:linear-gradient(135deg,#19875422,#20c99722);">
                    <i class="bi bi-check-circle-fill me-2 text-success"></i>Reset Link Generated
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">
                        Click the link below to reset your password. The link expires in <strong>1 hour</strong>.
                    </p>
                    <div class="alert alert-success d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-link-45deg fs-5 mt-1 flex-shrink-0"></i>
                        <a href="<?= $successLink ?>" class="text-break small"><?= $successLink ?></a>
                    </div>
                    <a href="<?= $successLink ?>" class="btn btn-care w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Go to Reset Page
                    </a>
                </div>
            </div>

            <?php else: ?>
            <!-- Forgot password form -->
            <div class="care-card">
                <div class="card-header">
                    <i class="bi bi-envelope-open me-2"></i>Enter Your Email
                </div>
                <div class="card-body p-4">

                    <?php if ($errorMsg): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= e($errorMsg) ?>
                    </div>
                    <?php endif; ?>

                    <p class="text-muted small mb-4">
                        Enter the email address linked to your CARE account. A reset link will be generated for you.
                    </p>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email"
                                       class="form-control" placeholder="you@example.com"
                                       value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-care w-100">
                            <i class="bi bi-key me-2"></i>Generate Reset Link
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
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>