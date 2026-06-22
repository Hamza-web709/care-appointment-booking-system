<?php
/**
 * CARE – Authentication & Session Helper
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin($redirect = '../login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit();
    }
}

/**
 * Require specific role
 */
function requireRole($role, $redirect = '../login.php') {
    requireLogin($redirect);
    if (getUserRole() !== $role) {
        header('Location: ' . $redirect . '?error=unauthorized');
        exit();
    }
}

/**
 * Redirect if already logged in (for login/register pages)
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        $role = getUserRole();
        switch ($role) {
            case 'admin':   header('Location: admin/dashboard.php'); break;
            case 'doctor':  header('Location: doctor/dashboard.php'); break;
            case 'patient': header('Location: patient/dashboard.php'); break;
        }
        exit();
    }
}

/**
 * Sanitize output (XSS protection)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message helper
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : $flash['type']);
        echo '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">';
        if (is_array($flash['message'])) {
            echo '<ul class="mb-0' . (count($flash['message']) > 1 ? '' : ' list-unstyled') . '">';
            foreach ($flash['message'] as $msg) {
                echo '<li>' . e($msg) . '</li>';
            }
            echo '</ul>';
        } else {
            echo e($flash['message']);
        }
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Format date
 */
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('d M Y', strtotime($date));
}

/**
 * Format time to 12-hour format
 */
function formatTime($time) {
    if (!$time) return 'N/A';
    return date('h:i A', strtotime($time));
}

/**
 * Get status badge HTML
 */
function statusBadge($status) {
    $colors = [
        'active'    => 'success',
        'inactive'  => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'cancelled' => 'danger',
        'completed' => 'primary',
        'available' => 'info',
        'booked'    => 'dark',
    ];
    $color = $colors[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
}

/**
 * Upload file (image) - Secure Version
 */
function uploadImage($file, $folder = 'profiles') {
    $baseDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    
    // 1. Validate extension/mime
    $fileInfo = pathinfo($file['name']);
    $ext = strtolower($fileInfo['extension']);
    
    if (!in_array($file['type'], $allowed) || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        return ['success' => false, 'message' => 'Only JPG, PNG and WEBP images are allowed.'];
    }

    // 2. Validate size (2MB limit per requirements)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image size must be under 2MB.'];
    }
    
    // 3. Generate secure random filename
    $filename = bin2hex(random_bytes(10)) . '.' . $ext;
    $destination = $baseDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'message' => 'Failed to upload image.'];
}

/**
 * Delete file from uploads
 */
function deleteOldImage($filename, $folder = 'profiles') {
    if (!$filename) return;
    $filePath = __DIR__ . '/../uploads/' . $folder . '/' . $filename;
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }
}
?>
