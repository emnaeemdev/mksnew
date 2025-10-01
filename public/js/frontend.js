// Frontend JavaScript Functions

// Language switcher
function switchLanguage(lang) {
    const currentUrl = window.location.href;
    const url = new URL(currentUrl);
    
    // Get current path without language prefix
    let currentPath = url.pathname;
    
    // Remove existing language prefix if present
    currentPath = currentPath.replace(/^\/(ar|en)/, '');
    
    // If path is empty, set to root
    if (!currentPath || currentPath === '/') {
        currentPath = '';
    }
    
    // Create new URL with language prefix
    const newPath = '/' + lang + currentPath;
    
    // Redirect to new URL
    window.location.href = url.origin + newPath + url.search + url.hash;
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// Search functionality
function initializeSearch() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            if (!searchInput.value.trim()) {
                e.preventDefault();
                alert('يرجى إدخال كلمة البحث');
            }
        });
    }
}

// Navbar scroll effect
function initializeNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Back to top button
function initializeBackToTop() {
    // Create back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'back-to-top';
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        display: none;
        z-index: 1000;
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });
    
    // Scroll to top when clicked
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Loading animation
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loading';
    loader.innerHTML = '<div class="spinner"></div>';
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const spinner = loader.querySelector('.spinner');
    spinner.style.cssText = `
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    `;
    
    // Add spinner animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('loading');
    if (loader) {
        loader.remove();
    }
}

// Form validation
function validateContactForm() {
    const form = document.getElementById('contact-form');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const name = form.querySelector('[name="name"]');
        const email = form.querySelector('[name="email"]');
        const message = form.querySelector('[name="message"]');
        
        let isValid = true;
        
        // Validate name
        if (!name.value.trim()) {
            showFieldError(name, 'يرجى إدخال الاسم');
            isValid = false;
        } else {
            clearFieldError(name);
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim() || !emailRegex.test(email.value)) {
            showFieldError(email, 'يرجى إدخال بريد إلكتروني صحيح');
            isValid = false;
        } else {
            clearFieldError(email);
        }
        
        // Validate message
        if (!message.value.trim()) {
            showFieldError(message, 'يرجى إدخال الرسالة');
            isValid = false;
        } else {
            clearFieldError(message);
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Share functionality
function sharePost(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('تم نسخ الرابط');
        });
    }
}

// Print functionality
function printPost() {
    window.print();
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize search
    initializeSearch();
    
    // Initialize navbar scroll effect
    initializeNavbarScroll();
    
    // Initialize back to top button
    initializeBackToTop();
    
    // Initialize contact form validation
    validateContactForm();
    
    // Add language switcher event listeners
    document.querySelectorAll('.lang-switch').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            switchLanguage(lang);
        });
    });
    
    // Add share button event listeners
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-url') || window.location.href;
            const title = this.getAttribute('data-title') || document.title;
            sharePost(url, title);
        });
    });
    
    // Add print button event listeners
    document.querySelectorAll('.print-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            printPost();
        });
    });

    // Initialize Bootstrap dropdowns
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
});