(function() {
        'use strict';

        function initSplashScreen() {
            const splashScreen = document.getElementById('splash-screen');
            const mainContent = document.getElementById('main-content');
            const progressBar = document.getElementById('splash-progress-bar');
            
            if (!splashScreen || !mainContent || !progressBar) {
                console.error("Splash screen elements not found. Aborting splash screen logic.");
                if(splashScreen) splashScreen.style.display = 'none';
                if(mainContent) mainContent.style.visibility = 'visible';
                return;
            }

            let progress = 0;
            let progressInterval;

            function updateProgress() {
                progress += Math.random() * 5 + 1;
                
                if (progress > 95) {
                    progress = 95;
                }
                
                progressBar.style.width = progress + '%';
                
                if (progress >= 95) {
                    clearInterval(progressInterval);
                }
            }

            progressInterval = setInterval(updateProgress, 100);

            window.addEventListener('load', function() {
                clearInterval(progressInterval);
                
                progressBar.style.width = '100%';

                setTimeout(function() {
                    splashScreen.classList.add('hidden');
                    mainContent.style.visibility = 'visible';
                    
                    setTimeout(function() {
                        splashScreen.remove();
                    }, 800);

                }, 400);
            });
        }

    document.addEventListener('DOMContentLoaded', initSplashScreen);
})();