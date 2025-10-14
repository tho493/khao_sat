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
            setTimeout(function () {
                splashScreen.classList.add('shrinking');
                mainContent.style.visibility = 'visible';

                setTimeout(function () {
                    splashScreen.remove();
                }, 1500); // 1.5s để đợi animation fadeOut hoàn thành

            }, 1000); // 1s delay để bắt đầu animation shrinkOut
        });
    }

    document.addEventListener('DOMContentLoaded', initSplashScreen);
    // Hide warning if JS is enabled
    const warningTitle = document.getElementById('warning-title');
    if (warningTitle) warningTitle.style.display = 'none';
})();