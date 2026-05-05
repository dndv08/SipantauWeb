// ===== SIDEBAR FUNCTIONS =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar || !overlay) return;
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
    
    // Add animation
    if (!sidebar.classList.contains('-translate-x-full')) {
        sidebar.style.animation = 'slideInLeft 0.3s ease-out';
    }
}

function toggleSubmenu(menuName) {
    const submenu = document.getElementById(`${menuName}-submenu`);
    const icon = document.getElementById(`${menuName}-icon`);
    
    if (!submenu || !icon) return;
    
    // Close other submenus (optional - remove if you want multiple open)
    // closeAllSubmenusExcept(menuName);
    
    submenu.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
    
    // Add smooth height transition
    if (!submenu.classList.contains('hidden')) {
        submenu.style.animation = 'slideDown 0.3s ease-out';
    }
}

function closeAllSubmenusExcept(exceptMenu) {
    const allSubmenus = document.querySelectorAll('[id$="-submenu"]');
    const allIcons = document.querySelectorAll('[id$="-icon"]');
    
    allSubmenus.forEach(submenu => {
        if (!submenu.id.startsWith(exceptMenu)) {
            submenu.classList.add('hidden');
        }
    });
    
    allIcons.forEach(icon => {
        if (!icon.id.startsWith(exceptMenu)) {
            icon.classList.remove('rotate-180');
        }
    });
}

// ===== USER MENU =====
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (!menu) return;
    
    menu.classList.toggle('hidden');
    
    // Add animation
    if (!menu.classList.contains('hidden')) {
        menu.style.animation = 'slideDown 0.2s ease-out';
    }
}

// ===== CLOSE DROPDOWNS ON OUTSIDE CLICK =====
document.addEventListener('click', function(event) {
    // Close user menu
    const userMenu = document.getElementById('userMenu');
    const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
    
    if (userMenu && !userButton && !userMenu.contains(event.target)) {
        userMenu.classList.add('hidden');
    }
    
    // Close sidebar on mobile when clicking outside
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn = event.target.closest('button[onclick="toggleSidebar()"]');
    
    if (window.innerWidth < 1024 && sidebar && !sidebar.contains(event.target) && !mobileMenuBtn) {
        if (!sidebar.classList.contains('-translate-x-full')) {
            toggleSidebar();
        }
    }
});

// ===== FLASH MESSAGES =====
window.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(function(message) {
        // Add close button
        addCloseButton(message);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            hideMessage(message);
        }, 5000);
    });
});

function addCloseButton(messageElement) {
    if (messageElement.querySelector('.close-btn')) return;
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn ml-4 text-current opacity-50 hover:opacity-100 transition-opacity';
    closeBtn.innerHTML = '&times;';
    closeBtn.style.fontSize = '1.5rem';
    closeBtn.style.lineHeight = '1';
    closeBtn.onclick = () => hideMessage(messageElement);
    
    messageElement.appendChild(closeBtn);
}

function hideMessage(messageElement) {
    messageElement.style.transition = 'all 0.3s ease-out';
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'translateY(-10px)';
    
    setTimeout(function() {
        messageElement.remove();
    }, 300);
}

// ===== KEEP ACTIVE SUBMENU OPEN =====
document.addEventListener('DOMContentLoaded', function() {
    const activeLink = document.querySelector('.sidebar-link.active');
    
    if (activeLink) {
        const submenu = activeLink.closest('[id$="-submenu"]');
        if (submenu) {
            submenu.classList.remove('hidden');
            const menuName = submenu.id.replace('-submenu', '');
            const icon = document.getElementById(`${menuName}-icon`);
            if (icon) {
                icon.classList.add('rotate-180');
            }
        }
    }
});

// ===== SEARCH FUNCTIONALITY =====
const searchInput = document.querySelector('input[type="text"][placeholder="Search..."]');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        console.log('Searching for:', searchTerm);
        // Implement your search logic here
    }, 300));
}

// ===== UTILITY FUNCTIONS =====
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
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ===== RESPONSIVE HANDLING =====
window.addEventListener('resize', debounce(function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Auto-close sidebar on desktop
    if (window.innerWidth >= 1024) {
        if (overlay && !overlay.classList.contains('hidden')) {
            overlay.classList.add('hidden');
        }
    }
}, 250));

// ===== EXPORT FUNCTIONS =====
window.AdminDashboard = {
    toggleSidebar,
    toggleSubmenu,
    toggleUserMenu,
    showNotification,
    hideMessage
};