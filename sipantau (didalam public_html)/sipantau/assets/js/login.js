// ===== DOM CONTENT LOADED =====
document.addEventListener('DOMContentLoaded', function() {
    initializeLoginPage();
    initializeAnimations();
});

// ===== INITIALIZE LOGIN PAGE =====
function initializeLoginPage() {
    setupPasswordToggle();
    setupFlashMessages();
    setupFormSubmission();
    setupInputAnimations();
}

// ===== PASSWORD TOGGLE =====
function setupPasswordToggle() {
    const toggleBtn = document.querySelector('[onclick="togglePassword()"]');
    if (toggleBtn) {
        toggleBtn.removeAttribute('onclick');
        toggleBtn.addEventListener('click', togglePassword);
    }
}

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (!passwordInput || !eyeIcon) return;
    
    const isPassword = passwordInput.type === 'password';
    
    // Toggle input type
    passwordInput.type = isPassword ? 'text' : 'password';
    
    // Update icon with animation
    eyeIcon.style.transform = 'scale(0.8)';
    setTimeout(() => {
        eyeIcon.style.transform = 'scale(1)';
    }, 150);
    
    eyeIcon.innerHTML = isPassword ? 
        // Eye slash icon (password visible)
        `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>` :
        // Eye icon (password hidden)
        `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
    
    // Add visual feedback
    passwordInput.focus();
}

// ===== FLASH MESSAGES =====
function setupFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    if (flashMessages.length > 0) {
        // Auto-hide after 5 seconds
        setTimeout(() => {
            flashMessages.forEach(msg => {
                hideFlashMessage(msg);
            });
        }, 5000);
        
        // Add close button to each message
        flashMessages.forEach(msg => {
            addCloseButton(msg);
        });
    }
}

function hideFlashMessage(messageElement) {
    messageElement.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        messageElement.remove();
    }, 500);
}

function addCloseButton(messageElement) {
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.className = 'absolute top-2 right-2 text-lg font-bold opacity-50 hover:opacity-100 transition-opacity';
    closeBtn.onclick = () => hideFlashMessage(messageElement);
    
    messageElement.style.position = 'relative';
    messageElement.style.paddingRight = '2rem';
    messageElement.appendChild(closeBtn);
}

// ===== FORM SUBMISSION =====
function setupFormSubmission() {
    const form = document.querySelector('form');
    const submitBtn = form?.querySelector('button[type="submit"]');
    
    if (!form || !submitBtn) return;
    
    form.addEventListener('submit', function(e) {
        // Show loading state
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging in...';
        submitBtn.disabled = true;
        
        // Allow form to submit normally
        // Loading state will be cleared on page reload
    });
}

// ===== INPUT ANIMATIONS =====
function setupInputAnimations() {
    const inputs = document.querySelectorAll('.input-field');
    
    inputs.forEach(input => {
        // Add focus animation
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease-out';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Add typing effect
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.classList.add('border-blue-500');
            } else {
                this.classList.remove('border-blue-500');
            }
        });
    });
}

// ===== PAGE ANIMATIONS =====
function initializeAnimations() {
    // Animate decorative boxes
    animateDecorativeBoxes();
    
    // Animate main sections
    animateSections();
    
    // Add parallax effect to decorative boxes
    addParallaxEffect();
}

function animateDecorativeBoxes() {
    const decorativeBoxes = document.querySelectorAll('.decorative-box');
    
    decorativeBoxes.forEach((box, index) => {
        // Set different animation delays for each box
        box.style.animationDelay = `${index * 0.3}s`;
        
        // Add floating animation
        box.classList.add('floating');
        
        // Add random rotation
        const randomRotation = Math.random() * 10 - 5;
        box.style.transform = `rotate(${randomRotation}deg)`;
    });
}

function animateSections() {
    // Animate login form
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.classList.add('slide-up');
    }
    
    // Animate brand section
    const brandSection = document.querySelector('.brand-section');
    if (brandSection) {
        brandSection.classList.add('scale-in');
    }
}

function addParallaxEffect() {
    const decorativeBoxes = document.querySelectorAll('.decorative-box');
    
    if (decorativeBoxes.length === 0) return;
    
    document.addEventListener('mousemove', function(e) {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        decorativeBoxes.forEach((box, index) => {
            const speed = (index + 1) * 10;
            const x = (mouseX - 0.5) * speed;
            const y = (mouseY - 0.5) * speed;
            
            box.style.transform = `translate(${x}px, ${y}px)`;
        });
    });
}

// ===== UTILITY FUNCTIONS =====
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full';
    
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-black'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ===== EXPORT FUNCTIONS =====
window.LoginPage = {
    togglePassword,
    showNotification,
    hideFlashMessage
};