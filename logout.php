<?php
/**
 * CARE – Logout
 */
require_once 'includes/auth.php';
session_destroy();
header('Location: login.php?msg=logged_out');
exit();
?>
