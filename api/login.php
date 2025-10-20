<?php
/**
 * Login API Handler
 * TLC Consult Web Application
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Validator.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON decode fails, try form data
    if ($input === null) {
        $input = $_POST;
    }
    
    // Validate CSRF token for form submissions
    if (isset($input['csrf_token']) && !Validator::validateCSRF($input['csrf_token'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token'
        ]);
        exit();
    }
    
    // Validate required fields
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit();
    }
    
    // Sanitize input
    $email = Validator::sanitize($input['email'], 'email');
    $password = $input['password']; // Don't sanitize password as it may contain special chars
    $remember_me = !empty($input['remember_me']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit();
    }
    
    // Attempt login
    $auth = new Auth();
    $result = $auth->login($email, $password, $remember_me);
    
    // Set appropriate HTTP status code
    if ($result['success']) {
        http_response_code(200);
        
        // If this is a form submission (not AJAX), redirect
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit();
        }
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Login API error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal error occurred. Please try again later.'
    ]);
}
?>