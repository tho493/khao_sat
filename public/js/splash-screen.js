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
                // --- 1. SETUP LOGO PATHS (Nét vẽ đơn sắc ban đầu) ---
                var logoPaths = logoSvg ? logoSvg.querySelectorAll('path') : [];
                for (var i = 0; i < logoPaths.length; i++) {
                    var path = logoPaths[i];
                    var origFill = path.getAttribute('fill') || '#ffffff';
                    path.setAttribute('data-fill', origFill);

                    // Đặt transition = none trước để không bị nhấp nháy nét vẽ cũ
                    path.style.transition = 'none';
                    path.style.fill = 'transparent';
                    path.style.stroke = 'rgba(255, 255, 255, 0.9)';
                    path.style.strokeWidth = '1.4px';
                    path.style.strokeLinecap = 'round';
                    path.style.strokeLinejoin = 'round';

                    var pLen = path.getTotalLength ? path.getTotalLength() : 200;
                    if (!pLen || isNaN(pLen)) pLen = 200;
                    path.style.strokeDasharray = pLen;
                    path.style.strokeDashoffset = pLen;
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

                var padX = 6;
                var padY = 8;
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

                // Ep layout reflow đe trinh duyet ghi nhận trạng thái offset = pLen (chua ve)
                if (logoSvg) void logoSvg.getBoundingClientRect();
                void svgTitle.getBoundingClientRect();

                // --- 3. TRIGGER SYNCHRONIZED STROKE DRAWING ---
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        // Bật transition và kích hoạt nét vẽ từ 0% đến 100% cùng lúc cho Logo & Chữ
                        for (var j = 0; j < logoPaths.length; j++) {
                            logoPaths[j].style.transition = 'stroke-dashoffset 1.25s cubic-bezier(0.35, 0, 0.15, 1), fill 0.55s ease-out 0.05s, stroke 0.4s ease-out';
                            logoPaths[j].style.strokeDashoffset = '0';
                        }
                        svgText.style.transition = 'stroke-dashoffset 1.25s cubic-bezier(0.35, 0, 0.15, 1), fill 0.5s ease-out, stroke 0.4s ease-out';
                        svgText.classList.add('drawing');
                        svgText.style.strokeDashoffset = '0';
                    });
                });

                // --- 4. PHASE 2: TÔ MÀU HIỆN RÕ (AT 1.25s) ---
                setTimeout(function () {
                    // Tô màu các mảng path của Logo & bật background nền trắng cho logo
                    if (logoContainer) logoContainer.classList.add('filled');
                    for (var k = 0; k < logoPaths.length; k++) {
                        var f = logoPaths[k].getAttribute('data-fill');
                        logoPaths[k].style.fill = f;
                        logoPaths[k].style.stroke = 'transparent';
                    }

                    // Tô màu trắng sáng cho chữ
                    svgText.classList.remove('drawing');
                    svgText.classList.add('filled');
                }, 1250);

                // --- 5. BẮT ĐẦU DISMISS BAY LÊN (AT 2.0s) ---
                setTimeout(function () {
                    svgAnimationDone = true;
                    checkDismiss();
                }, 2000);

            } catch (e) {
                // Fallback nếu browser không hỗ trợ SVG length measurement
                if (logoPaths) {
                    for (var m = 0; m < logoPaths.length; m++) {
                        logoPaths[m].style.fill = logoPaths[m].getAttribute('fill') || '#ffffff';
                        logoPaths[m].style.stroke = 'transparent';
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

        // Đợi font load xong rồi mới đo SVG
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () {
                requestAnimationFrame(function () {
                    requestAnimationFrame(setup);
                });
            });
        } else {
            setTimeout(setup, 200);
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
            var paths = logoSvg.querySelectorAll('path');
            for (var i = 0; i < paths.length; i++) {
                paths[i].style.transition = 'none';
                paths[i].style.strokeDashoffset = '0';
                var f = paths[i].getAttribute('data-fill') || paths[i].getAttribute('fill');
                if (f) paths[i].style.fill = f;
                paths[i].style.stroke = 'transparent';
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

        if (isHeaderTextVisible && splashLogoWrapper && splashTextGroup && headerLogoContainer) {
            // --- TRƯỜNG HỢP 1: Cả Logo + Chữ trên Header ---
            // FLIP Logo
            var srcLogo = splashLogoWrapper.getBoundingClientRect();
            var dstLogo = headerLogoContainer.getBoundingClientRect();

            // Validate rects
            if (srcLogo.width <= 0 || dstLogo.width <= 0) {
                fallbackDismiss();
                return;
            }

            var sLogo = dstLogo.width / srcLogo.width;
            var dxLogo = dstLogo.left - srcLogo.left;
            var dyLogo = dstLogo.top - srcLogo.top;

            // FLIP Text Group
            var splashTitleLine = document.getElementById('splash-title-line');
            var headerTitleLine = document.getElementById('header-title-line') ||
                headerTextContainer.querySelector('span') || headerTextContainer;

            var srcTitleRect = (splashTitleLine || splashTextGroup).getBoundingClientRect();
            var srcGroupRect = splashTextGroup.getBoundingClientRect();
            var dstTitleRect = headerTitleLine.getBoundingClientRect();

            if (srcTitleRect.width <= 0 || dstTitleRect.width <= 0) {
                fallbackDismiss();
                return;
            }

            var sText = dstTitleRect.width / srcTitleRect.width;
            var offsetX = srcTitleRect.left - srcGroupRect.left;
            var offsetY = srcTitleRect.top - srcGroupRect.top;
            var dxText = dstTitleRect.left - srcGroupRect.left - (offsetX * sText);
            var dyText = dstTitleRect.top - srcGroupRect.top - (offsetY * sText) - (6 * sText);

            var ease = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.18s ease 0.2s';

            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = ease;

            splashTextGroup.style.transformOrigin = 'top left';
            splashTextGroup.style.transition = ease;

            // Đồng bộ hoá sự xuất hiện (fade-in) của logo & text thật trên Header
            headerLogoContainer.style.transition = 'opacity 0.18s ease 0.2s';
            headerTextContainer.style.transition = 'opacity 0.18s ease 0.2s';

            // Kích hoạt transform bay lên góc và mượt mà trong rAF tiếp theo (loại bỏ forced reflows)
            requestAnimationFrame(function () {
                splashLogoWrapper.style.transform = 'translate3d(' + dxLogo + 'px, ' + dyLogo + 'px, 0) scale(' + sLogo + ')';
                splashLogoWrapper.style.opacity = '0';

                splashTextGroup.style.transform = 'translate3d(' + dxText + 'px, ' + dyText + 'px, 0) scale(' + sText + ')';
                splashTextGroup.style.opacity = '0';

                headerLogoContainer.style.opacity = '1';
                headerTextContainer.style.opacity = '1';
            });

            // Ẩn phông nền phụ
            if (progressTrack) progressTrack.classList.add('dismissing-text');
            orbs.forEach(function (orb) { orb.classList.add('dismissing-text'); });
            var bgOverlay = document.querySelector('#splash-screen .splash-bg-overlay');
            if (bgOverlay) bgOverlay.classList.add('dismissing-bg');
            splashLogoWrapper.classList.add('morphing');

            // Ẩn subtitle
            if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');

            // Kích hoạt hiển thị main content mượt mà
            if (main) {
                main.style.opacity = '1';
            }

            // Đợi toàn bộ quá trình hoàn tất rồi đóng splash
            setTimeout(function () {
                finishDismiss();
            }, 420);

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

            if (splashTextGroup) splashTextGroup.classList.add('dismissing-text');
            if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');
            if (progressTrack) progressTrack.classList.add('dismissing-text');
            orbs.forEach(function (orb) { orb.classList.add('dismissing-text'); });
            var bgOverlay = document.querySelector('#splash-screen .splash-bg-overlay');
            if (bgOverlay) bgOverlay.classList.add('dismissing-bg');
            splashLogoWrapper.classList.add('morphing');

            // ease-in cho exit animation nhỏ
            var easeSmall = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.18s ease 0.2s';
            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = easeSmall;

            headerLogoContainer.style.transition = 'opacity 0.18s ease 0.2s';

            requestAnimationFrame(function () {
                splashLogoWrapper.style.transform = 'translate3d(' + dxLogoSmall + 'px, ' + dyLogoSmall + 'px, 0) scale(' + sLogoSmall + ')';
                splashLogoWrapper.style.opacity = '0';
                headerLogoContainer.style.opacity = '1';
            });

            // Kích hoạt hiển thị main content mượt mà
            if (main) {
                main.style.opacity = '1';
            }

            // Đợi toàn bộ quá trình hoàn tất
            setTimeout(function () {
                finishDismiss();
            }, 380);

        } else {
            fallbackDismiss();
        }
    }

    function finishDismiss() {
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

    // 6. Fail-safe: tối đa 5 giây
    setTimeout(function () {
        svgAnimationDone = true;
        checkDismiss();
    }, 5000);
})();