<?php
/**
 * User Dashboard
 * TL Consulting Web Application
 */

require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Database.php';

// Require authentication
$auth = new Auth();
$auth->requireLogin('/login.php');

$user = $auth->getCurrentUser();
$db = Database::getInstance();

// Get user statistics
$stats = $db->fetch(
    'SELECT * FROM user_dashboard_stats WHERE user_id = ?',
    [$user['id']]
) ?: [
    'total_courses' => 0,
    'active_courses' => 0,
    'completed_courses' => 0,
    'total_certificates' => 0,
    'total_time_minutes' => 0
];

// Convert minutes to hours
$stats['total_hours'] = round($stats['total_time_minutes'] / 60, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TL Consulting</title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>TL Consulting</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="about.html" class="nav-link">About Us</a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Services <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="services-qa.html">Quality Control & Assurance</a></li>
                        <li><a href="services-training.html">Training & Education</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="gallery.html" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="industries.html" class="nav-link">Industries</a>
                </li>
                <li class="nav-item">
                    <a href="resources.html" class="nav-link">Resources</a>
                </li>
                <li class="nav-item">
                    <a href="contact.html" class="nav-link">Contact</a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link user-menu">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="/api/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Dashboard Layout -->
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Dashboard</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#overview" class="sidebar-link active" data-section="overview">
                        <i class="fas fa-tachometer-alt"></i>
                        Overview
                    </a></li>
                    <li><a href="#courses" class="sidebar-link" data-section="courses">
                        <i class="fas fa-book"></i>
                        My Courses
                    </a></li>
                    <li><a href="#certificates" class="sidebar-link" data-section="certificates">
                        <i class="fas fa-certificate"></i>
                        Certificates
                    </a></li>
                    <li><a href="#profile" class="sidebar-link" data-section="profile">
                        <i class="fas fa-user"></i>
                        Profile
                    </a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Overview Section -->
            <section id="overview" class="dashboard-section active">
                <div class="dashboard-header">
                    <h1>Welcome back, <?= htmlspecialchars($user['first_name']) ?>!</h1>
                    <p>Here's your learning progress and activity summary</p>
                </div>

                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['active_courses'] ?></h3>
                            <p>Active Courses</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['total_certificates'] ?></h3>
                            <p>Certificates Earned</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $stats['total_hours'] ?></h3>
                            <p>Hours Completed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <h3>85%</h3>
                            <p>Average Score</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Continue Learning</h3>
                        </div>
                        <div class="course-progress" id="activeCourses">
                            <!-- Dynamic content loaded via JavaScript -->
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                        </div>
                        <div class="activity-feed" id="recentActivity">
                            <!-- Dynamic content loaded via JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Upcoming Deadlines</h3>
                    </div>
                    <div class="deadlines-list" id="upcomingDeadlines">
                        <!-- Dynamic content loaded via JavaScript -->
                    </div>
                </div>
            </section>

            <!-- Courses Section -->
            <section id="courses" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>My Courses</h1>
                    <p>Track your learning progress and continue your courses</p>
                </div>

                <div class="courses-filter">
                    <button class="filter-btn active" data-filter="all">All Courses</button>
                    <button class="filter-btn" data-filter="active">In Progress</button>
                    <button class="filter-btn" data-filter="completed">Completed</button>
                </div>

                <div class="courses-grid" id="coursesGrid">
                    <!-- Dynamic content loaded via JavaScript -->
                </div>
            </section>

            <!-- Certificates Section -->
            <section id="certificates" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>My Certificates</h1>
                    <p>Your earned certificates and professional credentials</p>
                </div>

                <div class="certifications-grid" id="certificatesGrid">
                    <!-- Dynamic content loaded via JavaScript -->
                </div>
            </section>

            <!-- Profile Section -->
            <section id="profile" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Profile</h1>
                    <p>Manage your personal information and preferences</p>
                </div>

                <div class="profile-container">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <img src="<?= $user['profile_image'] ?: 'https://via.placeholder.com/120x120/64748b/ffffff?text=' . strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>" alt="Profile Picture">
                                <button class="avatar-edit">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <div class="profile-info">
                                <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                                <p><?= htmlspecialchars($user['position'] ?: 'Quality Professional') ?></p>
                                <span class="member-since">Member since <?= date('F Y', strtotime($user['created_at'])) ?></span>
                            </div>
                        </div>

                        <form class="profile-form" id="profileForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?: '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="company">Company</label>
                                <input type="text" id="company" name="company" value="<?= htmlspecialchars($user['company'] ?: '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" id="position" name="position" value="<?= htmlspecialchars($user['position'] ?: '') ?>" readonly>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-primary" onclick="toggleEdit()">Edit Profile</button>
                                <button type="button" class="btn btn-secondary" id="saveBtn" style="display: none;" onclick="saveProfile()">Save Changes</button>
                                <button type="button" class="btn btn-outline" id="cancelBtn" style="display: none;" onclick="cancelEdit()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        // Load dashboard overview data
        async function loadDashboardData() {
            try {
                const response = await fetch('/api/dashboard.php?action=overview');
                const result = await response.json();
                
                if (result.success) {
                    updateActiveCourses(result.data.active_courses);
                    updateRecentActivity(result.data.recent_activity);
                    updateUpcomingDeadlines(result.data.upcoming_deadlines);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        function updateActiveCourses(courses) {
            const container = document.getElementById('activeCourses');
            if (!courses || courses.length === 0) {
                container.innerHTML = '<p>No active courses found.</p>';
                return;
            }

            container.innerHTML = courses.map(course => `
                <div class="course-item">
                    <div class="course-info">
                        <h4>${course.title}</h4>
                        <p>${course.current_module || 'Getting Started'}</p>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${course.progress_percentage}%"></div>
                    </div>
                    <span class="progress-text">${course.progress_percentage}%</span>
                </div>
            `).join('');
        }

        function updateRecentActivity(activities) {
            const container = document.getElementById('recentActivity');
            if (!activities || activities.length === 0) {
                container.innerHTML = '<p>No recent activity.</p>';
                return;
            }

            container.innerHTML = activities.map(activity => `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>${activity.action}</strong> ${activity.description}</p>
                        <span class="activity-time">${formatDate(activity.created_at)}</span>
                    </div>
                </div>
            `).join('');
        }

        function updateUpcomingDeadlines(deadlines) {
            const container = document.getElementById('upcomingDeadlines');
            if (!deadlines || deadlines.length === 0) {
                container.innerHTML = '<p>No upcoming deadlines.</p>';
                return;
            }

            container.innerHTML = deadlines.map(deadline => `
                <div class="deadline-item">
                    <div class="deadline-info">
                        <h4>${deadline.title}</h4>
                        <p>${deadline.course}</p>
                    </div>
                    <div class="deadline-date">
                        <span class="date">${formatDate(deadline.due_date)}</span>
                        <span class="days-left">${deadline.days_left} days left</span>
                    </div>
                </div>
            `).join('');
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>