<?php
/**
 * Validator Class
 * TLC Consult Web Application
 */

require_once __DIR__ . '/Database.php';

class Validator {
    private $db;
    private $errors = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Validate data against rules
     */
    public function validate($data, $rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            $ruleList = explode('|', $rule);
            
            foreach ($ruleList as $singleRule) {
                $this->applyRule($field, $value, $singleRule, $data);
            }
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors
        ];
    }
    
    /**
     * Apply single validation rule
     */
    private function applyRule($field, $value, $rule, $allData) {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $ruleValue . ' characters');
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be no more than ' . $ruleValue . ' characters');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Please enter a valid email address');
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a number');
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be an integer');
                }
                break;
                
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'Please enter a valid URL');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' may only contain letters');
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' may only contain letters and numbers');
                }
                break;
                
            case 'alpha_dash':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' may only contain letters, numbers, dashes and underscores');
                }
                break;
                
            case 'regex':
                if (!empty($value) && !preg_match($ruleValue, $value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' format is invalid');
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'Please enter a valid date');
                }
                break;
                
            case 'date_format':
                if (!empty($value)) {
                    $date = DateTime::createFromFormat($ruleValue, $value);
                    if (!$date || $date->format($ruleValue) !== $value) {
                        $this->addError($field, 'Date must be in format: ' . $ruleValue);
                    }
                }
                break;
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $allowedValues));
                }
                break;
                
            case 'not_in':
                $forbiddenValues = explode(',', $ruleValue);
                if (!empty($value) && in_array($value, $forbiddenValues)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' cannot be: ' . implode(', ', $forbiddenValues));
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($allData[$confirmField] ?? '')) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' confirmation does not match');
                }
                break;
                
            case 'unique':
                if (!empty($value)) {
                    $tableParts = explode(',', $ruleValue);
                    $table = $tableParts[0];
                    $column = $tableParts[1] ?? $field;
                    $ignoreId = $tableParts[2] ?? null;
                    
                    $whereClause = "$column = ?";
                    $params = [$value];
                    
                    if ($ignoreId) {
                        $whereClause .= " AND id != ?";
                        $params[] = $ignoreId;
                    }
                    
                    if ($this->db->exists($table, $whereClause, $params)) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' already exists');
                    }
                }
                break;
                
            case 'exists':
                if (!empty($value)) {
                    $tableParts = explode(',', $ruleValue);
                    $table = $tableParts[0];
                    $column = $tableParts[1] ?? $field;
                    
                    if (!$this->db->exists($table, "$column = ?", [$value])) {
                        $this->addError($field, 'Selected ' . str_replace('_', ' ', $field) . ' is invalid');
                    }
                }
                break;
                
            case 'file':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $this->validateFile($field, $_FILES[$field]);
                }
                break;
                
            case 'image':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $this->validateImage($field, $_FILES[$field]);
                }
                break;
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($field, $file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, 'File upload failed');
            return;
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $this->addError($field, 'File size must be less than ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES);
        
        if (!in_array($extension, $allowedTypes)) {
            $this->addError($field, 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
        }
    }
    
    /**
     * Validate uploaded image
     */
    private function validateImage($field, $file) {
        // First validate as file
        $this->validateFile($field, $file);
        
        if (!empty($this->errors[$field])) {
            return; // Don't proceed if file validation failed
        }
        
        // Check if it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->addError($field, 'File must be a valid image');
            return;
        }
        
        // Check image dimensions (optional)
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        if ($width > 5000 || $height > 5000) {
            $this->addError($field, 'Image dimensions too large (max 5000x5000 pixels)');
        }
        
        // Check image extension against MIME type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
            $this->addError($field, 'Image type not allowed. Allowed types: ' . implode(', ', ALLOWED_IMAGE_TYPES));
        }
    }
    
    /**
     * Add error message
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data, $type = 'string') {
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
                
            case 'email':
                return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var(trim($data), FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'html':
                return htmlpurifier_sanitize($data); // Requires HTML Purifier library
                
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRF() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        $pattern = '/^[\+]?[1-9][\d]{0,15}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * Validate strong password
     */
    public static function validateStrongPassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password);
    }
    
    /**
     * Check if string contains only safe characters (prevent XSS)
     */
    public static function isSafeString($string) {
        return $string === strip_tags($string);
    }
}
?>