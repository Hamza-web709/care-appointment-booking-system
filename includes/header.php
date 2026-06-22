<?php
/**
 * CARE Medical System – Production-Ready Header
 * Reusable component with modern UI and dynamic PHP functionality.
 */

// 1. Session & Path Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect base path automatically
// Logic: Calculate how many directories deep we are from the root (htdocs/care_project)
$depth = substr_count(str_replace('\\', '/', $_SERVER['PHP_SELF']), '/') - 2;
$base = str_repeat('../', $depth > 0 ? $depth : 0);

// Ensure auth functions are available (assuming auth.php is in includes/)
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CARE – Modern Medical Appointment & Information System.">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | CARE' : 'CARE – Medical System' ?></title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CARE Custom Styles -->
    <link href="<?= $base ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= $base ?>assets/css/header.css" rel="stylesheet">
</head>
<body>

<header class="care-header sticky-top">
    <nav class="container d-flex align-items-center justify-content-between" role="navigation">
        
        <!-- Left: Logo -->
        <a href="<?= $base ?>index.php" class="care-brand" aria-label="CARE Home">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>CARE</span>
        </a>

        <!-- Center: Desktop Navigation -->
        <ul class="care-nav-list mb-0 d-none d-lg-flex">
            <li><a href="<?= $base ?>index.php" class="care-nav-link">Home</a></li>
            <li><a href="<?= $base ?>doctors.php" class="care-nav-link">Doctors</a></li>
            <li><a href="<?= $base ?>diseases.php" class="care-nav-link">Diseases</a></li>
            <li><a href="<?= $base ?>news.php" class="care-nav-link">Health News</a></li>
        </ul>

        <!-- Right: Auth & User Controls -->
        <div class="care-user-controls">
            <?php if (isLoggedIn()): ?>
                <?php 
                    $role = getUserRole();
                    $username = $_SESSION['username'] ?? 'User';
                    $initial = strtoupper(substr($username, 0, 1));
                ?>
                <!-- Notification Bell -->
                <button class="care-notif-btn" aria-label="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span class="pulse-badge"></span>
                </button>

                <!-- Profile Dropdown -->
                <div class="care-profile-wrapper">
                    <button class="care-profile-btn" id="care-profile-btn" aria-haspopup="true" aria-expanded="false" aria-label="User Profile">
                        <div class="care-avatar"><?= e($initial) ?></div>
                        <div class="care-user-info">
                            <span class="care-user-name"><?= e($username) ?></span>
                            <span class="care-user-role"><?= e($role) ?></span>
                        </div>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>

                    <div class="care-dropdown-menu" id="care-profile-dropdown">
                        <a href="<?= $base ?><?= $role ?>/dashboard.php" class="care-dropdown-item">
                            <i class="bi bi-grid-fill"></i> Dashboard
                        </a>
                        <a href="<?= $base ?><?= $role ?>/profile.php" class="care-dropdown-item">
                            <i class="bi bi-person-fill"></i> My Profile
                        </a>
                        <div class="care-dropdown-divider"></div>
                        <a href="<?= $base ?>logout.php" class="care-dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Not Logged In -->
                <a href="<?= $base ?>login.php" class="btn-login d-none d-sm-block">Login</a>
                <a href="<?= $base ?>register.php" class="btn-register">Register</a>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button class="care-mobile-toggle d-lg-none" id="care-mobile-toggle" aria-label="Toggle Navigation" aria-expanded="false">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Navigation Menu -->
    <div class="care-mobile-menu d-lg-none" id="care-mobile-menu" aria-hidden="true">
        <a href="<?= $base ?>index.php" class="care-dropdown-item"><i class="bi bi-house-door-fill"></i> Home</a>
        <a href="<?= $base ?>doctors.php" class="care-dropdown-item"><i class="bi bi-people-fill"></i> Doctors</a>
        <a href="<?= $base ?>diseases.php" class="care-dropdown-item"><i class="bi bi-virus"></i> Diseases</a>
        <a href="<?= $base ?>news.php" class="care-dropdown-item"><i class="bi bi-newspaper"></i> Health News</a>
        
        <?php if (isLoggedIn()): ?>
            <div class="care-dropdown-divider"></div>
            <?php $role = getUserRole(); ?>
            <a href="<?= $base ?><?= $role ?>/dashboard.php" class="care-dropdown-item">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="<?= $base ?>logout.php" class="care-dropdown-item text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        <?php else: ?>
            <div class="care-dropdown-divider"></div>
            <a href="<?= $base ?>login.php" class="care-dropdown-item"><i class="bi bi-box-arrow-in-right"></i> Login</a>
        <?php endif; ?>
    </div>
</header>

<!-- Flash Messages Injection -->
<div class="container mt-3" id="flash-container">
    <?php if (function_exists('showFlash')) showFlash(); ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>assets/js/header.js"></script>