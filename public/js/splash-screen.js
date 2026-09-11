/**
 * Splash Screen — SVG Stroke Drawing + FLIP Slide-Up
 *
 * Timeline:
 *   0.0s  Logo pop-in (CSS)
 *   0.1s  SVG wrapper slide-up (CSS)
 *   ~0.2s SVG text stroke drawing begins (JS, after fonts.ready)
 *   1.4s  Stroke complete → fill white (JS adds .filled)
 *   1.8s  minDisplayTime reached → FLIP dismiss begins
 */
(function () {
    'use strict';

    // 1. Skip nếu đã xem splash trong session này và không phải trang chủ
    if (sessionStorage.getItem('splash_shown') && !window.isHomepage) {
        document.documentElement.classList.add('no-splash');
        var splash = document.getElementById('splash-screen');
        var main = document.getElementById('main-content');
        if (splash && splash.parentNode) splash.remove();
        if (main) main.style.visibility = 'visible';
        return;
    }

    var splash = document.getElementById('splash-screen');

    if (!splash) return;

    var startTime = Date.now();
    var svgAnimationDone = false;
    var dismissed = false;

    // 2. SVG Stroke Drawing — Đồng bộ vẽ đơn sắc Logo & Chữ + Tô màu hiện rõ
    function initSvgDraw() {
        var svgText = document.getElementById('splash-svg-text');
        var svgTitle = document.getElementById('splash-title-svg');
        var logoSvg = document.getElementById('splash-logo-svg');
        var logoContainer = document.getElementById('splash-logo-container');

        if (!svgText || !svgTitle) return;

        function setup() {
            try {
                // --- 1. SETUP LOGO PATHS & RING (Nét vẽ đơn sắc ban đầu) ---
                var logoElements = logoSvg ? logoSvg.querySelectorAll('path, circle') : [];
                for (var i = 0; i < logoElements.length; i++) {
                    var elem = logoElements[i];
                    var isCircle = elem.tagName.toLowerCase() === 'circle';
                    var origFill = isCircle ? 'none' : (elem.getAttribute('fill') || '#ffffff');
                    elem.setAttribute('data-fill', origFill);

                    // Đặt transition = none trước để không bị nhấp nháy nét vẽ cũ
                    elem.style.transition = 'none';
                    elem.style.fill = 'transparent';
                    elem.style.stroke = 'rgba(255, 255, 255, 0.9)';
                    elem.style.strokeWidth = isCircle ? '2.5px' : '1.4px';
                    elem.style.strokeLinecap = 'round';
                    elem.style.strokeLinejoin = 'round';

                    var pLen = elem.getTotalLength ? elem.getTotalLength() : 200;
                    if (!pLen || isNaN(pLen)) pLen = 200;
                    elem.style.strokeDasharray = pLen;
                    elem.style.strokeDashoffset = pLen;
                }

                // --- 2. SETUP TEXT DRAWING ---
                if (!svgText.getBBox) return;

                var textLen = svgText.getComputedTextLength();
                if (!textLen || textLen <= 0) textLen = 600;

                var bbox = svgText.getBBox();
                if (bbox.width < 10) {
                    setTimeout(setup, 100);
                    return;
                }

                var padX = 2;
                var padY = 2;
                svgTitle.setAttribute('viewBox',
                    (bbox.x - padX) + ' ' + (bbox.y - padY) + ' ' +
                    (bbox.width + padX * 2) + ' ' + (bbox.height + padY * 2)
                );

                svgText.style.transition = 'none';
                svgText.style.fill = 'transparent';
                svgText.style.stroke = 'rgba(255, 255, 255, 0.9)';
                svgText.style.strokeWidth = '0.8px';
                svgText.style.strokeDasharray = textLen;
                svgText.style.strokeDashoffset = textLen;

                var splashBrandRow = document.querySelector('.splash-brand-row');

                // --- 3. TRIGGER SYNCHRONIZED STROKE DRAWING, SMOOTH FADE-IN & SCALE DOWN ---
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        // Vừa fade-in (opacity: 0 -> 1 trong 0.45s) vừa scale từ 1.08 -> 1.0 mượt mà
                        if (splashBrandRow) splashBrandRow.classList.add('drawing');

                        for (var j = 0; j < logoElements.length; j++) {
                            logoElements[j].style.transition = 'stroke-dashoffset 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94), fill 0.3s ease-out 0.05s, stroke 0.25s ease-out';
                            logoElements[j].style.strokeDashoffset = '0';
                        }
                        svgText.style.transition = 'stroke-dashoffset 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94), fill 0.3s ease-out, stroke 0.25s ease-out';
                        svgText.classList.add('drawing');
                        svgText.style.strokeDashoffset = '0';
                    });
                });

                // --- 4. PHASE 2: TÔ MÀU HIỆN RÕ (AT 0.7s - chuyển màu 0.3s) ---
                setTimeout(function () {
                    // Tô màu logo & chữ mượt trong 0.3s
                    if (logoContainer) logoContainer.classList.add('filled');
                    for (var k = 0; k < logoElements.length; k++) {
                        var isC = logoElements[k].tagName.toLowerCase() === 'circle';
                        logoElements[k].style.transition = 'fill 0.3s ease-out, stroke 0.25s ease-out';
                        if (isC) {
                            logoElements[k].style.fill = 'none';
                            logoElements[k].style.stroke = 'rgba(255, 255, 255, 0.95)';
                        } else {
                            var f = logoElements[k].getAttribute('data-fill');
                            logoElements[k].style.fill = f;
                            logoElements[k].style.stroke = 'transparent';
                        }
                    }

                    // Tô màu trắng sáng cho chữ (0.3s)
                    svgText.classList.remove('drawing');
                    svgText.classList.add('filled');
                }, 700);

                // --- 5. BẮT ĐẦU DISMISS BAY LÊN (AT 1.1s - ngay khi tô màu xong) ---
                setTimeout(function () {
                    svgAnimationDone = true;
                    checkDismiss();
                }, 1100);

            } catch (e) {
                // Fallback nếu browser không hỗ trợ SVG length measurement
                if (logoElements) {
                    for (var m = 0; m < logoElements.length; m++) {
                        var isC2 = logoElements[m].tagName.toLowerCase() === 'circle';
                        if (isC2) {
                            logoElements[m].style.fill = 'none';
                            logoElements[m].style.stroke = 'rgba(255, 255, 255, 0.95)';
                        } else {
                            logoElements[m].style.fill = logoElements[m].getAttribute('fill') || '#ffffff';
                            logoElements[m].style.stroke = 'transparent';
                        }
                    }
                }
                if (logoContainer) logoContainer.classList.add('filled');
                svgText.style.fill = '#ffffff';
                svgText.style.stroke = 'transparent';
                svgText.style.opacity = '1';
                svgAnimationDone = true;
                checkDismiss();
            }
        }

        // Bắt đầu setup mượt mà, không để màn hình đen chờ font quá lâu
        var setupDone = false;
        function runSetupOnce() {
            if (setupDone) return;
            setupDone = true;
            requestAnimationFrame(function () {
                requestAnimationFrame(setup);
            });
        }

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(runSetupOnce);
            setTimeout(runSetupOnce, 80);
        } else {
            setTimeout(runSetupOnce, 40);
        }
    }

    initSvgDraw();

    // 4. Freeze SVG text + logo trước khi FLIP đo vị trí
    function freezeAnimations() {
        var logoContainer = document.getElementById('splash-logo-container');
        if (logoContainer) {
            logoContainer.style.transition = 'none';
            logoContainer.classList.add('filled');
        }

        var logoSvg = document.getElementById('splash-logo-svg');
        if (logoSvg) {
            var elements = logoSvg.querySelectorAll('path, circle');
            for (var i = 0; i < elements.length; i++) {
                elements[i].style.transition = 'none';
                elements[i].style.strokeDashoffset = '0';
                var isC3 = elements[i].tagName.toLowerCase() === 'circle';
                if (isC3) {
                    elements[i].style.fill = 'none';
                    elements[i].style.stroke = 'rgba(255, 255, 255, 0.95)';
                } else {
                    var f = elements[i].getAttribute('data-fill') || elements[i].getAttribute('fill');
                    if (f) elements[i].style.fill = f;
                    elements[i].style.stroke = 'transparent';
                }
            }
        }

        var svgText = document.getElementById('splash-svg-text');
        if (svgText) {
            svgText.classList.remove('drawing', 'filled');
            svgText.classList.add('frozen');
        }

        var splashTitleLine = document.getElementById('splash-title-line');
        if (splashTitleLine) {
            splashTitleLine.style.animation = 'none';
            splashTitleLine.style.opacity = '1';
            splashTitleLine.style.transform = 'none';
        }

        var splashSubtitleLine = document.getElementById('splash-subtitle-line');
        if (splashSubtitleLine) {
            splashSubtitleLine.style.animation = 'none';
            splashSubtitleLine.style.opacity = '1';
            splashSubtitleLine.style.transform = 'none';
        }
    }

    // 5. Dismiss — FLIP animation
    function checkDismiss() {
        if (svgAnimationDone) {
            triggerDismiss();
        }
    }

    function triggerDismiss() {
        if (dismissed) return;
        dismissed = true;

        var main = document.getElementById('main-content');
        var splashLogoWrapper = document.querySelector('#splash-screen .splash-logo-wrapper');
        var splashTextGroup = document.querySelector('#splash-screen .splash-text-group');
        var splashSubtitle = document.querySelector('#splash-screen .splash-subtitle');
        var splashBrandRow = document.querySelector('#splash-screen .splash-brand-row');

        var headerLogoContainer = document.getElementById('header-logo-container');
        var headerTextContainer = document.getElementById('header-text-container');
        var orbs = document.querySelectorAll('#splash-screen .splash-orb');
        var progressTrack = document.getElementById('splash-progress-track');

        // Hiện main content ở chế độ trong suốt để đo vị trí Header chính xác
        if (main) {
            main.style.visibility = 'visible';
            main.style.opacity = '0';
        }
        if (headerLogoContainer) headerLogoContainer.style.opacity = '0';
        if (headerTextContainer) headerTextContainer.style.opacity = '0';

        // Freeze tất cả animation trước khi đo
        freezeAnimations();

        // Dùng requestAnimationFrame để đảm bảo layout đã tính toán xong
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                performFlip(
                    splashLogoWrapper, splashTextGroup, splashSubtitle, splashBrandRow,
                    headerLogoContainer, headerTextContainer,
                    orbs, progressTrack, main
                );
            });
        });
    }

    function performFlip(
        splashLogoWrapper, splashTextGroup, splashSubtitle, splashBrandRow,
        headerLogoContainer, headerTextContainer,
        orbs, progressTrack, main
    ) {
        var isHeaderTextVisible = headerTextContainer && (
            headerTextContainer.offsetWidth > 0 &&
            window.getComputedStyle(headerTextContainer).display !== 'none'
        );

        var duration = 0.35; // 350ms
        var easeTransform = 'transform ' + duration + 's cubic-bezier(0.16, 1, 0.3, 1)';

        if (isHeaderTextVisible && splashLogoWrapper && splashTextGroup && headerLogoContainer) {
            // --- TRƯỜNG HỢP 1: Cả Logo + Chữ trên Header ---
            var srcLogo = splashLogoWrapper.getBoundingClientRect();
            var dstLogo = headerLogoContainer.getBoundingClientRect();

            if (srcLogo.width <= 0 || dstLogo.width <= 0) {
                fallbackDismiss();
                return;
            }

            var sLogo = dstLogo.width / srcLogo.width;
            var dxLogo = dstLogo.left - srcLogo.left;
            var dyLogo = dstLogo.top - srcLogo.top;

            var splashTitleLine = document.getElementById('splash-title-line');
            var svgText = document.getElementById('splash-svg-text');
            var headerTitleLine = document.getElementById('header-title-line') ||
                headerTextContainer.querySelector('span') || headerTextContainer;

            var srcTextElem = (svgText && svgText.getBoundingClientRect && svgText.getBoundingClientRect().width > 0)
                ? svgText
                : (splashTitleLine || splashTextGroup);
            var srcTitleRect = srcTextElem.getBoundingClientRect();
            var srcGroupRect = splashTextGroup.getBoundingClientRect();
            var dstTitleRect = headerTitleLine.getBoundingClientRect();

            if (srcTitleRect.width <= 0 || dstTitleRect.width <= 0) {
                fallbackDismiss();
                return;
            }

            var sText = dstTitleRect.width / srcTitleRect.width;
            var offsetX = srcTitleRect.left - srcGroupRect.left;
            var srcCenterY = (srcTitleRect.top + (srcTitleRect.height / 2)) - srcGroupRect.top;
            var dstCenterY = dstTitleRect.top + (dstTitleRect.height / 2);

            var dxText = dstTitleRect.left - srcGroupRect.left - (offsetX * sText);
            var dyText = dstCenterY - srcGroupRect.top - (srcCenterY * sText);

            // Giữ logo và text rõ ràng 100% (opacity: 1), chỉ trượt vị trí
            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = easeTransform;
            splashLogoWrapper.style.opacity = '1';

            splashTextGroup.style.transformOrigin = 'top left';
            splashTextGroup.style.transition = easeTransform;
            splashTextGroup.style.opacity = '1';

            // Header thật ẩn tạm thời cho tới khi trượt tới nơi
            headerLogoContainer.style.transition = 'none';
            headerLogoContainer.style.opacity = '0';
            headerTextContainer.style.transition = 'none';
            headerTextContainer.style.opacity = '0';

            // Làm mờ nền phông splash và subtitle
            if (splash) {
                splash.classList.add('fade-bg');
            }
            if (progressTrack) progressTrack.classList.add('dismissing-text');
            if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');

            // Kích hoạt hiển thị main content
            if (main) {
                main.style.visibility = 'visible';
                main.style.opacity = '1';
            }

            // Kích hoạt trượt về góc header
            requestAnimationFrame(function () {
                splashLogoWrapper.style.transform = 'translate3d(' + dxLogo + 'px, ' + dyLogo + 'px, 0) scale(' + sLogo + ')';
                splashTextGroup.style.transform = 'translate3d(' + dxText + 'px, ' + dyText + 'px, 0) scale(' + sText + ')';
            });

            // TỚI NƠI MỚI CHUYỂN ĐỔI CHÍNH XÁC (tại 350ms)
            setTimeout(function () {
                splashLogoWrapper.style.opacity = '0';
                splashTextGroup.style.opacity = '0';
                headerLogoContainer.style.opacity = '1';
                headerTextContainer.style.opacity = '1';
                finishDismiss();
            }, duration * 1000);

        } else if (splashLogoWrapper && headerLogoContainer) {
            // --- TRƯỜNG HỢP 2: Màn hình nhỏ (chỉ Logo) ---
            var srcRectLogo = splashLogoWrapper.getBoundingClientRect();
            var dstRectLogo = headerLogoContainer.getBoundingClientRect();

            if (srcRectLogo.width <= 0 || dstRectLogo.width <= 0) {
                fallbackDismiss();
                return;
            }

            var sLogoSmall = dstRectLogo.width / srcRectLogo.width;
            var dxLogoSmall = dstRectLogo.left - srcRectLogo.left;
            var dyLogoSmall = dstRectLogo.top - srcRectLogo.top;

            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = easeTransform;
            splashLogoWrapper.style.opacity = '1';

            if (splashTextGroup) splashTextGroup.classList.add('dismissing-text');
            if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');
            if (progressTrack) progressTrack.classList.add('dismissing-text');

            headerLogoContainer.style.transition = 'none';
            headerLogoContainer.style.opacity = '0';

            if (splash) {
                splash.classList.add('fade-bg');
            }

            if (main) {
                main.style.visibility = 'visible';
                main.style.opacity = '1';
            }

            requestAnimationFrame(function () {
                splashLogoWrapper.style.transform = 'translate3d(' + dxLogoSmall + 'px, ' + dyLogoSmall + 'px, 0) scale(' + sLogoSmall + ')';
            });

            // TỚI NƠI MỚI CHUYỂN ĐỔI CHÍNH XÁC
            setTimeout(function () {
                splashLogoWrapper.style.opacity = '0';
                headerLogoContainer.style.opacity = '1';
                finishDismiss();
            }, duration * 1000);

        } else {
            fallbackDismiss();
        }
    }

    function finishDismiss() {
        if (splash) {
            splash.style.opacity = '0';
            splash.classList.add('dismissing-splash');
        }
        document.documentElement.classList.add('no-splash');
        sessionStorage.setItem('splash_shown', 'true');
        try {
            document.dispatchEvent(new CustomEvent('splashDismissed'));
        } catch (e) {
            if (typeof Event === 'function') {
                var event = new Event('splashDismissed');
                document.dispatchEvent(event);
            }
        }
        setTimeout(function () {
            if (splash && splash.parentNode) splash.remove();
        }, 50);
    }

    function fallbackDismiss() {
        // Fade out đơn giản khi không đo được FLIP
        splash.style.transition = 'opacity 0.4s ease';
        splash.style.opacity = '0';
        var main = document.getElementById('main-content');
        if (main) {
            main.style.visibility = 'visible';
            main.style.opacity = '1';
        }
        setTimeout(function () {
            finishDismiss();
        }, 400);
    }

    // 6. Fail-safe: tối đa 3.5 giây
    setTimeout(function () {
        svgAnimationDone = true;
        checkDismiss();
    }, 3500);
})();