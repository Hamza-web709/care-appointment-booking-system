<?php
/**
 * CARE – Medical Appointment System
 * Database Configuration File
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'care_project');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#dc3545;">⚠ Database Connection Failed</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Please check your XAMPP MySQL service is running and your database credentials are correct.</p>
        </div>');
}

// Set charset
$conn->set_charset('utf8mb4');

// Site Configuration
define('SITE_NAME', 'CARE');
define('SITE_TAGLINE', 'Medical Appointment & Information System');
define('BASE_URL', 'http://localhost/care_project/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

?>
