<?php
/**
 * Dashboard API Handler
 * TLC Consult Web Application
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Require authentication
    $auth = new Auth();
    $auth->requireLogin();
    
    $user = $auth->getCurrentUser();
    $user_id = $user['id'];
    $db = Database::getInstance();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? 'overview';
    
    switch ($action) {
        case 'overview':
            // Get user dashboard statistics
            $stats = $db->fetch(
                'SELECT * FROM user_dashboard_stats WHERE user_id = ?',
                [$user_id]
            );
            
            // Get active courses with progress
            $activeCourses = $db->fetchAll(
                'SELECT c.id, c.title, c.slug, ce.progress_percentage, 
                        cm.title as current_module
                 FROM course_enrollments ce
                 JOIN courses c ON ce.course_id = c.id
                 LEFT JOIN course_modules cm ON c.id = cm.course_id
                 WHERE ce.user_id = ? AND ce.status = "active"
                 ORDER BY ce.updated_at DESC
                 LIMIT 5',
                [$user_id]
            );
            
            // Get recent activity
            $recentActivity = $db->fetchAll(
                'SELECT action, description, created_at
                 FROM activity_logs
                 WHERE user_id = ?
                 ORDER BY created_at DESC
                 LIMIT 10',
                [$user_id]
            );
            
            // Get upcoming deadlines (mock data for now)
            $upcomingDeadlines = [
                [
                    'title' => 'Quality Management System Assessment',
                    'course' => 'ISO 9001:2015 Course',
                    'due_date' => date('Y-m-d', strtotime('+3 days')),
                    'days_left' => 3
                ],
                [
                    'title' => 'Final Exam',
                    'course' => 'Internal Auditing Course',
                    'due_date' => date('Y-m-d', strtotime('+10 days')),
                    'days_left' => 10
                ]
            ];
            
            $response = [
                'success' => true,
                'data' => [
                    'stats' => $stats ?: [
                        'total_courses' => 0,
                        'active_courses' => 0,
                        'completed_courses' => 0,
                        'total_certificates' => 0,
                        'total_time_minutes' => 0
                    ],
                    'active_courses' => $activeCourses,
                    'recent_activity' => $recentActivity,
                    'upcoming_deadlines' => $upcomingDeadlines
                ]
            ];
            break;
            
        case 'courses':
            $status = $_GET['status'] ?? 'all';
            $page = (int)($_GET['page'] ?? 1);
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            $whereClause = 'ce.user_id = ?';
            $params = [$user_id];
            
            if ($status !== 'all') {
                $whereClause .= ' AND ce.status = ?';
                $params[] = $status;
            }
            
            // Get courses
            $courses = $db->fetchAll(
                "SELECT c.id, c.title, c.slug, c.description, c.featured_image,
                        c.duration_hours, c.module_count, c.level,
                        ce.status, ce.progress_percentage, ce.start_date,
                        ce.completion_date, cc.name as category_name
                 FROM course_enrollments ce
                 JOIN courses c ON ce.course_id = c.id
                 LEFT JOIN course_categories cc ON c.category_id = cc.id
                 WHERE {$whereClause}
                 ORDER BY ce.updated_at DESC
                 LIMIT {$limit} OFFSET {$offset}",
                $params
            );
            
            // Get total count
            $totalCount = $db->fetch(
                "SELECT COUNT(*) as count FROM course_enrollments ce WHERE {$whereClause}",
                $params
            )['count'];
            
            $response = [
                'success' => true,
                'data' => [
                    'courses' => $courses,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $totalCount,
                        'pages' => ceil($totalCount / $limit)
                    ]
                ]
            ];
            break;
            
        case 'certificates':
            // Get user certificates
            $certificates = $db->fetchAll(
                'SELECT uc.id, uc.certificate_number, uc.issued_date, uc.expiry_date,
                        uc.verification_code, uc.status, c.title as course_title
                 FROM user_certificates uc
                 JOIN courses c ON uc.course_id = c.id
                 WHERE uc.user_id = ?
                 ORDER BY uc.issued_date DESC',
                [$user_id]
            );
            
            $response = [
                'success' => true,
                'data' => [
                    'certificates' => $certificates
                ]
            ];
            break;
            
        case 'profile':
            // Get user profile data
            $profile = $db->fetch(
                'SELECT id, first_name, last_name, email, phone, company, position,
                        profile_image, created_at, last_login
                 FROM users
                 WHERE id = ?',
                [$user_id]
            );
            
            $response = [
                'success' => true,
                'data' => [
                    'profile' => $profile
                ]
            ];
            break;
            
        case 'update_profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            
            // Validate and sanitize input
            $updateData = [];
            $allowedFields = ['first_name', 'last_name', 'phone', 'company', 'position'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = Validator::sanitize($input[$field]);
                }
            }
            
            if (empty($updateData)) {
                throw new Exception('No valid fields to update');
            }
            
            // Update user profile
            $updated = $db->update('users', $updateData, 'id = ?', ['id' => $user_id]);
            
            if ($updated) {
                // Log activity
                $auth->logActivity($user_id, 'profile_update', 'user', $user_id, 'Profile updated');
                
                $response = [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No changes were made'
                ];
            }
            break;
            
        case 'change_password':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('All password fields are required');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New password confirmation does not match');
            }
            
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                throw new Exception('New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long');
            }
            
            // Get current user data
            $currentUser = $db->fetch('SELECT password_hash FROM users WHERE id = ?', [$user_id]);
            
            if (!password_verify($currentPassword, $currentUser['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->update('users', ['password_hash' => $newPasswordHash], 'id = ?', ['id' => $user_id]);
            
            // Log activity
            $auth->logActivity($user_id, 'password_change', 'user', $user_id, 'Password changed');
            
            $response = [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>