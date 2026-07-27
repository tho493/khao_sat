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

    // 1. Skip nếu đã xem splash trong session này
    if (sessionStorage.getItem('splash_shown')) {
        document.documentElement.classList.add('no-splash');
        var splash = document.getElementById('splash-screen');
        var main = document.getElementById('main-content');
        if (splash && splash.parentNode) splash.remove();
        if (main) main.style.visibility = 'visible';
        return;
    }

    var splash = document.getElementById('splash-screen');
    var progressBar = document.getElementById('splash-progress');

    if (!splash) return;

    var startTime = Date.now();
    var pageLoaded = false;
    var svgAnimationDone = false;
    var dismissed = false;

    // 2. Progress bar fake-fill (0% → 88%)
    var pct = 0;
    var fillInterval = setInterval(function () {
        pct += (88 - pct) * 0.055;
        if (progressBar) progressBar.style.width = pct.toFixed(1) + '%';
    }, 40);

    // 3. SVG Stroke Drawing — khởi tạo sau khi font load xong
    function initSvgDraw() {
        var svgText = document.getElementById('splash-svg-text');
        var svg = document.getElementById('splash-title-svg');
        if (!svgText || !svg) return;

        function setup() {
            try {
                // Đảm bảo SVG element đang visible trong DOM
                if (!svgText.getBBox) return;

                // Đo chiều dài nét chữ thực tế
                var textLen = svgText.getComputedTextLength();
                if (!textLen || textLen <= 0) textLen = 600; // fallback

                // Đo bounding box chữ (bao gồm dấu tiếng Việt)
                var bbox = svgText.getBBox();

                // Validate bbox — nếu width quá nhỏ nghĩa là font chưa load
                if (bbox.width < 10) {
                    setTimeout(setup, 100);
                    return;
                }

                var padX = 6;
                var padY = 8;
                svg.setAttribute('viewBox',
                    (bbox.x - padX) + ' ' + (bbox.y - padY) + ' ' +
                    (bbox.width + padX * 2) + ' ' + (bbox.height + padY * 2)
                );

                // Set stroke-dasharray/offset inline → CSS animation sẽ animate về 0
                svgText.style.strokeDasharray = textLen;
                svgText.style.strokeDashoffset = textLen;

                // Force reflow để trình duyệt nhận giá trị inline trước khi thêm class
                void svgText.offsetWidth;

                // Bắt đầu vẽ nét
                svgText.classList.add('drawing');

                // Sau 1.8s (stroke xong) → fill trắng
                setTimeout(function () {
                    svgText.classList.remove('drawing');
                    svgText.classList.add('filled');
                }, 1800);

                // Sau 2.2s (tô màu xong hoàn toàn) → cho phép dismiss
                setTimeout(function () {
                    svgAnimationDone = true;
                    checkDismiss();
                }, 2200);
            } catch (e) {
                // Fallback: hiện chữ trắng ngay
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
                // Thêm delay nhỏ sau fonts.ready để đảm bảo render xong
                requestAnimationFrame(function () {
                    requestAnimationFrame(setup);
                });
            });
        } else {
            setTimeout(setup, 250);
        }
    }

    initSvgDraw();

    // 4. Freeze SVG text + subtitle trước khi FLIP đo vị trí
    function freezeAnimations() {
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
        if (pageLoaded && svgAnimationDone) {
            triggerDismiss();
        }
    }

    function triggerDismiss() {
        if (dismissed) return;
        dismissed = true;

        clearInterval(fillInterval);
        if (progressBar) progressBar.style.width = '100%';

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
            headerLogoContainer.style.transition = 'opacity 0.15s ease';
            headerTextContainer.style.transition = 'opacity 0.15s ease';
            headerLogoContainer.style.opacity = '0';
            headerTextContainer.style.opacity = '0';

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
            var dyText = dstTitleRect.top - srcGroupRect.top - (offsetY * sText);

            // Ẩn phông nền phụ
            if (progressTrack) progressTrack.classList.add('dismissing-text');
            orbs.forEach(function (orb) { orb.classList.add('dismissing-text'); });
            splash.classList.add('transparent-bg');
            splashLogoWrapper.classList.add('morphing');

            // Ẩn subtitle
            if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');

            // Kích hoạt hiển thị main content mượt mà
            if (main) {
                main.style.opacity = '1';
            }

            // Bay Logo
            var ease = 'transform 0.68s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.68s ease';
            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = ease;
            splashLogoWrapper.style.transform = 'translate(' + dxLogo + 'px, ' + dyLogo + 'px) scale(' + sLogo + ')';

            // Bay Text Group
            splashTextGroup.style.transformOrigin = 'top left';
            splashTextGroup.style.transition = ease;
            splashTextGroup.style.transform = 'translate(' + dxText + 'px, ' + dyText + 'px) scale(' + sText + ')';

            // Cross-fade Header
            setTimeout(function () {
                headerLogoContainer.style.transition = 'opacity 0.25s ease';
                headerTextContainer.style.transition = 'opacity 0.25s ease';
                headerLogoContainer.style.opacity = '1';
                headerTextContainer.style.opacity = '1';

                // Đồng thời mờ dần phần tử bay của splash
                splashLogoWrapper.style.transition = 'transform 0.68s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                splashTextGroup.style.transition = 'transform 0.68s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                splashLogoWrapper.style.opacity = '0';
                splashTextGroup.style.opacity = '0';
            }, 450);

            setTimeout(function () {
                finishDismiss();
            }, 1430);

        } else if (splashLogoWrapper && headerLogoContainer) {
            // --- TRƯỜNG HỢP 2: Màn hình nhỏ (chỉ Logo) ---
            headerLogoContainer.style.transition = 'opacity 0.2s ease';
            headerLogoContainer.style.opacity = '0';

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
            splash.classList.add('transparent-bg');
            splashLogoWrapper.classList.add('morphing');

            // Kích hoạt hiển thị main content mượt mà
            if (main) {
                main.style.opacity = '1';
            }

            splashLogoWrapper.style.transformOrigin = 'top left';
            splashLogoWrapper.style.transition = 'transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.65s ease';
            splashLogoWrapper.style.transform = 'translate(' + dxLogoSmall + 'px, ' + dyLogoSmall + 'px) scale(' + sLogoSmall + ')';

            setTimeout(function () {
                headerLogoContainer.style.opacity = '1';

                // Đồng thời mờ dần phần tử bay của splash
                splashLogoWrapper.style.transition = 'transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
                splashLogoWrapper.style.opacity = '0';
            }, 450);

            setTimeout(function () {
                finishDismiss();
            }, 1350);

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

    // 6. Tự động dismiss khi trang nạp xong
    window.addEventListener('load', function () {
        pageLoaded = true;
        checkDismiss();
    });

    // 7. Fail-safe: tối đa 5 giây
    setTimeout(function () {
        pageLoaded = true;
        svgAnimationDone = true;
        checkDismiss();
    }, 5000);
})();