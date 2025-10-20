<?php
/**
 * Authentication Class
 * TLC Consult Web Application
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $remember_me = false) {
        try {
            // Check if user is locked
            if ($this->isUserLocked($email)) {
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed login attempts. Please try again later.'
                ];
            }
            
            // Get user from database
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                $this->incrementLoginAttempts($email);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                $this->incrementLoginAttempts($email);
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.'
                ];
            }
            
            // Reset login attempts and update last login
            $this->resetLoginAttempts($user['id']);
            $this->updateLastLogin($user['id']);
            
            // Create session
            $this->createUserSession($user, $remember_me);
            
            // Log activity
            $this->logActivity($user['id'], 'login', null, null, 'User logged in');
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUser($user)
            ];
            
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Validate input data
            $validator = new Validator();
            $rules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:' . PASSWORD_MIN_LENGTH,
                'phone' => 'max:20',
                'company' => 'max:100',
                'position' => 'max:100'
            ];
            
            $validation = $validator->validate($data, $rules);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Prepare user data
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password_hash' => $password_hash,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'position' => $data['position'] ?? null,
                'verification_token' => $verification_token,
                'role' => 'user',
                'status' => 'active'
            ];
            
            // Insert user
            $user_id = $this->db->insert('users', $userData);
            
            if ($user_id) {
                // Get created user
                $user = $this->getUserById($user_id);
                
                // Send verification email (implement as needed)
                // $this->sendVerificationEmail($user);
                
                // Log activity
                $this->logActivity($user_id, 'register', 'user', $user_id, 'User registered');
                
                return [
                    'success' => true,
                    'message' => 'Registration successful. Please check your email for verification.',
                    'user' => $this->sanitizeUser($user)
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
            
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.'
            ];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $user_id = $_SESSION['user']['id'];
            
            // Remove session from database
            if (isset($_SESSION['session_id'])) {
                $this->db->delete('user_sessions', 'id = ?', [$_SESSION['session_id']]);
            }
            
            // Log activity
            $this->logActivity($user_id, 'logout', null, null, 'User logged out');
            
            // Clear session
            session_unset();
            session_destroy();
            
            // Clear remember me cookie if set
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
    }
    
    /**
     * Get current logged in user
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
    
    /**
     * Check user role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Require login
     */
    public function requireLogin($redirect = '/login.php') {
        if (!$this->isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
    
    /**
     * Require admin role
     */
    public function requireAdmin($redirect = '/index.php') {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            } else {
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
    
    /**
     * Password reset request
     */
    public function requestPasswordReset($email) {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'If an account with that email exists, a reset link has been sent.'
                ];
            }
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Update user
            $this->db->update('users', [
                'reset_token' => $reset_token,
                'reset_expires' => $reset_expires
            ], 'id = ?', ['id' => $user['id']]);
            
            // Send reset email (implement as needed)
            // $this->sendPasswordResetEmail($user, $reset_token);
            
            // Log activity
            $this->logActivity($user['id'], 'password_reset_request', 'user', $user['id'], 'Password reset requested');
            
            return [
                'success' => true,
                'message' => 'If an account with that email exists, a reset link has been sent.'
            ];
            
        } catch (Exception $e) {
            error_log('Password reset request error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $password) {
        try {
            // Find user by reset token
            $user = $this->db->fetch(
                'SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() AND status = "active"',
                [$token]
            );
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token.'
                ];
            }
            
            // Validate password
            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.'
                ];
            }
            
            // Update password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $this->db->update('users', [
                'password_hash' => $password_hash,
                'reset_token' => null,
                'reset_expires' => null
            ], 'id = ?', ['id' => $user['id']]);
            
            // Log activity
            $this->logActivity($user['id'], 'password_reset', 'user', $user['id'], 'Password was reset');
            
            return [
                'success' => true,
                'message' => 'Password has been reset successfully. You can now login with your new password.'
            ];
            
        } catch (Exception $e) {
            error_log('Password reset error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
    
    // Private helper methods
    
    private function getUserByEmail($email) {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }
    
    private function getUserById($id) {
        return $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }
    
    private function isUserLocked($email) {
        $user = $this->getUserByEmail($email);
        if ($user && $user['locked_until'] && $user['locked_until'] > date('Y-m-d H:i:s')) {
            return true;
        }
        return false;
    }
    
    private function incrementLoginAttempts($email) {
        $user = $this->getUserByEmail($email);
        if ($user) {
            $attempts = $user['login_attempts'] + 1;
            $locked_until = null;
            
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $locked_until = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
            }
            
            $this->db->update('users', [
                'login_attempts' => $attempts,
                'locked_until' => $locked_until
            ], 'id = ?', ['id' => $user['id']]);
        }
    }
    
    private function resetLoginAttempts($user_id) {
        $this->db->update('users', [
            'login_attempts' => 0,
            'locked_until' => null
        ], 'id = ?', ['id' => $user_id]);
    }
    
    private function updateLastLogin($user_id) {
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = ?', ['id' => $user_id]);
    }
    
    private function createUserSession($user, $remember_me = false) {
        // Create session
        $_SESSION['user'] = $this->sanitizeUser($user);
        $_SESSION['login_time'] = time();
        
        // Generate session ID for database
        $session_id = bin2hex(random_bytes(64));
        $_SESSION['session_id'] = $session_id;
        
        // Store session in database
        $session_data = [
            'id' => $session_id,
            'user_id' => $user['id'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', time() + SESSION_LIFETIME)
        ];
        
        $this->db->insert('user_sessions', $session_data);
        
        // Set remember me cookie if requested
        if ($remember_me) {
            $remember_token = bin2hex(random_bytes(32));
            setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
            
            $this->db->update('users', [
                'remember_token' => password_hash($remember_token, PASSWORD_DEFAULT)
            ], 'id = ?', ['id' => $user['id']]);
        }
    }
    
    private function sanitizeUser($user) {
        unset($user['password_hash']);
        unset($user['verification_token']);
        unset($user['reset_token']);
        unset($user['reset_expires']);
        unset($user['login_attempts']);
        unset($user['locked_until']);
        return $user;
    }
    
    private function logActivity($user_id, $action, $entity_type = null, $entity_id = null, $description = null) {
        $log_data = [
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $this->db->insert('activity_logs', $log_data);
    }
}
?>