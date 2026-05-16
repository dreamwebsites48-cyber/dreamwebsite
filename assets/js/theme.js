/**
 * DreamWebsites — Premium UI/UX Scripts v2.0
 * Handles: theme toggle, sidebar (mobile), animations, tooltips, toasts
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Theme Toggle ──────────────────────────────────────── */
    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme') || 'dark';

    body.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const newTheme = body.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        const icons = document.querySelectorAll('#theme-toggle i');
        icons.forEach(icon => {
            if (theme === 'light') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }

    /* ── Mobile Sidebar ────────────────────────────────────── */
    const sidebar      = document.querySelector('.sidebar-premium');
    const toggleBtn    = document.querySelector('.sidebar-toggle-btn');
    let overlay        = document.querySelector('.sidebar-overlay');

    // Create overlay dynamically if sidebar exists but overlay doesn't
    if (sidebar && !overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on link click (mobile UX)
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    }

    // Re-open sidebar on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) closeSidebar();
    });

    /* ── Bootstrap Tooltips ────────────────────────────────── */
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (typeof bootstrap !== 'undefined' && tooltipEls.length) {
        [...tooltipEls].forEach(el => new bootstrap.Tooltip(el));
    }

    /* ── Intersection Observer Animations ──────────────────── */
    const animEls = document.querySelectorAll('.animate-fade-up');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animEls.forEach((el, i) => {
            // Don't override explicitly set delays
            if (!el.style.animationDelay) {
                el.style.animationDelay = `${i * 0.07}s`;
            }
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    }

});

/* ── Toast Notification System ─────────────────────────── */
window.showToast = function(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `glass-panel animate-fade-up`;
    Object.assign(toast.style, {
        padding: '14px 18px',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        minWidth: '240px',
        borderLeft: `4px solid ${getColorForType(type)}`,
        borderRadius: '12px',
    });

    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const iconClass = icons[type] || 'fa-info-circle';

    toast.innerHTML = `
        <i class="fas ${iconClass}" style="color:${getColorForType(type)};font-size:1.15rem;flex-shrink:0;"></i>
        <div style="flex-grow:1;font-weight:500;font-size:.9rem;">${message}</div>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;padding:0;line-height:1;">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            Object.assign(toast.style, { opacity: '0', transform: 'translateX(20px)', transition: 'all 0.3s ease' });
            setTimeout(() => toast.remove(), 300);
        }
    }, 4500);
};

function getColorForType(type) {
    const map = { success: 'var(--accent-tertiary)', error: 'var(--accent-danger)', warning: 'var(--accent-warning)' };
    return map[type] || 'var(--accent-primary)';
}
