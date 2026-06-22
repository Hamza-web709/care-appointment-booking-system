/**
 * CARE – Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss flash alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 4000);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Add active class to current nav link
    const current = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') && current.includes(link.getAttribute('href').split('/').pop().split('.')[0])) {
            link.classList.add('active');
        }
    });

    // Animate elements on scroll
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));

    // Confirm delete dialogs
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Set min date for date inputs to today
    const dateInputs = document.querySelectorAll('input[type="date"].min-today');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => input.setAttribute('min', today));

    // Preview image before upload
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            if (preview && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => preview.src = e.target.result;
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});

// <!-- =====================================================
    //  HEADER SCRIPT — include before </body>
// ===================================================== -->
  /* ── Hamburger Toggle ── */
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');

  hamburger.addEventListener('click', () => {
    const isOpen = mobileNav.classList.toggle('open');
    hamburger.classList.toggle('active', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen);
    mobileNav.setAttribute('aria-hidden', !isOpen);
  });

  /* ── Profile Dropdown Toggle ── */
  const profileWrap = document.getElementById('profileWrap');
  const profileBtn  = document.getElementById('profileBtn');
  const dropdown    = document.getElementById('dropdown');

  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = profileWrap.classList.toggle('open');
    profileBtn.setAttribute('aria-expanded', isOpen);
    dropdown.setAttribute('aria-hidden', !isOpen);
  });

  /* Close dropdown when clicking outside */
  document.addEventListener('click', () => {
    profileWrap.classList.remove('open');
    profileBtn.setAttribute('aria-expanded', false);
    dropdown.setAttribute('aria-hidden', true);
  });

  /* Close dropdown on Escape key */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      profileWrap.classList.remove('open');
      profileBtn.setAttribute('aria-expanded', false);
      dropdown.setAttribute('aria-hidden', true);
      mobileNav.classList.remove('open');
      hamburger.classList.remove('active');
      hamburger.setAttribute('aria-expanded', false);
      mobileNav.setAttribute('aria-hidden', true);
    }
  });

  /* ── Active nav link highlight ── */
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav a, .mobile-nav a').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });

