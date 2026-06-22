<?php
/**
 * CARE – Login Page 
 * Fully corrected login system matching user requirements.
 */
require_once 'config/db.php';
require_once 'includes/auth.php';
redirectIfLoggedIn();

// Ensure session is started (usually handled in auth.php, but good practice here just in case)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '';
$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and fetch input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($password)) {
        setFlash('error', 'Please enter both username and password.');
        header('Location: login.php'); exit();
    } else {
        // 1. Fetch user by username only
        $stmt = $conn->prepare("SELECT id, username, email, password, role, status FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // 2. Check if record exists
        if ($result->num_rows === 0) {
            setFlash('error', 'User not found.');
            header('Location: login.php'); exit();
        } else {
            $user = $result->fetch_assoc();

            // 3. Verify password
            if (!password_verify($password, $user['password'])) {
                setFlash('error', 'Incorrect password.');
                header('Location: login.php'); exit();
            } 
            // 4. Check status
            elseif ($user['status'] !== 'active') {
                setFlash('error', 'Account inactive.');
                header('Location: login.php'); exit();
            } 
            // 5. Success
            else {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];

                setFlash('success', 'Welcome back, ' . e($user['username']) . '!');
                
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } elseif ($user['role'] === 'doctor') {
                    header('Location: doctor/dashboard.php');
                } elseif ($user['role'] === 'patient') {
                    header('Location: patient/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            }
        }
    }
}

include 'includes/header.php';
?>
<div class="auth-wrapper">
    <div class="container">
        <div class="auth-card mx-auto">
            <div class="auth-header">
                <i class="bi bi-lock-fill"></i>
                <h4 class="fw-bold mb-1">Welcome Back</h4>
                <p class="small opacity-75 mb-0">Sign in to your CARE account</p>
            </div>
            <div class="auth-body">
                <?php showFlash(); ?>
                
                <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
                <div class="alert alert-warning">You don't have permission to access that page.</div>
                <?php endif; ?>

                <form method="POST" action="login.php" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person me-1"></i>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter username"
                               autocomplete="off" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-lock me-1"></i>Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control" 
                                   placeholder="Enter password" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('loginPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <a href="forgot_password.php" class="btn btn-forgot-password w-100 py-2 mb-3" style="font-size: 12px; color: #666; margin: 0 auto;">Forgot Password?</a>
                    <button type="submit" class="btn btn-care w-100 py-2 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                    
                    <hr>
                    <div class="text-center">
                        <p class="text-muted small mb-0">Don't have an account? <a href="register.php" class="text-care fw-600">Register Here</a></p>
                    </div>
                </form>
                
                <!-- Debugging Block (Commented)
                <?php /*
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($user)) {
                    echo '<pre class="mt-3 p-3 bg-light">';
                    print_r([
                        'Inputted Username' => $username,
                        'DB Record Found' => $user ? 'Yes' : 'No',
                        'DB Role' => $user['role'],
                        'DB Status' => $user['status'],
                        'Password Verify Result' => password_verify($password, $user['password']) ? 'True' : 'False',
                        'DB Hash' => $user['password']
                    ]);
                    echo '</pre>';
                }
                */ ?>
                -->
            </div>
        </div>
    </div>
</div>
<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
    else { inp.type = 'password'; btn.innerHTML = '<i class="bi bi-eye"></i>'; }
}
</script>
<?php include 'includes/footer.php'; ?>
