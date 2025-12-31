/**
 * Happy New Year 2025 Theme JavaScript
 * Adds celebratory decorations and effects
 */

(function () {
    'use strict';

    const CONFIG = {
        confettiCount: 100,
        enableConfetti: true,
        enableFireworks: true,
        fireworkInterval: 200,
    };

    document.addEventListener('DOMContentLoaded', function () {
        initNewYearTheme();
    });

    function initNewYearTheme() {
        if (CONFIG.enableConfetti) createConfetti();
        if (CONFIG.enableFireworks) startFireworks();

        enhanceExistingElements();
    }

    /**
     * Create confetti effect
     */
    function createConfetti() {
        const container = document.createElement('div');
        container.className = 'confetti-container';
        container.setAttribute('aria-hidden', 'true');

        for (let i = 0; i < CONFIG.confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';

            const left = Math.random() * 100;
            const animationDuration = 4 + Math.random() * 6;
            const animationDelay = Math.random() * 8;
            const size = 5 + Math.random() * 10;
            const shapes = ['50%', '0', '50% 0 50% 50%'];
            const shape = shapes[Math.floor(Math.random() * shapes.length)];

            confetti.style.left = left + '%';
            confetti.style.animationDuration = animationDuration + 's';
            confetti.style.animationDelay = animationDelay + 's';
            confetti.style.width = size + 'px';
            confetti.style.height = size + 'px';
            confetti.style.borderRadius = shape;

            container.appendChild(confetti);
        }

        document.body.appendChild(container);
    }

    /**
     * Start firework effects periodically
     */
    function startFireworks() {
        const container = document.createElement('div');
        container.className = 'fireworks-container';
        container.setAttribute('aria-hidden', 'true');
        document.body.appendChild(container);

        // Initial fireworks burst
        for (let i = 0; i < 3; i++) {
            setTimeout(() => createFirework(container), i * 500);
        }

        // Periodic fireworks
        setInterval(() => {
            if (Math.random() > 0.5) {
                createFirework(container);
            }
        }, CONFIG.fireworkInterval);
    }

    function createFirework(container) {
        const firework = document.createElement('div');
        firework.className = 'firework';

        const colors = ['#ffd700', '#8b5cf6', '#ec4899', '#3b82f6', '#10b981'];
        const color = colors[Math.floor(Math.random() * colors.length)];
        const left = 10 + Math.random() * 80;

        firework.style.left = left + '%';
        firework.style.background = color;
        firework.style.boxShadow = `0 0 6px ${color}, 0 0 12px ${color}`;

        container.appendChild(firework);

        // Create explosion after rise
        setTimeout(() => {
            createExplosion(container, left, color);
            firework.remove();
        }, 1400);
    }

    function createExplosion(container, x, color) {
        const particles = 12;
        const explosionY = 15 + Math.random() * 10;

        for (let i = 0; i < particles; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                left: ${x}%;
                top: ${explosionY}%;
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background: ${color};
                box-shadow: 0 0 4px ${color};
                pointer-events: none;
            `;

            const angle = (i / particles) * 360;
            const distance = 30 + Math.random() * 40;
            const duration = 0.8 + Math.random() * 0.4;

            particle.animate([
                {
                    transform: 'translate(0, 0) scale(1)',
                    opacity: 1
                },
                {
                    transform: `translate(${Math.cos(angle * Math.PI / 180) * distance}px, ${Math.sin(angle * Math.PI / 180) * distance}px) scale(0)`,
                    opacity: 0
                }
            ], {
                duration: duration * 1000,
                easing: 'ease-out',
                fill: 'forwards'
            });

            container.appendChild(particle);
            setTimeout(() => particle.remove(), duration * 1000);
        }
    }

    /**
     * Enhance existing elements with New Year classes
     */
    function enhanceExistingElements() {
        const header = document.querySelector('.sticky-header');
        if (header) header.classList.add('newyear-header');

        const footer = document.querySelector('footer');
        if (footer) footer.classList.add('newyear-footer');

        const progressBars = document.querySelectorAll('.progress-bar-dynamic');
        progressBars.forEach(bar => bar.classList.add('newyear-progress'));

        const glassElements = document.querySelectorAll('.glass-effect');
        glassElements.forEach(el => el.classList.add('newyear'));
    }

    /**
     * Toggle theme (can be called from console)
     */
    window.toggleNewYearTheme = function (enabled) {
        const elements = document.querySelectorAll(
            '.fireworks-container, .confetti-container, .newyear-banner, .newyear-decoration, .newyear-badge'
        );

        elements.forEach(el => {
            el.style.display = enabled ? '' : 'none';
        });
    };

})();
