<?php
/**
 * Database Configuration
 * TLC Consult Web Application
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'tlc_consult');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_URL', 'http://localhost/tlc-consult');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 1800); // 30 minutes

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@tlc-consult.com');
define('FROM_NAME', 'TLC Consult');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

// Application settings
define('ITEMS_PER_PAGE', 12);
define('DEBUG_MODE', true);

// Error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('UTC');
?>