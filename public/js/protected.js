(function () {
            'use strict';

            const devtoolsBlocker = document.getElementById('devtools-blocker');
            const message = document.getElementById('blocker-message');
            const mainContent = document.getElementById('main-content');

            console.log('Anti-DevTools script initialized.');
            if (!devtoolsBlocker) {
                console.error('Lỗi: Không tìm thấy phần tử #devtools-blocker.');
                return;
            }
            if (!mainContent) {
                console.error('Lỗi: Không tìm thấy phần tử #mainContent. Nội dung sẽ không được ẩn.');
                return;
            }
            console.log('Các phần tử Blocker và Wrapper đã được tìm thấy.');

            const threshold = 160;
            let isBlocked = false;

            function setBlocked(blocked) {
                if (blocked && !isBlocked) {
                    isBlocked = true;
                    // mainContent.style.display = 'none';
                    mainContent?.remove();
                    devtoolsBlocker.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    console.clear();
                    console.warn('DevTools DETECTED. Page content hidden.');
                } 
                else if (!blocked && isBlocked) {
                    isBlocked = false;
                    // mainContent.style.display = 'block';
                    // devtoolsBlocker.style.display = 'none';
                    message.innerHTML = 'Được rồi, bây giờ bạn có thể refresh để tiếp tục'
                    document.body.style.overflow = 'auto';
                    console.log('DevTools CLOSED. Page content restored.');
                }
            }

            function checkDevTools() {
                const widthDifference = window.outerWidth - window.innerWidth;
                const heightDifference = window.outerHeight - window.innerHeight;

                const isDevToolsOpen = widthDifference > threshold || heightDifference > threshold;

                // console.log(`Diff (W/H): ${widthDifference} / ${heightDifference}. Is Open: ${isDevToolsOpen}`);

                setBlocked(isDevToolsOpen);
            }

            setInterval(checkDevTools, 1000);

            window.addEventListener('load', checkDevTools);
            window.addEventListener('resize', checkDevTools);

        })();