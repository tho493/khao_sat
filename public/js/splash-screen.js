(function () {
    // 1. Kiểm tra nếu đã xem splash trong session này
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
    var minDisplayTime = 1800; // Đảm bảo hiệu ứng vẽ chữ (1.6s) chạy xong hẳn trước khi trượt lên

    // Fake-fill progress bar (0% -> 88%)
    var pct = 0;
    var fillInterval = setInterval(function () {
        pct += (88 - pct) * 0.055;
        if (progressBar) progressBar.style.width = pct.toFixed(1) + '%';
    }, 40);

    var dismissed = false;

    function dismiss() {
        if (dismissed) return;
        dismissed = true;

        var elapsed = Date.now() - startTime;
        var waitDelay = Math.max(0, minDisplayTime - elapsed);

        setTimeout(function () {
            clearInterval(fillInterval);
            if (progressBar) progressBar.style.width = '100%';

            var main = document.getElementById('main-content');
            var splashLogoWrapper = document.querySelector('.splash-logo-wrapper');
            var splashTextGroup = document.querySelector('.splash-text-group');
            var splashSubtitle = document.querySelector('.splash-subtitle');

            var headerLogoContainer = document.getElementById('header-logo-container');
            var headerTextContainer = document.getElementById('header-text-container');
            var orbs = document.querySelectorAll('.splash-orb');
            var progressTrack = document.getElementById('splash-progress-track');

            // Hiện main content ở bên dưới để đo chính xác vị trí Header
            if (main) main.style.visibility = 'visible';

            setTimeout(function () {
                var isHeaderTextVisible = headerTextContainer && (
                    headerTextContainer.offsetWidth > 0 ||
                    window.getComputedStyle(headerTextContainer).display !== 'none'
                );

                if (isHeaderTextVisible && splashLogoWrapper && splashTextGroup && headerLogoContainer) {
                    // --- TRƯỜNG HỢP 1: Cả Logo và Chữ cùng hiển thị trên Header ---
                    headerLogoContainer.style.transition = 'opacity 0.15s ease';
                    headerTextContainer.style.transition = 'opacity 0.15s ease';
                    headerLogoContainer.style.opacity = '0';
                    headerTextContainer.style.opacity = '0';

                    // Tính FLIP riêng cho Logo
                    var srcLogo = splashLogoWrapper.getBoundingClientRect();
                    var dstLogo = headerLogoContainer.getBoundingClientRect();
                    var dxLogo = (dstLogo.left + dstLogo.width / 2) - (srcLogo.left + srcLogo.width / 2);
                    var dyLogo = (dstLogo.top + dstLogo.height / 2) - (srcLogo.top + srcLogo.height / 2);
                    var sLogo = dstLogo.width / srcLogo.width;

                    // Tính FLIP riêng cho dòng chữ tiêu đề chính để đáp chính xác tuyệt đối từng 0.001px
                    var splashTitleLine = document.getElementById('splash-title-line') || splashTextGroup;
                    var headerTitleLine = headerTextContainer.querySelector('span') || headerTextContainer;

                    var srcTitleRect = splashTitleLine.getBoundingClientRect();
                    var srcGroupRect = splashTextGroup.getBoundingClientRect();
                    var dstTitleRect = headerTitleLine.getBoundingClientRect();

                    var sText = dstTitleRect.width / srcTitleRect.width;
                    var offsetX = srcTitleRect.left - srcGroupRect.left;
                    var offsetY = srcTitleRect.top - srcGroupRect.top;

                    var dxText = dstTitleRect.left - srcGroupRect.left - (offsetX * sText);
                    var dyText = dstTitleRect.top - srcGroupRect.top - (offsetY * sText);

                    // Ẩn orbs, progress track
                    if (progressTrack) progressTrack.classList.add('dismissing-text');
                    orbs.forEach(function (orb) {
                        orb.classList.add('dismissing-text');
                    });
                    splash.classList.add('transparent-bg');
                    splashLogoWrapper.classList.add('morphing');

                    // Bay Logo riêng
                    var ease = 'transform 0.68s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.68s ease';
                    splashLogoWrapper.style.transition = ease;
                    splashLogoWrapper.style.transform = 'translate(' + dxLogo + 'px, ' + dyLogo + 'px) scale(' + sLogo + ')';

                    // Bay toàn bộ khối Chữ (gồm 2 dòng) riêng về đáp 100% khớp vị trí Header
                    splashTextGroup.style.transition = ease;
                    splashTextGroup.style.transformOrigin = 'top left';
                    splashTextGroup.style.transform = 'translate(' + dxText + 'px, ' + dyText + 'px) scale(' + sText + ')';

                    // Hiện sẵn Header từ 520ms để cross-fade siêu mượt không khựng
                    setTimeout(function () {
                        headerLogoContainer.style.transition = 'opacity 0.2s ease';
                        headerTextContainer.style.transition = 'opacity 0.2s ease';
                        headerLogoContainer.style.opacity = '1';
                        headerTextContainer.style.opacity = '1';
                    }, 520);

                    setTimeout(function () {
                        document.documentElement.classList.add('no-splash');
                        sessionStorage.setItem('splash_shown', 'true');

                        setTimeout(function () {
                            if (splash && splash.parentNode) splash.remove();
                        }, 50);
                    }, 680);

                } else if (splashLogoWrapper && headerLogoContainer) {
                    // --- TRƯỜNG HỢP 2: Màn hình quá nhỏ (chữ ở header ẩn), chỉ trượt Logo lên Header ---
                    headerLogoContainer.style.transition = 'opacity 0.2s ease';
                    headerLogoContainer.style.opacity = '0';

                    var srcRectLogo = splashLogoWrapper.getBoundingClientRect();
                    var dstRectLogo = headerLogoContainer.getBoundingClientRect();

                    var deltaXLogo = (dstRectLogo.left + dstRectLogo.width / 2) - (srcRectLogo.left + srcRectLogo.width / 2);
                    var deltaYLogo = (dstRectLogo.top + dstRectLogo.height / 2) - (srcRectLogo.top + srcRectLogo.height / 2);
                    var scaleLogo = dstRectLogo.width / srcRectLogo.width;

                    if (splashTextGroup) splashTextGroup.classList.add('dismissing-text');
                    if (splashSubtitle) splashSubtitle.classList.add('dismissing-text');
                    if (progressTrack) progressTrack.classList.add('dismissing-text');
                    orbs.forEach(function (orb) {
                        orb.classList.add('dismissing-text');
                    });
                    splash.classList.add('transparent-bg');
                    splashLogoWrapper.classList.add('morphing');

                    splashLogoWrapper.style.transition = 'transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1)';
                    splashLogoWrapper.style.transform = 'translate(' + deltaXLogo + 'px, ' + deltaYLogo + 'px) scale(' + scaleLogo + ')';

                    setTimeout(function () {
                        headerLogoContainer.style.opacity = '1';
                        document.documentElement.classList.add('no-splash');
                        sessionStorage.setItem('splash_shown', 'true');

                        setTimeout(function () {
                            if (splash && splash.parentNode) splash.remove();
                        }, 50);
                    }, 650);

                } else {
                    document.documentElement.classList.add('no-splash');
                    sessionStorage.setItem('splash_shown', 'true');
                    if (splash && splash.parentNode) splash.remove();
                }
            }, 100);
        }, waitDelay);
    }

    // Tự động dismiss khi trang nạp xong
    window.addEventListener('load', dismiss);

    // Fail-safe: tối đa 5 giây
    setTimeout(dismiss, 5000);
})();