// Interactive Effects for Survey System
// Advanced Zoom Transition Effect
// Created: 2026-01-05

(function () {
    'use strict';

    // Create transition overlay
    function createTransitionOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay';
        overlay.id = 'page-transition-overlay';
        document.body.appendChild(overlay);
        return overlay;
    }

    // Get overlay or create if doesn't exist
    function getOverlay() {
        let overlay = document.getElementById('page-transition-overlay');
        if (!overlay) {
            overlay = createTransitionOverlay();
        }
        return overlay;
    }

    // Ripple Effect
    function createRipple(event, element) {
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');

        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';

        element.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    }

    // Survey cards now use global fade transition (removed special effects)

    // Smooth Scroll
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    // Global Link Transition Handler
    function initGlobalLinkTransitions() {
        const overlay = getOverlay();

        // Get all links that navigate to different pages (including survey cards now)
        const links = document.querySelectorAll('a[href]:not([href^="#"]):not([target="_blank"])');

        links.forEach(link => {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');

                // Skip if no href or javascript: links
                if (!href || href.startsWith('javascript:') || href === '#') {
                    return;
                }

                // Skip if it's the current page
                if (href === window.location.href || href === window.location.pathname) {
                    return;
                }

                e.preventDefault();

                // Fast fade out
                document.body.style.transition = 'opacity 0.3s ease-out';
                document.body.style.opacity = '0';
                overlay.style.transition = 'opacity 0.3s ease-out';
                overlay.classList.add('active');

                // Navigate after fade
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            });
        });
    }


    // Entrance Animations
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    target.style.opacity = '1';
                    target.style.transform = 'translateY(0)';
                    observer.unobserve(target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-survey-card, .reveal-banner-text, .reveal-banner-image').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }

    // Respect reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Initialize
    function init() {
        if (!prefersReducedMotion) {
            initScrollAnimations();
            initGlobalLinkTransitions(); // Global fade transitions
        }
        initSmoothScroll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
