// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }));
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
        navbar.style.backdropFilter = 'blur(10px)';
    } else {
        navbar.style.backgroundColor = '#ffffff';
        navbar.style.backdropFilter = 'none';
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Newsletter form submission
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Simple email validation
        if (isValidEmail(email)) {
            // Here you would typically send the data to your server
            showNotification('Thank you for subscribing! We\'ll keep you updated.', 'success');
            this.reset();
        } else {
            showNotification('Please enter a valid email address.', 'error');
        }
    });
}

// Email validation function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);

    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    });
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.service-card, .feature-item, .testimonial-card, .industry-item');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
`;
document.head.appendChild(style);

// Form validation helper
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }

        // Email validation
        if (input.type === 'email' && input.value.trim()) {
            if (!isValidEmail(input.value)) {
                input.classList.add('error');
                isValid = false;
            }
        }
    });

    return isValid;
}

// Add error styles
const errorStyle = document.createElement('style');
errorStyle.textContent = `
    .error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(errorStyle);

// Gallery functionality
function openModal(button) {
    const galleryCard = button.closest('.gallery-card');
    const img = galleryCard.querySelector('img');
    const title = galleryCard.querySelector('h3').textContent;
    const description = galleryCard.querySelector('p').textContent;
    
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    
    if (modal && modalImg && modalTitle && modalDescription) {
        modalImg.src = img.src;
        modalImg.alt = img.alt;
        modalTitle.textContent = title;
        modalDescription.textContent = description;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Gallery filtering
document.addEventListener('DOMContentLoaded', function() {
    const galleryFilters = document.querySelectorAll('.filter-btn[data-filter]');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Update active filter
            galleryFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-link[data-section]');
    const dashboardSections = document.querySelectorAll('.dashboard-section');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            
            // Update active sidebar link
            sidebarLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Show target section
            dashboardSections.forEach(section => {
                if (section.id === targetSection) {
                    section.classList.add('active');
                } else {
                    section.classList.remove('active');
                }
            });
        });
    });
});

// Profile editing functionality
function toggleEdit() {
    const inputs = document.querySelectorAll('#profile input[readonly]');
    const editBtn = document.querySelector('button[onclick="toggleEdit()"]');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    inputs.forEach(input => {
        input.removeAttribute('readonly');
        input.style.backgroundColor = '#f8fafc';
    });
    
    if (editBtn) editBtn.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'inline-block';
    if (cancelBtn) cancelBtn.style.display = 'inline-block';
}

function saveProfile() {
    const inputs = document.querySelectorAll('#profile input');
    const editBtn = document.querySelector('button[onclick="toggleEdit()"]');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', true);
        input.style.backgroundColor = '';
    });
    
    if (editBtn) editBtn.style.display = 'inline-block';
    if (saveBtn) saveBtn.style.display = 'none';
    if (cancelBtn) cancelBtn.style.display = 'none';
    
    showNotification('Profile updated successfully!', 'success');
}

function cancelEdit() {
    const inputs = document.querySelectorAll('#profile input');
    const editBtn = document.querySelector('button[onclick="toggleEdit()"]');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', true);
        input.style.backgroundColor = '';
    });
    
    if (editBtn) editBtn.style.display = 'inline-block';
    if (saveBtn) saveBtn.style.display = 'none';
    if (cancelBtn) cancelBtn.style.display = 'none';
}

// Course filtering
document.addEventListener('DOMContentLoaded', function() {
    const courseFilters = document.querySelectorAll('.courses-filter .filter-btn');
    const courseCards = document.querySelectorAll('.course-card');
    
    courseFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Update active filter
            courseFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter course cards
            courseCards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-status') === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});

// Resource filtering
document.addEventListener('DOMContentLoaded', function() {
    const resourceFilters = document.querySelectorAll('.resource-filters .filter-btn');
    const resourceCards = document.querySelectorAll('.resource-card');
    
    resourceFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Update active filter
            resourceFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter resource cards
            resourceCards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});

// Search functionality for resources
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const resourceCards = document.querySelectorAll('.resource-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            resourceCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
                
                const matches = title.includes(searchTerm) || 
                              description.includes(searchTerm) || 
                              tags.some(tag => tag.includes(searchTerm));
                
                card.style.display = matches ? 'block' : 'none';
            });
        });
    }
});

// Logout functionality
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Here you would typically make an API call to logout
        showNotification('Logged out successfully!', 'success');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 1000);
    }
}

// FAQ functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', function() {
                // Close other FAQ items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        }
    });
});

// Enhanced Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin(this);
        });
    }
});

async function handleLogin(form) {
    const submitBtn = form.querySelector('#loginBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    if (btnText) btnText.style.display = 'none';
    if (btnSpinner) btnSpinner.style.display = 'inline-flex';
    
    // Clear previous errors
    clearFormErrors(form);
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/api/login.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            showNotification(result.message || 'Login successful! Redirecting...', 'success');
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = '/dashboard.php';
            }, 1000);
        } else {
            // Show error message
            showNotification(result.message || 'Login failed. Please try again.', 'error');
            
            // Show field-specific errors if available
            if (result.errors) {
                showFormErrors(form, result.errors);
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
        if (btnText) btnText.style.display = 'inline';
        if (btnSpinner) btnSpinner.style.display = 'none';
    }
}

// Form error handling
function showFormErrors(form, errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const errorContainer = form.querySelector(`#${fieldName}-error`);
        
        if (field && errorContainer) {
            field.closest('.form-group').classList.add('has-error');
            errorContainer.textContent = Array.isArray(errors[fieldName]) ? errors[fieldName][0] : errors[fieldName];
            errorContainer.classList.add('show');
        }
    });
}

function clearFormErrors(form) {
    // Remove error classes
    form.querySelectorAll('.form-group').forEach(group => {
        group.classList.remove('has-error');
    });
    
    // Hide error messages
    form.querySelectorAll('.error-message').forEach(error => {
        error.classList.remove('show');
        error.textContent = '';
    });
}

// Contact Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleContactForm(this);
        });
    }
});

async function handleContactForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    // Clear previous errors
    clearFormErrors(form);
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/api/contact.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Message sent successfully!', 'success');
            form.reset();
        } else {
            showNotification(result.message || 'Failed to send message. Please try again.', 'error');
            
            if (result.errors) {
                showFormErrors(form, result.errors);
            }
        }
    } catch (error) {
        console.error('Contact form error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Password Toggle
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.parentNode.querySelector('.password-toggle i');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Profile Update Functions
function toggleEdit() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[readonly]');
    const editBtn = form.querySelector('button[onclick="toggleEdit()"]');
    const saveBtn = form.querySelector('#saveBtn');
    const cancelBtn = form.querySelector('#cancelBtn');
    
    inputs.forEach(input => {
        if (input.name !== 'email') { // Keep email readonly
            input.removeAttribute('readonly');
            input.style.backgroundColor = '#f8fafc';
        }
    });
    
    if (editBtn) editBtn.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'inline-block';
    if (cancelBtn) cancelBtn.style.display = 'inline-block';
}

async function saveProfile() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/api/dashboard.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'update_profile', ...data })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Profile updated successfully!', 'success');
            cancelEdit();
            
            // Update displayed values
            location.reload();
        } else {
            showNotification(result.message || 'Failed to update profile.', 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showNotification('An error occurred while updating profile.', 'error');
    }
}

function cancelEdit() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input');
    const editBtn = form.querySelector('button[onclick="toggleEdit()"]');
    const saveBtn = form.querySelector('#saveBtn');
    const cancelBtn = form.querySelector('#cancelBtn');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', true);
        input.style.backgroundColor = '';
    });
    
    if (editBtn) editBtn.style.display = 'inline-block';
    if (saveBtn) saveBtn.style.display = 'none';
    if (cancelBtn) cancelBtn.style.display = 'none';
    
    // Reset form to original values
    location.reload();
}

// Lazy loading for images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', lazyLoadImages);

// Counter animation for statistics
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            counter.textContent = Math.floor(current);
            
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            }
        }, 16);
    });
}

// Initialize counter animation when counters are visible
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            counterObserver.unobserve(entry.target);
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        counterObserver.observe(statsSection);
    }
});

// Search functionality (for future implementation)
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');
    
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', debounce((e) => {
            const query = e.target.value.trim();
            if (query.length > 2) {
                performSearch(query);
            } else {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
            }
        }, 300));
    }
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search function (placeholder)
function performSearch(query) {
    // This would typically make an API call to your backend
    console.log('Searching for:', query);
}

// Initialize search
document.addEventListener('DOMContentLoaded', initSearch);

// Cookie consent (basic implementation)
function initCookieConsent() {
    if (!localStorage.getItem('cookieConsent')) {
        const cookieBanner = document.createElement('div');
        cookieBanner.className = 'cookie-banner';
        cookieBanner.innerHTML = `
            <div class="cookie-content">
                <p>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
                <button class="btn btn-primary" onclick="acceptCookies()">Accept</button>
            </div>
        `;
        
        cookieBanner.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: white;
            padding: 1rem;
            z-index: 10000;
            box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        document.body.appendChild(cookieBanner);
    }
}

function acceptCookies() {
    localStorage.setItem('cookieConsent', 'true');
    const banner = document.querySelector('.cookie-banner');
    if (banner) {
        banner.remove();
    }
}

// Initialize cookie consent
document.addEventListener('DOMContentLoaded', initCookieConsent);

// Print functionality
function printPage() {
    window.print();
}

// Back to top button
function createBackToTopButton() {
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
    backToTop.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    `;
    
    document.body.appendChild(backToTop);
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.style.opacity = '1';
            backToTop.style.visibility = 'visible';
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.visibility = 'hidden';
        }
    });
    
    // Scroll to top when clicked
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Initialize back to top button
document.addEventListener('DOMContentLoaded', createBackToTopButton);

// Theme toggle (for future dark mode implementation)
function initThemeToggle() {
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            localStorage.setItem('darkTheme', document.body.classList.contains('dark-theme'));
        });
        
        // Load saved theme
        if (localStorage.getItem('darkTheme') === 'true') {
            document.body.classList.add('dark-theme');
        }
    }
}

// Initialize theme toggle
document.addEventListener('DOMContentLoaded', initThemeToggle);

// FAQ Functionality
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active', !isActive);
        });
    });
}

// Initialize FAQ functionality
document.addEventListener('DOMContentLoaded', initFAQ);

// Contact form functionality
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Sending...';
                submitBtn.disabled = true;
                
                // Simulate form submission (replace with actual form handling)
                setTimeout(() => {
                    showNotification('Thank you for your message! We\'ll get back to you within 24 hours.', 'success');
                    this.reset();
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });
    }
}

// Initialize contact form
document.addEventListener('DOMContentLoaded', initContactForm);

// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.parentElement.querySelector('.password-toggle i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let score = 0;
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    if (!strengthFill || !strengthText) return;
    
    // Length check
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Character variety checks
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    // Remove all classes first
    strengthFill.classList.remove('weak', 'fair', 'good', 'strong');
    
    if (score <= 2) {
        strengthFill.classList.add('weak');
        strengthText.textContent = 'Weak';
    } else if (score <= 3) {
        strengthFill.classList.add('fair');
        strengthText.textContent = 'Fair';
    } else if (score <= 4) {
        strengthFill.classList.add('good');
        strengthText.textContent = 'Good';
    } else {
        strengthFill.classList.add('strong');
        strengthText.textContent = 'Strong';
    }
}

// Login form functionality
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Signing In...';
                submitBtn.disabled = true;
                
                // Simulate login process
                setTimeout(() => {
                    showNotification('Login successful! Redirecting to dashboard...', 'success');
                    
                    // Redirect to dashboard (replace with actual dashboard URL)
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 1500);
                }, 2000);
            }
        });
    }
}

// Registration form functionality
function initRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        // Password strength checker
        const passwordInput = registerForm.querySelector('#password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }
        
        // Password confirmation checker
        const confirmPasswordInput = registerForm.querySelector('#confirmPassword');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    this.setCustomValidity('Passwords do not match');
                    this.classList.add('error');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('error');
                }
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check password confirmation
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                showNotification('Passwords do not match. Please check and try again.', 'error');
                return;
            }
            
            if (validateForm(this)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Creating Account...';
                submitBtn.disabled = true;
                
                // Simulate registration process
                setTimeout(() => {
                    showNotification('Account created successfully! Please check your email to verify your account.', 'success');
                    
                    // Redirect to login page
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                }, 2000);
            }
        });
    }
}

// Initialize authentication forms
document.addEventListener('DOMContentLoaded', function() {
    initLoginForm();
    initRegisterForm();
});

// Social login handlers
document.addEventListener('DOMContentLoaded', function() {
    const googleBtns = document.querySelectorAll('.btn-social.google');
    const linkedinBtns = document.querySelectorAll('.btn-social.linkedin');
    
    googleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            showNotification('Google login integration would be implemented here.', 'info');
        });
    });
    
    linkedinBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            showNotification('LinkedIn login integration would be implemented here.', 'info');
        });
    });
});
