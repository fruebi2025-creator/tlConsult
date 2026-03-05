<?php
/**
 * Logout Handler
 * TLC Consult Web Application
 */

require_once __DIR__ . '/../includes/Auth.php';

// Initialize auth
$auth = new Auth();

// Logout user
$auth->logout();

// Redirect to login page with success message
header('Location: /login.php?logout=1');
exit();
?>