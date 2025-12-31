/**
 * Christmas Theme JavaScript
 * Adds festive decorations and effects to the survey website
 */

(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        snowflakeCount: 10,
        lightBulbCount: 15,
        enableSnow: true,
        enableLights: true,
        enableSantaHat: true
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        initChristmasTheme();
    });

    function initChristmasTheme() {
        if (CONFIG.enableSnow) createSnowfall();
        if (CONFIG.enableLights) createChristmasLights();
        if (CONFIG.enableSantaHat) addSantaHat();

        // Add Christmas classes to existing elements
        enhanceExistingElements();
    }

    /**
     * Create snowfall effect
     */
    function createSnowfall() {
        const container = document.createElement('div');
        container.className = 'snowflakes-container';
        container.setAttribute('aria-hidden', 'true');

        for (let i = 0; i < CONFIG.snowflakeCount; i++) {
            const snowflake = document.createElement('div');
            snowflake.className = 'snowflake';

            // Random properties
            const left = Math.random() * 100;
            const animationDuration = 5 + Math.random() * 10; // 5-15 seconds
            const animationDelay = Math.random() * 10;
            const fontSize = 0.5 + Math.random() * 1.2; // 0.5-1.7rem
            const opacity = 0.4 + Math.random() * 0.6;

            snowflake.style.left = left + '%';
            snowflake.style.animationDuration = animationDuration + 's';
            snowflake.style.animationDelay = animationDelay + 's';
            snowflake.style.fontSize = fontSize + 'rem';
            snowflake.style.opacity = opacity;

            container.appendChild(snowflake);
        }

        document.body.appendChild(container);
    }

    /**
     * Create Christmas lights at the top of the page
     */
    function createChristmasLights() {
        const lightsContainer = document.createElement('div');
        lightsContainer.className = 'christmas-lights';
        lightsContainer.setAttribute('aria-hidden', 'true');

        for (let i = 0; i < CONFIG.lightBulbCount; i++) {
            const bulb = document.createElement('div');
            bulb.className = 'light-bulb';
            lightsContainer.appendChild(bulb);
        }

        document.body.appendChild(lightsContainer);
    }

    /**
     * Add Santa hat to logo
     */
    function addSantaHat() {
        const logoContainers = document.querySelectorAll('.h-10.w-10, .h-12.w-12, .h-14.w-14');

        logoContainers.forEach(container => {
            if (container.querySelector('img[alt*="Logo"]')) {
                container.style.position = 'relative';
                container.style.overflow = 'visible';

                const hat = document.createElement('span');
                hat.className = 'santa-hat';
                hat.textContent = '🎅';
                hat.setAttribute('aria-hidden', 'true');

                container.appendChild(hat);
            }
        });
    }

    /**
     * Enhance existing elements with Christmas classes
     */
    function enhanceExistingElements() {
        // Header
        const header = document.querySelector('.sticky-header');
        if (header) {
            header.classList.add('christmas-header');
        }

        // Survey cards
        const surveyCards = document.querySelectorAll('.survey-card-link');
        surveyCards.forEach(card => {
            card.classList.add('christmas-card');
        });

        // Footer
        const footer = document.querySelector('footer');
        if (footer) {
            footer.classList.add('christmas-footer');
        }

        // Progress bars
        const progressBars = document.querySelectorAll('.progress-bar-dynamic');
        progressBars.forEach(bar => {
            bar.classList.add('christmas-progress');
        });

        // Glass effect elements
        const glassElements = document.querySelectorAll('.glass-effect');
        glassElements.forEach(el => {
            el.classList.add('christmas');
        });
    }

    /**
     * Toggle Christmas theme (can be called from console or UI)
     */
    window.toggleChristmasTheme = function (enabled) {
        const elements = document.querySelectorAll(
            '.snowflakes-container, .christmas-lights, .christmas-corner-decoration, .christmas-greeting'
        );

        elements.forEach(el => {
            el.style.display = enabled ? '' : 'none';
        });

        // Toggle classes
        const classElements = {
            '.sticky-header': 'christmas-header',
            '.survey-card-link': 'christmas-card',
            'footer': 'christmas-footer',
            '.progress-bar-dynamic': 'christmas-progress',
            '.glass-effect': 'christmas'
        };

        Object.entries(classElements).forEach(([selector, className]) => {
            document.querySelectorAll(selector).forEach(el => {
                if (enabled) {
                    el.classList.add(className);
                } else {
                    el.classList.remove(className);
                }
            });
        });
    };

    // Add some sparkle effect on mouse move (subtle)
    let throttleTimer;
    document.addEventListener('mousemove', function (e) {
        if (throttleTimer) return;

        throttleTimer = setTimeout(() => {
            throttleTimer = null;
        }, 100);

        // Occasionally create a sparkle
        if (Math.random() > 0.95) {
            createSparkle(e.clientX, e.clientY);
        }
    });

    function createSparkle(x, y) {
        const sparkle = document.createElement('div');
        sparkle.textContent = '✨';
        sparkle.style.cssText = `
            position: fixed;
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
            font-size: 1.2rem;
            z-index: 9999;
            animation: sparkle-fade 0.8s ease-out forwards;
        `;

        document.body.appendChild(sparkle);

        setTimeout(() => sparkle.remove(), 800);
    }

    // Add sparkle animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes sparkle-fade {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.5) translateY(-20px);
            }
        }
    `;
    document.head.appendChild(style);

})();
