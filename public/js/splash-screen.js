(function () {
    function initSplashScreen() {
        const splashScreen = document.getElementById('splash-screen');
        const mainContent = document.getElementById('main-content');

        if (!splashScreen || !mainContent) {
            console.error("Splash screen elements not found.");
            if (splashScreen) splashScreen.style.display = 'none';
            if (mainContent) mainContent.style.visibility = 'visible';
            return;
        }

        window.addEventListener('load', function () {
            // Dismiss ngay khi load xong, không delay thêm
            splashScreen.classList.add('shrinking');
            mainContent.style.visibility = 'visible';

            setTimeout(function () {
                splashScreen.remove();
            }, 300); // khớp với duration fadeOut trong CSS
        });
    }

    document.addEventListener('DOMContentLoaded', initSplashScreen);
    // Hide warning if JS is enabled
    const warningTitle = document.getElementById('warning-title');
    if (warningTitle) warningTitle.style.display = 'none';
})();