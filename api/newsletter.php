<?php
/**
 * Newsletter Subscription Handler
 * TLC Consult Web Application
 */

require_once __DIR__ . '/../includes/Database.php';
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
    // Get input data
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
    
    // Validate required email field
    if (empty($input['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email address is required'
        ]);
        exit();
    }
    
    // Sanitize and validate input
    $email = Validator::sanitize($input['email'], 'email');
    $name = isset($input['name']) ? Validator::sanitize($input['name']) : '';
    $source = isset($input['source']) ? Validator::sanitize($input['source']) : 'website';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit();
    }
    
    // Check for spam (basic honeypot check)
    if (isset($input['website']) && !empty($input['website'])) {
        // This is likely spam - honeypot field should be empty
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Spam detected'
        ]);
        exit();
    }
    
    $db = Database::getInstance();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Check if email is already subscribed
    $existing_subscriber = $db->fetch(
        'SELECT id, status FROM newsletter_subscribers WHERE email = ?',
        [$email]
    );
    
    if ($existing_subscriber) {
        if ($existing_subscriber['status'] === 'active') {
            // Already subscribed and active
            echo json_encode([
                'success' => false,
                'message' => 'This email address is already subscribed to our newsletter'
            ]);
            exit();
        } else {
            // Reactivate existing subscription
            $updated = $db->update('newsletter_subscribers', [
                'status' => 'active',
                'subscription_date' => date('Y-m-d H:i:s'),
                'unsubscription_date' => null,
                'name' => $name,
                'source' => $source,
                'ip_address' => $ip_address
            ], 'id = ?', ['id' => $existing_subscriber['id']]);
            
            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Welcome back! Your newsletter subscription has been reactivated.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to reactivate subscription. Please try again.'
                ]);
            }
            exit();
        }
    }
    
    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));
    
    // Prepare subscription data
    $subscription_data = [
        'email' => $email,
        'name' => $name,
        'status' => 'active', // Set to active immediately, or 'pending' if email verification is required
        'verification_token' => $verification_token,
        'verified' => 1, // Set to 0 if email verification is required
        'source' => $source,
        'ip_address' => $ip_address
    ];
    
    // Insert subscription
    $subscription_id = $db->insert('newsletter_subscribers', $subscription_data);
    
    if ($subscription_id) {
        // Send welcome email (implement as needed)
        // sendWelcomeEmail($email, $name, $verification_token);
        
        $response = [
            'success' => true,
            'message' => 'Thank you for subscribing! You will receive our latest updates and insights.',
            'subscription_id' => $subscription_id
        ];
        
        // If this is a form submission (not AJAX), redirect with success
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $redirect_url = $_SERVER['HTTP_REFERER'] ?? '/index.html';
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'newsletter=subscribed';
            header('Location: ' . $redirect_url);
            exit();
        }
    } else {
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => 'Failed to subscribe. Please try again.'
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your subscription. Please try again.'
    ]);
}

/**
 * Send welcome email (placeholder function)
 */
function sendWelcomeEmail($email, $name, $verification_token) {
    $subject = 'Welcome to TLC Consult Newsletter';
    
    $body = "
    Dear " . ($name ?: 'Subscriber') . ",
    
    Thank you for subscribing to the TLC Consult newsletter!
    
    You'll receive:
    - Weekly quality insights
    - Exclusive resources
    - Industry updates
    - Training announcements
    
    If you didn't subscribe to this newsletter, you can unsubscribe at any time using the link below.
    
    Best regards,
    TLC Consult Team
    
    ---
    Unsubscribe: " . APP_URL . "/unsubscribe.php?token=" . $verification_token . "
    ";
    
    $headers = "From: info@tlc-consult.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // mail($email, $subject, $body, $headers);
}
?>