(function() {
function initSplashScreen() {
    const splashScreen = document.getElementById('splash-screen');
    const mainContent = document.getElementById('main-content');
    // Progress bar removed

    if (!splashScreen || !mainContent) {
        console.error("Splash screen elements not found.");
        if(splashScreen) splashScreen.style.display = 'none';
        if(mainContent) mainContent.style.visibility = 'visible';
        return;
    }

    window.addEventListener('load', function() {
        setTimeout(function() {
            splashScreen.classList.add('hidden');
            mainContent.style.visibility = 'visible';
            
            setTimeout(function() {
                splashScreen.remove();
            }, 400);

        }, 1200);
    });
}

document.addEventListener('DOMContentLoaded', initSplashScreen);
// Hide warning if JS is enabled
const warningTitle = document.getElementById('warning-title');
if (warningTitle) warningTitle.style.display = 'none';
})();