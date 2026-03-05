<?php
/**
 * Contact Form Handler
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
    
    // Required fields validation
    $required_fields = ['name', 'email', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => ucfirst($field) . ' is required'
            ]);
            exit();
        }
    }
    
    // Sanitize and validate input
    $name = Validator::sanitize($input['name']);
    $email = Validator::sanitize($input['email'], 'email');
    $phone = isset($input['phone']) ? Validator::sanitize($input['phone']) : '';
    $company = isset($input['company']) ? Validator::sanitize($input['company']) : '';
    $subject = isset($input['subject']) ? Validator::sanitize($input['subject']) : '';
    $message = Validator::sanitize($input['message']);
    $type = isset($input['type']) ? Validator::sanitize($input['type']) : 'general';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ]);
        exit();
    }
    
    // Validate message length
    if (strlen($message) < 10) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Message must be at least 10 characters long'
        ]);
        exit();
    }
    
    if (strlen($message) > 5000) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Message cannot exceed 5000 characters'
        ]);
        exit();
    }
    
    // Validate type
    $allowed_types = ['general', 'quote', 'support', 'training'];
    if (!in_array($type, $allowed_types)) {
        $type = 'general';
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
    
    // Rate limiting check (prevent multiple submissions from same IP)
    $db = Database::getInstance();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Check if IP has submitted in the last 5 minutes
    $recent_submission = $db->fetch(
        'SELECT id FROM contact_submissions 
         WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)',
        [$ip_address]
    );
    
    if ($recent_submission) {
        http_response_code(429); // Too Many Requests
        echo json_encode([
            'success' => false,
            'message' => 'Please wait a few minutes before submitting another message'
        ]);
        exit();
    }
    
    // Prepare data for database
    $submission_data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'company' => $company,
        'subject' => $subject,
        'message' => $message,
        'type' => $type,
        'status' => 'new',
        'ip_address' => $ip_address,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
    
    // Insert into database
    $submission_id = $db->insert('contact_submissions', $submission_data);
    
    if ($submission_id) {
        // Send email notification (implement as needed)
        // sendContactNotification($submission_data);
        
        // Send auto-reply email (implement as needed)
        // sendAutoReply($email, $name);
        
        $response = [
            'success' => true,
            'message' => 'Thank you for your message. We will get back to you soon!',
            'submission_id' => $submission_id
        ];
        
        // If this is a form submission (not AJAX), redirect with success
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /contact.html?sent=1');
            exit();
        }
    } else {
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => 'Failed to submit your message. Please try again.'
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while submitting your message. Please try again.'
    ]);
}

/**
 * Send contact notification email (placeholder function)
 */
function sendContactNotification($data) {
    // Implement email sending logic here
    // You can use PHPMailer, SwiftMailer, or built-in mail() function
    
    $to = 'info@tlc-consult.com';
    $subject = 'New Contact Form Submission - ' . $data['subject'];
    
    $body = "
    New contact form submission received:
    
    Name: {$data['name']}
    Email: {$data['email']}
    Phone: {$data['phone']}
    Company: {$data['company']}
    Type: {$data['type']}
    Subject: {$data['subject']}
    
    Message:
    {$data['message']}
    
    IP Address: {$data['ip_address']}
    Submitted: " . date('Y-m-d H:i:s') . "
    ";
    
    $headers = "From: noreply@tlc-consult.com\r\n";
    $headers .= "Reply-To: {$data['email']}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // mail($to, $subject, $body, $headers);
}

/**
 * Send auto-reply email (placeholder function)
 */
function sendAutoReply($email, $name) {
    $subject = 'Thank you for contacting TLC Consult';
    
    $body = "
    Dear {$name},
    
    Thank you for contacting TLC Consult. We have received your message and will respond within 24 hours.
    
    If you have an urgent inquiry, please call us at +1 (555) 123-4567.
    
    Best regards,
    TLC Consult Team
    ";
    
    $headers = "From: info@tlc-consult.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // mail($email, $subject, $body, $headers);
}
?>