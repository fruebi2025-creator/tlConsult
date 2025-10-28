<?php
/**
 * Registration API Handler
 * TL Consulting Web Application
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
    
    // Required fields
    $required_fields = ['first_name', 'last_name', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
            ]);
            exit();
        }
    }
    
    // Sanitize input data
    $data = [];
    $data['first_name'] = Validator::sanitize($input['first_name']);
    $data['last_name'] = Validator::sanitize($input['last_name']);
    $data['email'] = Validator::sanitize($input['email'], 'email');
    $data['password'] = $input['password']; // Don't sanitize password
    $data['phone'] = isset($input['phone']) ? Validator::sanitize($input['phone']) : '';
    $data['company'] = isset($input['company']) ? Validator::sanitize($input['company']) : '';
    $data['position'] = isset($input['position']) ? Validator::sanitize($input['position']) : '';
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit();
    }
    
    // Validate password strength
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
        ]);
        exit();
    }
    
    // Check if password confirmation matches (if provided)
    if (isset($input['password_confirmation']) && $data['password'] !== $input['password_confirmation']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password confirmation does not match'
        ]);
        exit();
    }
    
    // Check if terms are accepted (if required)
    if (isset($input['accept_terms']) && !$input['accept_terms']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'You must accept the terms and conditions'
        ]);
        exit();
    }
    
    // Attempt registration
    $auth = new Auth();
    $result = $auth->register($data);
    
    // Set appropriate HTTP status code
    if ($result['success']) {
        http_response_code(201); // Created
        
        // If this is a form submission (not AJAX), redirect to login
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /login.php?registered=1');
            exit();
        }
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Registration API error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal error occurred. Please try again later.'
    ]);
}
?>