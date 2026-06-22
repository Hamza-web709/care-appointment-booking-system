/**
 * CARE Medical System - Header Interactions
 * Vanilla JavaScript Implementation
 */

document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const mobileToggle = document.getElementById('care-mobile-toggle');
    const mobileMenu = document.getElementById('care-mobile-menu');
    const profileBtn = document.getElementById('care-profile-btn');
    const profileDropdown = document.getElementById('care-profile-dropdown');
    const navLinks = document.querySelectorAll('.care-nav-link, .care-mobile-link');

    // --- Active Link Highlighting ---
    const currentPath = window.location.pathname;
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        // Simple match or base index match
        if (href && (currentPath.endsWith(href) || (href === 'index.php' && currentPath.endsWith('/')))) {
            link.classList.add('active');
        }
    });

    // --- Mobile Menu Toggle ---
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = mobileMenu.classList.contains('show');

            // Close profile dropdown if open
            if (profileDropdown) profileDropdown.classList.remove('show');

            mobileMenu.classList.toggle('show');
            mobileToggle.setAttribute('aria-expanded', !isOpen);

            // Toggle icon
            const icon = mobileToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-list');
                icon.classList.toggle('bi-x-lg');
            }
        });
    }

    // --- Profile Dropdown Toggle ---
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.contains('show');

            // Close mobile menu if open
            if (mobileMenu) {
                mobileMenu.classList.remove('show');
                const toggleIcon = mobileToggle.querySelector('i');
                if (toggleIcon) {
                    toggleIcon.classList.add('bi-list');
                    toggleIcon.classList.remove('bi-x-lg');
                }
            }

            profileDropdown.classList.toggle('show');
            profileBtn.setAttribute('aria-expanded', !isOpen);
        });
    }

    // --- Close Menus on Outside Click ---
    document.addEventListener('click', (e) => {
        if (mobileMenu && !mobileMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
            mobileMenu.classList.remove('show');
            if (mobileToggle) {
                mobileToggle.setAttribute('aria-expanded', 'false');
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.add('bi-list');
                    icon.classList.remove('bi-x-lg');
                }
            }
        }

        if (profileDropdown && !profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
            profileDropdown.classList.remove('show');
            if (profileBtn) profileBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // --- Accessibility: Escape Key ---
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (mobileMenu) mobileMenu.classList.remove('show');
            if (profileDropdown) profileDropdown.classList.remove('show');

            if (mobileToggle) {
                mobileToggle.setAttribute('aria-expanded', 'false');
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.add('bi-list');
                    icon.classList.remove('bi-x-lg');
                }
            }
            if (profileBtn) profileBtn.setAttribute('aria-expanded', 'false');
        }
    });
});
