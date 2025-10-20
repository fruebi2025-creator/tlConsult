<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Validator.php';

// Redirect if already logged in
$auth = new Auth();
if ($auth->isLoggedIn()) {
    header('Location: /dashboard.php');
    exit();
}

$csrf_token = Validator::generateCSRF();
$error_message = '';
$success_message = '';

// Check for registration success
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success_message = 'Registration successful! Please log in with your credentials.';
}

// Check for logout success
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success_message = 'You have been logged out successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TLC Consult</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>TLC Consult</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.html" class="nav-link">Home</a>
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
                <li class="nav-item">
                    <a href="login.php" class="nav-link login-btn active">Login</a>
                </li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Auth Container -->
    <section class="auth-container">
        <div class="container">
            <div class="auth-wrapper">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to access your account and continue your learning journey</p>
                    </div>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form class="auth-form" id="loginForm" action="/api/login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                            <div class="error-message" id="email-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="error-message" id="password-error"></div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" id="remember" name="remember_me">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="/forgot-password.php" class="forgot-password">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-spinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                        
                        <div class="auth-divider">
                            <span>or</span>
                        </div>
                        
                        <div class="social-login">
                            <button type="button" class="btn btn-social google">
                                <i class="fab fa-google"></i>
                                Sign in with Google
                            </button>
                            <button type="button" class="btn btn-social linkedin">
                                <i class="fab fa-linkedin-in"></i>
                                Sign in with LinkedIn
                            </button>
                        </div>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.html">Sign up here</a></p>
                    </div>
                </div>
                
                <div class="auth-info">
                    <div class="info-content">
                        <h2>Access Your Learning Dashboard</h2>
                        <p>Sign in to your TLC Consult account to:</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Access your enrolled courses</li>
                            <li><i class="fas fa-check"></i> Download certificates and materials</li>
                            <li><i class="fas fa-check"></i> Track your learning progress</li>
                            <li><i class="fas fa-check"></i> Connect with other professionals</li>
                            <li><i class="fas fa-check"></i> Access exclusive resources</li>
                        </ul>
                        <div class="stats">
                            <div class="stat">
                                <h3>10,000+</h3>
                                <p>Active Learners</p>
                            </div>
                            <div class="stat">
                                <h3>50+</h3>
                                <p>Courses Available</p>
                            </div>
                            <div class="stat">
                                <h3>95%</h3>
                                <p>Success Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>TLC Consult</h3>
                    <p>Excellence in Quality Control, Assurance & Training Services</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="services-qa.html">Quality Control</a></li>
                        <li><a href="services-qa.html">Quality Assurance</a></li>
                        <li><a href="services-training.html">Training Programs</a></li>
                        <li><a href="services-training.html">Certifications</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="industries.html">Industries</a></li>
                        <li><a href="gallery.html">Gallery</a></li>
                        <li><a href="resources.html">Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@tlc-consult.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> 123 Business Ave, Suite 100<br>City, State 12345</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 TLC Consult. All rights reserved.</p>
                    <div class="footer-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
