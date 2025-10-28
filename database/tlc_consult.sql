-- =====================================================
-- TLC Consult Database Schema
-- Complete database structure with sample data
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS tlc_consult;
CREATE DATABASE tlc_consult CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tlc_consult;

-- =====================================================
-- Users and Authentication Tables
-- =====================================================

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    position VARCHAR(100),
    profile_image VARCHAR(255),
    role ENUM('user', 'admin', 'instructor') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64),
    reset_token VARCHAR(64),
    reset_expires DATETIME,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- User sessions table
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);

-- =====================================================
-- Content Management Tables
-- =====================================================

-- Industries table
CREATE TABLE industries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) NOT NULL,
    description TEXT,
    services TEXT,
    certifications TEXT,
    image VARCHAR(255),
    meta_title VARCHAR(150),
    meta_description VARCHAR(300),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
);

-- Resources/Blog categories
CREATE TABLE resource_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#2563eb',
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);

-- Resources/Blog posts
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    type ENUM('article', 'whitepaper', 'guide', 'template', 'webinar') NOT NULL,
    category_id INT,
    featured_image VARCHAR(255),
    file_attachment VARCHAR(255),
    file_size INT,
    download_count INT DEFAULT 0,
    read_time INT DEFAULT 0,
    author_id INT,
    tags TEXT,
    meta_title VARCHAR(150),
    meta_description VARCHAR(300),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured TINYINT(1) DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES resource_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_published (published_at),
    FULLTEXT idx_search (title, excerpt, content)
);

-- Gallery categories
CREATE TABLE gallery_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);

-- Gallery items
CREATE TABLE gallery_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    category_id INT,
    alt_text VARCHAR(200),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
);

-- =====================================================
-- Course and Training Management
-- =====================================================

-- Course categories
CREATE TABLE course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#2563eb',
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);

-- Courses
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    objectives TEXT,
    prerequisites TEXT,
    category_id INT,
    instructor_id INT,
    featured_image VARCHAR(255),
    duration_hours INT DEFAULT 0,
    module_count INT DEFAULT 0,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured TINYINT(1) DEFAULT 0,
    max_students INT DEFAULT 0,
    certificate_template VARCHAR(255),
    passing_score INT DEFAULT 70,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES course_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured)
);

-- Course modules
CREATE TABLE course_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    content LONGTEXT,
    video_url VARCHAR(500),
    duration_minutes INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_sort (sort_order),
    INDEX idx_status (status)
);

-- User course enrollments
CREATE TABLE course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('active', 'completed', 'dropped', 'suspended') DEFAULT 'active',
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    completion_date DATETIME NULL,
    certificate_issued TINYINT(1) DEFAULT 0,
    certificate_url VARCHAR(255),
    final_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    INDEX idx_user (user_id),
    INDEX idx_course (course_id),
    INDEX idx_status (status)
);

-- User module progress
CREATE TABLE user_module_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent_minutes INT DEFAULT 0,
    started_at DATETIME,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, module_id),
    INDEX idx_user (user_id),
    INDEX idx_module (module_id)
);

-- User certificates
CREATE TABLE user_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    issued_date DATE NOT NULL,
    expiry_date DATE,
    certificate_url VARCHAR(255),
    verification_code VARCHAR(32) UNIQUE NOT NULL,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_course (course_id),
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_verification_code (verification_code)
);

-- =====================================================
-- Communication and Marketing
-- =====================================================

-- Contact form submissions
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    type ENUM('general', 'quote', 'support', 'training') DEFAULT 'general',
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    ip_address VARCHAR(45),
    user_agent TEXT,
    replied_at DATETIME,
    replied_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
);

-- Newsletter subscribers
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    status ENUM('active', 'unsubscribed', 'bounced') DEFAULT 'active',
    subscription_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscription_date DATETIME NULL,
    verification_token VARCHAR(64),
    verified TINYINT(1) DEFAULT 0,
    source VARCHAR(50) DEFAULT 'website',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_verified (verified)
);

-- =====================================================
-- System and Configuration Tables
-- =====================================================

-- Site settings
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'html') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Activity logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Insert Sample Data
-- =====================================================

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, password_hash, role, status, email_verified) VALUES
('Admin', 'User', 'admin@tlc-consult.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1),
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', 1),
('Jane', 'Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', 1);

-- Insert industries
INSERT INTO industries (name, slug, icon, description, services, certifications) VALUES
('Manufacturing', 'manufacturing', 'fas fa-industry', 'Comprehensive quality management solutions for manufacturing operations, from lean production to Six Sigma implementation.', 'Production Quality Control,Process Optimization,ISO 9001 Implementation,Supplier Quality Management,Statistical Process Control,Lean Manufacturing Training', 'ISO 9001,ISO 14001,OHSAS 18001'),
('Healthcare', 'healthcare', 'fas fa-hospital', 'Specialized quality assurance for healthcare organizations, ensuring patient safety and regulatory compliance.', 'Medical Device Quality Systems,Clinical Quality Assurance,Regulatory Compliance,Risk Management,Accreditation Support,Healthcare Staff Training', 'ISO 13485,Joint Commission,CLIA'),
('Food & Beverage', 'food-beverage', 'fas fa-utensils', 'Food safety and quality management systems to ensure consumer protection and regulatory compliance.', 'Food Safety Management Systems,HACCP Implementation,Supplier Audits,Traceability Systems,Regulatory Compliance,Food Safety Training', 'ISO 22000,BRC,HACCP'),
('Automotive', 'automotive', 'fas fa-cogs', 'Automotive quality standards and continuous improvement methodologies for the automotive supply chain.', 'Automotive Quality Systems,APQP Implementation,PPAP Preparation,Supplier Development,Problem Solving (8D),Automotive Core Tools Training', 'IATF 16949,ISO 9001,VDA'),
('Aerospace', 'aerospace', 'fas fa-plane', 'High-precision quality management for aerospace and defense industries with stringent safety requirements.', 'Aerospace Quality Systems,Configuration Management,First Article Inspection,Supplier Qualification,Reliability Engineering,AS9100 Training', 'AS9100,AS9110,AS9120'),
('Pharmaceuticals', 'pharmaceuticals', 'fas fa-pills', 'Pharmaceutical quality systems and GMP compliance to ensure product safety and regulatory approval.', 'GMP Implementation,Validation Services,Quality Risk Management,Regulatory Compliance,Change Control Systems,Pharmaceutical Quality Training', 'GMP,ICH Q10,21 CFR Part 11');

-- Insert gallery categories
INSERT INTO gallery_categories (name, slug, description) VALUES
('Events', 'events', 'Company events, conferences, and networking gatherings'),
('Certifications', 'certifications', 'Professional certifications and achievement ceremonies'),
('Company', 'company', 'Office spaces, team photos, and corporate culture'),
('Training', 'training', 'Training sessions, workshops, and educational programs');

-- Insert gallery items
INSERT INTO gallery_items (title, description, image_url, category_id, alt_text) VALUES
('Quality Summit 2024', 'Annual quality management conference', '/uploads/gallery/quality-summit-2024.jpg', 1, 'Quality Summit 2024 conference'),
('ISO Certification Ceremony', 'Client certification celebration', '/uploads/gallery/iso-ceremony.jpg', 1, 'ISO Certification Ceremony'),
('ISO 9001:2015 Certificate', 'Quality Management Systems certification', '/uploads/gallery/iso-9001-cert.jpg', 2, 'ISO 9001:2015 Certificate'),
('ISO 14001:2015 Certificate', 'Environmental Management Systems certification', '/uploads/gallery/iso-14001-cert.jpg', 2, 'ISO 14001:2015 Certificate'),
('TLC Consult Headquarters', 'Our modern office facility', '/uploads/gallery/office-headquarters.jpg', 3, 'TLC Consult Office'),
('Team Collaboration', 'Our expert consultants at work', '/uploads/gallery/team-meeting.jpg', 3, 'Team Meeting'),
('Quality Management Workshop', 'Interactive training session', '/uploads/gallery/qm-workshop.jpg', 4, 'Quality Management Workshop'),
('Online Learning Platform', 'Digital training resources', '/uploads/gallery/online-learning.jpg', 4, 'Online Learning Platform');

-- Insert resource categories
INSERT INTO resource_categories (name, slug, description, color, icon) VALUES
('Quality Management Systems', 'qms', 'ISO standards, implementation guides, and best practices', '#2563eb', 'fas fa-cog'),
('Auditing & Compliance', 'auditing', 'Internal audits, compliance checklists, and audit management', '#16a34a', 'fas fa-search'),
('Process Improvement', 'process-improvement', 'Lean, Six Sigma, and continuous improvement methodologies', '#d97706', 'fas fa-chart-line'),
('Training & Development', 'training', 'Training materials, competency frameworks, and skill development', '#9333ea', 'fas fa-graduation-cap');

-- Insert sample resources
INSERT INTO resources (title, slug, excerpt, content, type, category_id, author_id, tags, status, featured, published_at) VALUES
('Complete ISO 9001:2015 Implementation Guide', 'iso-9001-implementation-guide', 'Step-by-step guide to implementing ISO 9001:2015 quality management systems in your organization.', 'Comprehensive guide content here...', 'guide', 1, 1, 'ISO 9001,Implementation,Quality Management', 'published', 1, NOW()),
('5 Key Benefits of Digital Quality Management Systems', 'digital-qms-benefits', 'Explore how digital transformation is revolutionizing quality management and the key benefits organizations are experiencing.', 'Article content here...', 'article', 1, 1, 'Digital Transformation,QMS,Technology', 'published', 0, NOW()),
('Risk-Based Thinking in ISO 9001:2015', 'risk-based-thinking-iso-9001', 'Comprehensive analysis of implementing risk-based thinking in quality management systems for better organizational resilience.', 'Whitepaper content here...', 'whitepaper', 1, 1, 'Risk Management,ISO 9001,Quality Systems', 'published', 0, NOW()),
('Internal Audit Checklist Template', 'internal-audit-checklist', 'Comprehensive checklist template for conducting effective internal quality audits.', 'Template description and usage instructions...', 'template', 2, 1, 'Auditing,Templates,Quality Control', 'published', 1, NOW());

-- Insert course categories
INSERT INTO course_categories (name, slug, description, icon, color) VALUES
('Quality Management', 'quality-management', 'ISO standards and quality system courses', 'fas fa-certificate', '#2563eb'),
('Auditing', 'auditing', 'Internal and external auditing certification programs', 'fas fa-search', '#16a34a'),
('Process Improvement', 'process-improvement', 'Lean, Six Sigma, and continuous improvement courses', 'fas fa-chart-line', '#d97706'),
('Industry Specific', 'industry-specific', 'Specialized training for specific industries', 'fas fa-industry', '#dc2626');

-- Insert sample courses
INSERT INTO courses (title, slug, description, category_id, instructor_id, duration_hours, module_count, level, status, featured) VALUES
('ISO 9001:2015 Quality Management Systems', 'iso-9001-qms', 'Learn the fundamentals of quality management systems and ISO 9001 requirements.', 1, 3, 20, 8, 'beginner', 'published', 1),
('Internal Auditing Fundamentals', 'internal-auditing-fundamentals', 'Master the skills needed to conduct effective internal audits in your organization.', 2, 3, 15, 6, 'intermediate', 'published', 1),
('Quality Auditor Certification', 'quality-auditor-certification', 'Comprehensive training for aspiring quality auditors and professionals.', 2, 3, 25, 10, 'advanced', 'published', 0);

-- Insert course modules
INSERT INTO course_modules (course_id, title, description, duration_minutes, sort_order) VALUES
(1, 'Introduction to Quality Management', 'Overview of quality management principles and ISO 9001 history', 60, 1),
(1, 'Quality Management Principles', 'The seven quality management principles of ISO 9001:2015', 90, 2),
(1, 'Process Approach', 'Understanding and implementing the process approach', 75, 3),
(2, 'Audit Fundamentals', 'Basic principles of internal auditing', 45, 1),
(2, 'Audit Planning', 'How to plan and prepare for internal audits', 60, 2),
(2, 'Audit Execution', 'Conducting effective audit interviews and assessments', 90, 3);

-- Insert sample enrollments
INSERT INTO course_enrollments (user_id, course_id, status, progress_percentage, start_date) VALUES
(2, 1, 'active', 65.00, NOW() - INTERVAL 10 DAY),
(2, 2, 'active', 40.00, NOW() - INTERVAL 5 DAY),
(2, 3, 'completed', 100.00, NOW() - INTERVAL 30 DAY);

-- Insert sample certificates
INSERT INTO user_certificates (user_id, course_id, certificate_number, issued_date, verification_code, status) VALUES
(2, 3, 'TLC-QA-2024-001', CURDATE(), MD5(CONCAT('cert', 2, 3, UNIX_TIMESTAMP())), 'active');

-- Insert site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'TLC Consult', 'text', 'Website name'),
('site_description', 'Excellence in Quality Control, Assurance & Training', 'text', 'Website description'),
('contact_email', 'contactus@tlconsultingltd.com', 'text', 'Main contact email'),
('contact_phone', '+234 809 062 2735', 'text', 'Main contact phone'),
('office_address', '39A Eric Moore Street, Wemabod Estate, Ajao Road, Off Adeniyi Jones Avenue, Ikeja, LAGOS.', 'text', 'Office address'),
('items_per_page', '12', 'number', 'Items to display per page'),
('maintenance_mode', '0', 'boolean', 'Site maintenance mode');

-- Insert newsletter subscribers
INSERT INTO newsletter_subscribers (email, name, status, verified) VALUES
('subscriber1@example.com', 'Newsletter Subscriber 1', 'active', 1),
('subscriber2@example.com', 'Newsletter Subscriber 2', 'active', 1);

-- Insert sample contact submissions
INSERT INTO contact_submissions (name, email, phone, company, subject, message, type, status) VALUES
('Test User', 'test@example.com', '+1-555-0123', 'Test Company', 'Inquiry about Quality Training', 'I am interested in your ISO 9001 training programs.', 'training', 'new'),
('Another User', 'user@example.com', '+1-555-0456', 'Another Company', 'General Inquiry', 'Please provide more information about your services.', 'general', 'read');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_resources_published ON resources(status, published_at);
CREATE INDEX idx_courses_status_featured ON courses(status, featured);
CREATE INDEX idx_enrollments_user_status ON course_enrollments(user_id, status);

-- =====================================================
-- Create Views for Common Queries
-- =====================================================

-- View for user dashboard statistics
CREATE VIEW user_dashboard_stats AS
SELECT 
    u.id as user_id,
    COUNT(DISTINCT ce.id) as total_courses,
    COUNT(DISTINCT CASE WHEN ce.status = 'active' THEN ce.id END) as active_courses,
    COUNT(DISTINCT CASE WHEN ce.status = 'completed' THEN ce.id END) as completed_courses,
    COUNT(DISTINCT uc.id) as total_certificates,
    COALESCE(SUM(ump.time_spent_minutes), 0) as total_time_minutes
FROM users u
LEFT JOIN course_enrollments ce ON u.id = ce.user_id
LEFT JOIN user_certificates uc ON u.id = uc.user_id AND uc.status = 'active'
LEFT JOIN user_module_progress ump ON u.id = ump.user_id
GROUP BY u.id;

-- View for course statistics
CREATE VIEW course_stats AS
SELECT 
    c.id as course_id,
    c.title,
    c.slug,
    COUNT(DISTINCT ce.id) as total_enrollments,
    COUNT(DISTINCT CASE WHEN ce.status = 'completed' THEN ce.id END) as completed_enrollments,
    AVG(ce.progress_percentage) as avg_progress,
    COUNT(DISTINCT uc.id) as certificates_issued
FROM courses c
LEFT JOIN course_enrollments ce ON c.id = ce.course_id
LEFT JOIN user_certificates uc ON c.id = uc.course_id AND uc.status = 'active'
GROUP BY c.id;

-- View for resource download statistics
CREATE VIEW resource_stats AS
SELECT 
    r.id as resource_id,
    r.title,
    r.type,
    r.download_count,
    r.status,
    r.featured,
    rc.name as category_name,
    CONCAT(u.first_name, ' ', u.last_name) as author_name
FROM resources r
LEFT JOIN resource_categories rc ON r.category_id = rc.id
LEFT JOIN users u ON r.author_id = u.id;

COMMIT;