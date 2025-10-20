# TLC Consult - Full Stack Web Application

A comprehensive quality management consulting and training platform built with PHP, MySQL, HTML, CSS, and JavaScript.

## 🚀 Features

### Frontend
- **Responsive Design**: Mobile-first approach with modern UI/UX
- **Interactive Components**: Dynamic galleries, dashboards, and forms
- **Modern Styling**: CSS Grid, Flexbox, and custom animations
- **User Authentication**: Login, registration, and session management
- **Dashboard**: Personal learning progress and activity tracking

### Backend
- **PHP 7.4+**: Object-oriented architecture with MVC patterns
- **MySQL Database**: Comprehensive schema with relationships
- **RESTful APIs**: JSON-based API endpoints for all operations
- **Security Features**: CSRF protection, input validation, SQL injection prevention
- **Session Management**: Secure user authentication and authorization

### Database Features
- **User Management**: Registration, authentication, profiles
- **Course System**: Enrollments, progress tracking, certificates
- **Content Management**: Resources, galleries, industry information
- **Communication**: Contact forms, newsletters, activity logs
- **Analytics**: Dashboard statistics and reporting views

## 📁 Project Structure

```
/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── Auth.php              # Authentication class
│   ├── Database.php          # Database connection class
│   └── Validator.php         # Input validation class
├── api/
│   ├── login.php             # Login endpoint
│   ├── register.php          # Registration endpoint
│   ├── dashboard.php         # Dashboard data endpoint
│   ├── contact.php           # Contact form handler
│   ├── newsletter.php        # Newsletter subscription
│   └── logout.php            # Logout handler
├── database/
│   └── tlc_consult.sql       # Complete database schema and sample data
├── uploads/                  # File upload directory
├── *.php                     # Frontend PHP pages
├── *.html                    # Static pages
├── styles.css                # Complete CSS styling
├── script.js                 # JavaScript functionality
└── README.md                 # This file
```

## 🛠 Installation & Setup

### Prerequisites
- **Web Server**: Apache/Nginx with PHP 7.4+ support
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, openssl

### Step 1: Clone/Download Files
```bash
# Place all files in your web server directory
# Example: /var/www/html/tlc-consult/ or C:\xampp\htdocs\tlc-consult\
```

### Step 2: Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE tlc_consult CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p tlc_consult < database/tlc_consult.sql
```

3. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tlc_consult');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 3: Configure Application
1. Update `config/database.php` with your settings:
```php
define('APP_URL', 'http://localhost/tlc-consult');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
```

2. Create uploads directory and set permissions:
```bash
mkdir uploads
chmod 755 uploads
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^.]+)$ $1.php [NC,L]

# Security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
```

#### Nginx
```nginx
location / {
    try_files $uri $uri.php $uri/ =404;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## 🔐 Default Login Credentials

After importing the database, you can login with:

**Admin Account:**
- Email: `admin@tlc-consult.com`
- Password: `password` (default hash in database)

**Test User:**
- Email: `john.doe@example.com` 
- Password: `password`

**Note**: Change these passwords immediately in production!

## 🎯 Key Pages & Functionality

### Public Pages
- **Home (`index.php`)**: Landing page with company overview
- **About (`about.html`)**: Company information and team
- **Services**: Quality assurance and training services
- **Industries (`industries.html`)**: Industry-specific solutions
- **Gallery (`gallery.html`)**: Events, certifications, company photos
- **Resources (`resources.html`)**: Articles, guides, templates, webinars
- **Contact (`contact.html`)**: Contact form and company information

### Authentication
- **Login (`login.php`)**: User authentication with session management
- **Register (`register.php`)**: New user registration
- **Dashboard (`dashboard.php`)**: Personal learning dashboard

### API Endpoints
- `POST /api/login.php` - User authentication
- `POST /api/register.php` - User registration
- `GET /api/dashboard.php?action=overview` - Dashboard data
- `POST /api/contact.php` - Contact form submission
- `POST /api/newsletter.php` - Newsletter subscription
- `GET /api/logout.php` - User logout

## 🔒 Security Features

### Input Validation
- CSRF token protection on all forms
- XSS prevention with input sanitization
- SQL injection prevention with prepared statements
- File upload validation and restrictions

### Authentication Security
- Password hashing with PHP password_hash()
- Session management with database storage
- Login attempt limiting and account lockout
- Remember me functionality with secure tokens

### General Security
- Secure headers configuration
- Input validation and sanitization
- Error logging and monitoring
- File upload restrictions

## 📊 Database Schema Overview

### Core Tables
- **users**: User accounts and profiles
- **user_sessions**: Active user sessions
- **courses**: Training courses and programs  
- **course_enrollments**: User course registrations
- **user_certificates**: Earned certifications
- **resources**: Articles, guides, templates
- **gallery_items**: Photo gallery content
- **contact_submissions**: Contact form entries
- **newsletter_subscribers**: Email subscribers

### Views
- **user_dashboard_stats**: User progress statistics
- **course_stats**: Course enrollment analytics
- **resource_stats**: Content download metrics

## 🎨 Design System

### Colors
- **Primary Blue**: #2563eb
- **Secondary Blue**: #1e40af  
- **Light Blue**: #dbeafe
- **Success Green**: #10b981
- **Warning Orange**: #f59e0b
- **Error Red**: #ef4444

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700

### Components
- Responsive navigation with mobile menu
- Card-based layouts with hover effects
- Modal dialogs for image galleries
- Progress bars and statistics displays
- Form validation with error states
- Toast notifications for user feedback

## 🔧 Development

### Adding New Features
1. **Database**: Add new tables/columns to `database/tlc_consult.sql`
2. **Backend**: Create API endpoints in `/api/` directory
3. **Frontend**: Add pages and update `styles.css` / `script.js`
4. **Validation**: Update `includes/Validator.php` for new rules

### Code Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ES6+ features with async/await
- **CSS**: BEM naming convention
- **Database**: Snake_case for tables/columns

## 📝 Sample Data

The database includes sample data for:
- 3 user accounts (admin, user, instructor)
- 6 industry categories with detailed information
- 4 resource categories with sample content
- 3 courses with modules and enrollment data
- Gallery items across 4 categories
- Contact form submissions and newsletter subscribers

## 🚀 Deployment

### Production Checklist
1. **Security**:
   - Change all default passwords
   - Set `DEBUG_MODE = false` in config
   - Configure SSL certificates
   - Set up proper file permissions

2. **Performance**:
   - Enable PHP OPcache
   - Configure database connection pooling
   - Set up CDN for static assets
   - Enable gzip compression

3. **Monitoring**:
   - Set up error logging
   - Configure backup procedures
   - Monitor database performance
   - Set up uptime monitoring

## 📄 License

This project is proprietary software developed for TLC Consult. All rights reserved.

## 🤝 Support

For technical support or questions about this implementation, please contact the development team.

---

**Built with ❤️ for TLC Consult - Excellence in Quality Control, Assurance & Training**