(function () {
    var splash = document.getElementById('splash-screen');
    var progressBar = document.getElementById('splash-progress');

    // Không tìm thấy splash → không làm gì
    if (!splash) return;

    // ⚠️ KHÔNG query #main-content ở đây — DOM chưa ready,
    //    script chạy synchronous trước khi #main-content được parse.
    //    Query lazily bên trong dismiss() sau khi window.load.

    // --- Fake-fill progress bar (0 → 85%, ease-out) ---
    var pct = 0;
    var fillInterval = setInterval(function () {
        pct += (85 - pct) * 0.055;
        if (progressBar) progressBar.style.width = pct.toFixed(1) + '%';
    }, 40);

    var dismissed = false;

    function dismiss() {
        if (dismissed) return;
        dismissed = true;

        var main = document.getElementById('main-content');

        clearInterval(fillInterval);
        if (progressBar) progressBar.style.width = '100%';

        setTimeout(function () {
            if (main) {
                main.style.visibility = 'visible';
            } else {
                // Fail-safe: nếu vẫn không tìm thấy, unhide toàn bộ body
                document.body.style.visibility = 'visible';
            }

            splash.classList.add('dismissing');

            setTimeout(function () {
                if (splash && splash.parentNode) splash.remove();
            }, 380);

        }, 300);
    }

    window.addEventListener('load', dismiss);

    // Fail-safe: tối đa 5s dù load event không fire
    setTimeout(dismiss, 5000);
})();