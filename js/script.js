// ===================================
// Navigation Toggle
// ===================================
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('nav-menu');
const navLinks = document.querySelectorAll('.nav-link');

hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
});

// Close menu when clicking on nav links
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// ===================================
// Navbar Scroll Effect
// ===================================
const navbar = document.getElementById('navbar');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }

    lastScroll = currentScroll;
});

// ===================================
// Scroll Animations
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observe all animated elements
const animatedElements = document.querySelectorAll('.fade-in, .fade-up, .fade-in-left, .fade-in-right');
animatedElements.forEach(el => observer.observe(el));

// ===================================
// CAPTCHA System
// ===================================
let captchaAnswer;

function generateCaptcha() {
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const operators = ['+', '-', '*'];
    const operator = operators[Math.floor(Math.random() * operators.length)];

    let question = `Berapa hasil dari ${num1} ${operator} ${num2}?`;

    switch(operator) {
        case '+':
            captchaAnswer = num1 + num2;
            break;
        case '-':
            captchaAnswer = num1 - num2;
            break;
        case '*':
            captchaAnswer = num1 * num2;
            break;
    }

    document.getElementById('captcha-question').textContent = question;
}

// Generate captcha on page load
generateCaptcha();

// ===================================
// Contact Form Handler
// ===================================
const contactForm = document.getElementById('contactForm');
const formMessage = document.getElementById('form-message');

contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Get form data
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        message: document.getElementById('message').value,
        captcha: document.getElementById('captcha-answer').value
    };

    // Validate CAPTCHA
    if (parseInt(formData.captcha) !== captchaAnswer) {
        showMessage('Jawaban CAPTCHA salah. Silakan coba lagi.', 'error');
        generateCaptcha();
        document.getElementById('captcha-answer').value = '';
        return;
    }

    // Show loading state
    const submitBtn = contactForm.querySelector('.btn-submit');
    submitBtn.classList.add('loading');

    // Simulate sending email (in production, this should be connected to a backend)
    try {
        // Create mailto link with form data
        const subject = encodeURIComponent('Inquiry dari Website - ' + formData.name);
        const body = encodeURIComponent(
            `Nama: ${formData.name}\n` +
            `Email: ${formData.email}\n` +
            `Telepon: ${formData.phone}\n\n` +
            `Pesan:\n${formData.message}`
        );

        // For demonstration, we'll show success message
        // In production, you would send this to a server
        setTimeout(() => {
            submitBtn.classList.remove('loading');
            showMessage('Terima kasih! Pesan Anda telah diterima. Kami akan segera menghubungi Anda.', 'success');
            contactForm.reset();
            generateCaptcha();

            // Optional: Open email client
            // window.location.href = `mailto:info@infobit.co.id?subject=${subject}&body=${body}`;
        }, 1500);

    } catch (error) {
        submitBtn.classList.remove('loading');
        showMessage('Terjadi kesalahan. Silakan coba lagi atau hubungi kami langsung di info@infobit.co.id', 'error');
    }
});

function showMessage(message, type) {
    formMessage.textContent = message;
    formMessage.className = `form-message ${type}`;

    // Hide message after 5 seconds
    setTimeout(() => {
        formMessage.style.display = 'none';
        setTimeout(() => {
            formMessage.className = 'form-message';
        }, 300);
    }, 5000);
}

// ===================================
// Smooth Scroll for Navigation Links
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offset = 80; // Height of navbar
            const targetPosition = target.offsetTop - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ===================================
// Parallax Effect for Hero Section
// ===================================
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        const heroContent = document.querySelector('.hero-content');
        heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
        heroContent.style.opacity = 1 - (scrolled / 500);
    }
});

// ===================================
// Counter Animation for About Section
// ===================================
function animateCounter(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Observe about section for counter animation
const aboutSection = document.querySelector('.about');
if (aboutSection) {
    const aboutObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                entry.target.classList.add('counted');
                // You can add counter animations here if needed
            }
        });
    }, { threshold: 0.3 });

    aboutObserver.observe(aboutSection);
}

// ===================================
// Add hover effects to partner cards
// ===================================
const partnerCards = document.querySelectorAll('.partner-card');
partnerCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// ===================================
// Loading Animation
// ===================================
window.addEventListener('load', () => {
    // Add entrance animations
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.classList.add('visible');
    }

    // Trigger initial animations
    setTimeout(() => {
        const firstSection = document.querySelectorAll('.fade-in, .fade-up, .fade-in-left, .fade-in-right');
        firstSection.forEach((el, index) => {
            setTimeout(() => {
                if (el.getBoundingClientRect().top < window.innerHeight) {
                    el.classList.add('visible');
                }
            }, index * 100);
        });
    }, 300);
});

// ===================================
// Form Validation Enhancement
// ===================================
const formInputs = document.querySelectorAll('.contact-form input, .contact-form textarea');
formInputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            this.classList.add('filled');
        } else {
            this.classList.remove('filled');
        }
    });
});

// Email validation
const emailInput = document.getElementById('email');
if (emailInput) {
    emailInput.addEventListener('input', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailPattern.test(this.value)) {
            this.setCustomValidity('Mohon masukkan email yang valid');
        } else {
            this.setCustomValidity('');
        }
    });
}

// Phone validation
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
    });
}

// ===================================
// Console Welcome Message
// ===================================
console.log('%c PT INFOBIT CIPTA MANDIRI ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 20px; padding: 10px; border-radius: 5px;');
console.log('%c Your Trusted IT Solutions Partner since 2012 ', 'color: #667eea; font-size: 14px;');
