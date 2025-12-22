/**
 * ============================================
 * ADMIN DASHBOARD - Main JavaScript
 * ============================================
 */

// ============================================
// SIDEBAR TOGGLE FUNCTIONALITY
// ============================================

/**
 * Toggle sidebar visibility
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    if (!sidebar || !overlay || !mainContent) {
        console.error('Sidebar elements not found');
        return;
    }

    // Toggle classes
    sidebar.classList.toggle('show');
    sidebar.classList.toggle('hidden');

    // Only show overlay on mobile/tablet
    if (window.innerWidth < 992) {
        overlay.classList.toggle('show');
    }

    // On desktop, toggle main content margin
    if (window.innerWidth >= 992) {
        mainContent.classList.toggle('expanded');
    }

    // Save state to localStorage
    const isOpen = sidebar.classList.contains('show') || !sidebar.classList.contains('hidden');
    localStorage.setItem('sidebarState', isOpen ? 'open' : 'closed');
}

/**
 * Close sidebar (used for overlay click)
 */
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebar && overlay) {
        sidebar.classList.remove('show');
        sidebar.classList.add('hidden');
        overlay.classList.remove('show');

        localStorage.setItem('sidebarState', 'closed');
    }
}

// ============================================
// WINDOW RESIZE HANDLER
// ============================================

/**
 * Handle window resize events
 */
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    if (!sidebar || !overlay || !mainContent) return;

    // On desktop, ensure sidebar is visible
    if (window.innerWidth >= 992) {
        sidebar.classList.remove('hidden');
        overlay.classList.remove('show');

        // Restore expanded state from localStorage
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'closed') {
            mainContent.classList.add('expanded');
            sidebar.classList.add('hidden');
        }
    } else {
        // On mobile/tablet, hide sidebar by default
        sidebar.classList.remove('show');
        sidebar.classList.add('hidden');
        overlay.classList.remove('show');
        mainContent.classList.remove('expanded');
    }
}

// Debounce resize events
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(handleResize, 250);
});

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize the dashboard
 */
function initDashboard() {
    console.log('Dashboard initialized');

    // Restore sidebar state from localStorage
    const sidebarState = localStorage.getItem('sidebarState');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    if (window.innerWidth >= 992 && sidebarState === 'closed') {
        if (sidebar) sidebar.classList.add('hidden');
        if (mainContent) mainContent.classList.add('expanded');
    }

    // Initialize tooltips if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Auto-close dropdown menus on mobile when clicking a link
    if (window.innerWidth < 992) {
        const dropdownLinks = document.querySelectorAll('.dropdown-menu-custom .nav-link');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(closeSidebar, 200);
            });
        });
    }
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

/**
 * Handle keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // ESC key closes sidebar on mobile
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    }

    // Ctrl+B toggles sidebar
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
});

// ============================================
// SEARCH FUNCTIONALITY
// ============================================

/**
 * Initialize search functionality
 */
function initSearch() {
    const searchInput = document.querySelector('.header input[type="text"]');

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    console.log('Searching for:', query);
                    // Implement your search logic here
                    // window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }
}

// ============================================
// NOTIFICATION HANDLING
// ============================================

/**
 * Handle notification clicks
 */
function initNotifications() {
    const notificationBtn = document.querySelector('.notification-badge').parentElement;

    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Notifications clicked');
            // Implement notification panel logic here
        });
    }
}

// ============================================
// LIVEWIRE INTEGRATION
// ============================================

/**
 * Listen for Livewire events
 */
if (typeof Livewire !== 'undefined') {
    // Show toast notifications
    Livewire.on('notify', (data) => {
        console.log('Notification:', data.message);
        // You can implement a toast notification system here
        alert(data.message);
    });

    // Refresh page after certain actions
    Livewire.on('refresh', () => {
        window.location.reload();
    });
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Format currency
 */
function formatCurrency(amount, currency = '€') {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency === '€' ? 'EUR' : currency
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, format = 'short') {
    const d = new Date(date);
    const options = format === 'short'
        ? { day: '2-digit', month: '2-digit', year: 'numeric' }
        : { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };

    return d.toLocaleDateString('fr-FR', options);
}

/**
 * Confirm action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        console.log('Copied to clipboard:', text);
        // Show success message
    }, function(err) {
        console.error('Could not copy text:', err);
    });
}

// ============================================
// PAGE LOAD EVENT
// ============================================

/**
 * Execute when DOM is fully loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initDashboard();
    initSearch();
    initNotifications();

    console.log('All systems initialized ✓');
});

/**
 * POUR LES TOOLTIP
 */
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el))
})

/**
 * THOUSAND SEPARTOR
 */
function priceInput(livewire, field) {
    return {
        display: '',

        format() {
            // Remove everything except numbers and dot
            let raw = this.display.replace(/[^\d.]/g, '');

            // Prevent multiple dots
            raw = raw.split('.').slice(0, 2).join('.');

            // Update Livewire with clean value
            livewire.set(field, raw);

            // Format for display
            if (raw !== '') {
                const parts = raw.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                this.display = parts.join('.');
            } else {
                this.display = '';
            }
        }
    }
}

// ============================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================

window.toggleSidebar = toggleSidebar;
window.closeSidebar = closeSidebar;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.confirmAction = confirmAction;
window.copyToClipboard = copyToClipboard;
