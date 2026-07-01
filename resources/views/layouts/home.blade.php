<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://www.google.com https://www.gstatic.com https://unpkg.com https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; img-src 'self' data: https:; frame-src 'self' https://www.google.com https://www.gstatic.com; connect-src 'self' https://www.google.com https://www.gstatic.com https://maps.googleapis.com">
    <meta name="referrer" content="strict-origin-when-cross-origin"> -->
    <meta name="description"
        content="@yield('description', 'Hệ thống khảo sát trực tuyến - Nền tảng khảo sát hiện đại, bảo mật và dễ sử dụng.')" />
    <title> @yield('title', "Trang chủ") - Hệ thống khảo sát trực tuyến </title>

    <meta property="og:site_name" content="Hệ thống khảo sát trực tuyến">
    <meta name="keywords"
        content="khảo sát, survey, trực tuyến, online, hệ thống, khảo sát trực tuyến, sdu, sao đỏ, trường đại học sao đỏ" />
    <meta name="author" content="Hệ thống khảo sát trực tuyến" />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="@yield('title', 'Trang chủ') - Hệ thống khảo sát trực tuyến" />
    <meta property="og:description"
        content="@yield('og:description', 'Hệ thống khảo sát trực tuyến - Nền tảng khảo sát hiện đại, bảo mật và dễ sử dụng.')" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />

    <meta property="og:image" content="@yield('og:image', asset('/image/logo.png'))">
    <meta property="og:image:type" content="@yield('og:image:type', 'image/png')">
    <meta property="og:image:alt" content="Ảnh khảo sát">
    <meta property="og:image:secure_url" content="@yield('og:image', asset('/image/logo.png'))">
    <meta property="og:image:width" content="@yield('og:image:width', 300)">
    <meta property="og:image:height" content="@yield('og:image:height', 300)">
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <meta property="og:locale" content="vi_VN">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="stylesheet"
        href="{{ asset('css/splash-screen.css') }}?v={{ @filemtime(public_path('css/splash-screen.css')) }}">
    <!-- <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script> -->

    {{-- CSS for Glassmorphism & Improvements --}}
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ @filemtime(public_path('css/home.css')) }}">

    {{-- Christmas Theme CSS --}}
    @if (date('m') == 12 && date('d') >= 20 && date('d') <= 25)
        <link rel="stylesheet" href="/css/christmas-theme.css">
    @endif

    {{-- Happy New Year Theme CSS (Dec 28 - Jan 5) --}}
    @if ((date('m') == 12 && date('d') >= 29) || (date('m') == 1 && date('d') <= 3))
        <link rel="stylesheet" href="/css/newyear-theme.css">
    @endif


    <!-- CSS NProgress -->
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />

    {{-- CSS SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Tailwind CSS & Scripts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('styles')
</head>

<body>
    <!-- Splash (overlay) -->
    <div id="splash-screen">
        <!-- Ambient orbs -->
        <div class="splash-orb splash-orb-1"></div>
        <div class="splash-orb splash-orb-2"></div>
        <div class="splash-orb splash-orb-3"></div>

        <div class="splash-content">
            <div class="splash-logo-wrapper">
                <div class="splash-logo-bg"></div>
                <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ" class="splash-logo">
            </div>

            <h1 class="splash-title">Hệ thống Khảo sát Trực tuyến</h1>
            <p class="splash-subtitle">Trường Đại học Sao Đỏ</p>

            <noscript>
                <div id="splash-noscript-warning">Trình duyệt của bạn đang không bật Javascript. Bạn cần bật nó để
                    website có thể hoạt động.</div>
            </noscript>
        </div>

        <!-- Progress bar -->
        <div id="splash-progress-track">
            <div id="splash-progress"></div>
        </div>
    </div>
    <script src="{{ asset('js/splash-screen.js') }}?v={{ @filemtime(public_path('js/splash-screen.js')) }}"></script>

    <div class="bg-gradient-to-br from-blue-500 to-slate-50 text-slate-800">
        {{-- Main Content Wrapper --}}
        <div id="main-content" style="visibility: hidden;">

            {{-- Chatbot Container --}}
            <!-- <button class="chatbot-toggler">
                <i class="bi bi-chat-dots-fill"></i>
            </button> -->

            <!-- <div class="chatbot-container">
                <div class="chatbot-header">
                    <h2>Trợ lý ảo</h2>
                </div>
                <ul class="chatbox list-unstyled">
                    <li class="chat incoming">
                        <p>Xin chào 👋<br>Tôi có thể giúp gì cho bạn về các vấn đề thường gặp trong khảo sát?</p>
                    </li>
                </ul>
                <div class="chat-input">
                    <textarea placeholder="Nhập câu hỏi của bạn..." required></textarea>
                    <button id="send-btn"><i class="bi bi-send-fill"></i></button>
                </div>
            </div> -->

            <header class="sticky-header">
                <div class="mx-auto px-2 sm:px-4" style="max-width: 90%;">
                    <div class="flex items-center justify-between py-2">
                        <a href="{{ route('khao-sat.index') }}" class="flex items-center gap-2 sm:gap-3">
                            <div
                                class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/95 grid place-items-center shadow-md p-1">
                                <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ"
                                    class="h-full w-full object-contain">
                            </div>
                            <span class="hidden min-[320px]:block">
                                <span class="text-white font-bold text-base sm:text-lg">Hệ thống khảo sát</span>
                                <span class="hidden sm:block text-white/80 text-xs font-medium">Thu thập ý kiến, nâng
                                    cao chất lượng</span>
                            </span>
                        </a>
                        <nav class="flex items-center gap-2 sm:gap-4">
                            <a href="https://saodo.edu.vn/vi/about/Gioi-thieu-ve-truong-Dai-hoc-Sao-Do.html"
                                target="_blank"
                                class="text-white/90 hover:text-white text-xs sm:text-sm font-medium transition xs:inline">GIỚI
                                THIỆU</a>
                            <a href="javascript:void(0)" id="theme-toggle"
                                class="h-[32px] w-[32px] sm:h-[38px] sm:w-[38px] flex items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition backdrop-blur-sm"
                                title="Chuyển chế độ tối/sáng">
                                <i id="theme-toggle-icon" class="bi bi-moon-fill text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-2 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-white/20 text-white text-xs font-semibold hover:bg-white/30 transition backdrop-blur-sm"
                                title="Truy cập trang quản trị">
                                <i class="bi bi-shield-lock-fill sm:mr-1"></i> <span class="hidden xs:inline">Quản
                                    trị</span>
                            </a>
                        </nav>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            @yield('content')

            {{-- Footer --}}
            <footer
                class="relative text-white pt-16 pb-8 overflow-hidden bg-gradient-to-br from-[#174a7e] to-[#1f66b3]">
                <!-- <div class="absolute inset-0 -z-10"></div> -->
                <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5"></div>

                <div class="mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-12" style="max-width: 90%;">

                    <div class="lg:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-14 w-14 rounded-full bg-white/95 grid place-items-center shadow-md p-1">
                                <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ"
                                    class="h-full w-full object-contain">
                            </div>
                            <div>
                                <h4 class="font-extrabold text-xl">Trường Đại học Sao Đỏ</h4>
                                <p class="text-white/80 text-sm">Chất lượng toàn diện - Hợp tác sâu rộng - Phát triển
                                    bền vững</p>
                            </div>
                        </div>
                        <p class="text-white/70 text-sm mb-6">
                            Hệ thống khảo sát trực tuyến nhằm nâng cao chất lượng đào tạo và dịch vụ, lắng nghe ý kiến
                            đóng
                            góp từ các bên liên quan.
                        </p>
                        <div class="flex items-center gap-4">
                            <a href="https://www.facebook.com/truongdhsaodo" target="_blank"
                                class="text-white/70 hover:text-white transition text-2xl" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <!-- <a href="https://www.youtube.com/channel/UCiP2q-gYq8-Y-g-q" target="_blank"
                            class="text-white/70 hover:text-white transition text-2xl" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a> -->
                            <a href="mailto:info@saodo.edu.vn"
                                class="text-white/70 hover:text-white transition text-2xl" title="Email">
                                <i class="bi bi-envelope-fill"></i>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <h5 class="font-bold text-lg mb-4 tracking-wider">THÔNG TIN LIÊN HỆ</h5>
                        <div class="text-white/80 space-y-4 text-sm">
                            <div class="flex flex-row items-start gap-0" style="line-height:1.9;">
                                <span class="flex-shrink-0 flex items-center justify-center" style="height:1.6em;">
                                    <i class="bi bi-geo-alt-fill mr-3 text-base" style="vertical-align:middle;"></i>
                                </span>
                                <span class="flex-1">Số 76, Nguyễn Thị Duệ, Thái Học 2, phường Chu Văn An, thành phố Hải
                                    Phòng.</span>
                            </div>
                            <div class="flex flex-row items-start gap-0" style="line-height:1.9;">
                                <span class="flex-shrink-0 flex items-center justify-center" style="height:1.6em;">
                                    <i class="bi bi-telephone-fill mr-3 text-base" style="vertical-align:middle;"></i>
                                </span>
                                <span class="flex-1">Điện thoại: (0220) 3882 402</span>
                            </div>
                            <div class="flex flex-row items-start gap-0" style="line-height:1.9;">
                                <span class="flex-shrink-0 flex items-center justify-center" style="height:1.6em;">
                                    <i class="bi bi-printer-fill mr-3 text-base" style="vertical-align:middle;"></i>
                                </span>
                                <span class="flex-1">Fax: (0220) 3882 921</span>
                            </div>
                            <div class="flex flex-row items-start gap-0" style="line-height:1.9;">
                                <span class="flex-shrink-0 flex items-center justify-center" style="height:1.6em;">
                                    <i class="bi bi-globe2 mr-3 text-base" style="vertical-align:middle;"></i>
                                </span>
                                <span class="flex-1">
                                    <a href="https://saodo.edu.vn" class="hover:text-white hover:underline transition"
                                        target="_blank">https://saodo.edu.vn</a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <h5 class="font-bold text-lg mb-4 tracking-wider">BẢN ĐỒ</h5>
                        <div class="rounded-lg overflow-hidden shadow-lg">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3722.080211255413!2d106.39125117529709!3d21.10936808500497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31357909df4b3bff%3A0xd8784721e55d91ca!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBTYW8gxJDhu48!5e0!3m2!1svi!2s!4v1757063624491!5m2!1svi!2s"
                                class="w-full h-full" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" title="Bản đồ vị trí Trường Đại học Sao Đỏ">
                            </iframe>
                        </div>
                    </div>
                </div>

                <div class="mt-12 border-t border-white/20 pt-6 text-center text-white/60 text-sm">
                    © 2025 Trường Đại học Sao Đỏ · Hệ thống khảo sát trực tuyến.
                    <span class="text-white">
                        Designed by
                        <a style="color: aquamarine;" href="https://github.com/tho493" target="_blank"
                            class="no-underline transition">
                            tho493
                        </a>
                    </span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Interactive Effects Script --}}
    <!-- <script src="/js/interactive-effects.js"></script> -->

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script>
        // Đồng bộ icon Dark Mode ngay khi load DOM
        document.addEventListener('DOMContentLoaded', function () {
            const themeToggleIcon = document.getElementById('theme-toggle-icon');
            if (themeToggleIcon) {
                if (document.documentElement.classList.contains('dark')) {
                    themeToggleIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
                } else {
                    themeToggleIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
                }
            }

            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn && themeToggleIcon) {
                themeToggleBtn.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.contains('dark');
                    const targetThemeIsDark = !isDark;

                    // Tạo lớp phủ chuyển đổi mờ ảo (translucent & blur)
                    const overlay = document.createElement('div');
                    Object.assign(overlay.style, {
                        position: 'fixed',
                        inset: '0',
                        zIndex: '999999',
                        pointerEvents: 'none',
                        opacity: '0',
                        transition: 'opacity 0.25s ease-in-out',
                        backgroundColor: targetThemeIsDark ? 'rgba(15, 23, 42, 0.35)' : 'rgba(255, 255, 255, 0.35)',
                        backdropFilter: 'blur(8px)',
                        webkitBackdropFilter: 'blur(8px)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                    });

                    // Icon tiến trình chuyển đổi ở giữa
                    const spinner = document.createElement('div');
                    const iconClass = targetThemeIsDark ? 'bi-moon-stars-fill' : 'bi-sun-fill';
                    const iconColor = targetThemeIsDark ? '#38bdf8' : '#eab308'; // xanh dương trời hoặc vàng ấm
                    const animationName = targetThemeIsDark ? 'theme-pulse' : 'theme-spin';

                    spinner.innerHTML = `<i class="bi ${iconClass}" style="font-size: 3rem; color: ${iconColor}; display: inline-block; animation: ${animationName} 0.8s ease-in-out infinite;"></i>`;

                    // Thêm keyframe animation cho spinner nếu chưa có
                    if (!document.getElementById('theme-transition-style')) {
                        const style = document.createElement('style');
                        style.id = 'theme-transition-style';
                        style.textContent = `
                            @keyframes theme-spin {
                                0% { transform: rotate(0deg); }
                                100% { transform: rotate(360deg); }
                            }
                            @keyframes theme-pulse {
                                0% { transform: scale(0.8); opacity: 0.7; }
                                50% { transform: scale(1.15); opacity: 1; }
                                100% { transform: scale(0.8); opacity: 0.7; }
                            }
                        `;
                        document.head.appendChild(style);
                    }

                    overlay.appendChild(spinner);
                    document.body.appendChild(overlay);

                    // Kích hoạt transition xuất hiện lớp phủ
                    requestAnimationFrame(() => {
                        overlay.style.opacity = '1';
                    });

                    // Thực hiện đổi theme khi lớp phủ đã sẵn sàng (250ms)
                    setTimeout(() => {
                        if (isDark) {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                            themeToggleIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                            themeToggleIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
                        }

                        // Đợi thêm 200ms để CSS transition của các thành phần UI (textarea, card) hoàn tất dưới lớp phủ
                        setTimeout(() => {
                            // Mờ dần lớp phủ đi sau khi đổi theme hoàn tất
                            requestAnimationFrame(() => {
                                overlay.style.opacity = '0';
                            });

                            // Xóa overlay khỏi DOM khi kết thúc transition mờ dần (250ms)
                            setTimeout(() => {
                                overlay.remove();
                            }, 250);
                        }, 200);
                    }, 250);
                });
            }


        });

        NProgress.start();

        window.addEventListener('load', function () {
            NProgress.done();
        });
        document.addEventListener('ajax:send', () => NProgress.start());
        document.addEventListener('ajax:complete', () => NProgress.done());
        if (window.jQuery) {
            $(document).on('ajaxStart', () => NProgress.start());
            $(document).on('ajaxStop', () => NProgress.done());
        }

        const header = document.querySelector('header.sticky-header');
        if (header) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sr = ScrollReveal({
                origin: 'bottom',    // Xuất hiện từ phía dưới
                distance: '40px',    // Khoảng cách di chuyển
                duration: 800,       // Thời gian hiệu ứng (ms)
                delay: 200,          // Độ trễ trước khi bắt đầu (ms)
                opacity: 0,          // Bắt đầu với trạng thái trong suốt
                scale: 1,            // Không thay đổi kích thước
                easing: 'cubic-bezier(0.5, 0, 0, 1)',
                reset: false         // Chạy hiệu ứng lại
            });

            // Hiệu ứng cho Banner
            sr.reveal('.reveal-banner-text', { origin: 'left', distance: '40px', duration: 600 });
            sr.reveal('.reveal-banner-image', { origin: 'right', distance: '40px', duration: 600 });

            // Hiệu ứng cho tiêu đề section khảo sát
            sr.reveal('.reveal-section-title', { duration: 600, scale: 0.95 });

            // Hiệu ứng cho các card khảo sát (xuất hiện lần lượt)
            sr.reveal('.reveal-survey-card', { interval: 100 });
        });
    </script>
    <div id="cookie-consent"
        class="fixed bottom-20 right-0 left-0 sm:left-auto sm:bottom-24 sm:right-4 z-[100] p-4 max-w-md transition-all duration-500 transform translate-y-full opacity-0">
        <div class="glass-effect p-5 rounded-xl shadow-lg flex items-start gap-4">
            <div class="text-2xl text-blue-500 mt-1">
                <i class="bi bi-cookie"></i>
            </div>
            <div>
                <p class="text-sm text-slate-700 mb-3">
                    Trang web này sử dụng cookie để đảm bảo bạn có trải nghiệm tốt nhất. Vui lòng chấp nhận để tiếp tục.
                </p>
                <div class="flex justify-end">
                    <button id="cookie-accept"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Chấp nhận
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    {{-- Christmas Theme JS --}}
    @if (date('m') == 12 && date('d') >= 20 && date('d') <= 25)
        <script src="/js/christmas-theme.js"></script>
    @endif

    {{-- Happy New Year Theme JS (Dec 28 - Jan 5) --}}
    @if ((date('m') == 12 && date('d') >= 29) || (date('m') == 1 && date('d') <= 3))
        <script src="/js/newyear-theme.js"></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cookieConsent = document.getElementById('cookie-consent');
            const acceptButton = document.getElementById('cookie-accept');

            if (!localStorage.getItem('cookie_accepted')) {
                setTimeout(() => {
                    cookieConsent.classList.remove('translate-y-full', 'opacity-0');
                }, 1000);
            }

            acceptButton.addEventListener('click', function () {
                localStorage.setItem('cookie_accepted', 'true');
                cookieConsent.classList.add('opacity-0', 'translate-y-full');
                setTimeout(() => cookieConsent.style.display = 'none', 500);
            });
        });

        // Hàm hiển thị thông báo
        function alert(type, title = null, message = null) {
            type = type != null ? type : 'success';
            title = title != null ? title : 'Thông báo';
            const alertClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
            function alert(type, title, message) {
                if (arguments.length === 1) {
                    message = type;
                    type = 'danger';
                    title = 'Thông báo';
                } else {
                    type = type != null ? type : 'success';
                    title = title != null ? title : 'Thông báo';
                }
                const alertClass = type === 'success' ? 'bg-emerald-600 text-white shadow-emerald-500/20' : 'bg-rose-600 text-white shadow-rose-500/20';
                const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';

                // Tạo vùng chứa toast nếu chưa có
                let toastContainer = document.getElementById('custom-toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'custom-toast-container';
                    toastContainer.style.position = 'fixed';
                    toastContainer.style.top = '24px';
                    toastContainer.style.right = '24px';
                    toastContainer.style.zIndex = 99999;
                    toastContainer.style.maxWidth = '350px';
                    document.body.appendChild(toastContainer);
                }

                // Tạo HTML toast dạng TailwindCSS đặc đục, bóng đổ đẹp mắt
                const toastId = 'toast-' + Date.now() + Math.floor(Math.random() * 10000);
                const toastHtml = `
                <div id="${toastId}" class="${alertClass} px-4 py-3 rounded-xl shadow-xl mb-3 flex items-center justify-between border border-white/10 min-w-[280px] max-w-[350px] transition-all duration-300 transform translate-y-0 opacity-100" role="alert">
                    <div class="flex items-center gap-3">
                        <i class="bi ${iconClass} text-lg flex-shrink-0"></i>
                        <div class="text-sm font-medium text-left leading-snug">
                            <strong>${title}:</strong> ${message}
                        </div>
                    </div>
                    <button type="button" class="btn-close-toast text-white/70 hover:text-white transition-colors ml-4 text-lg flex-shrink-0" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;

                // Thêm toast vào vùng chứa
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);

                // Bắt sự kiện tắt bằng nút close
                const toastElem = document.getElementById(toastId);
                toastElem.querySelector('.btn-close-toast').onclick = function () {
                    toastElem.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toastElem.remove(), 300);
                };

                // Tự động ẩn sau 5 giây
                setTimeout(() => {
                    if (toastElem) {
                        toastElem.classList.add('opacity-0', 'translate-y-2');
                        setTimeout(() => { if (toastElem) toastElem.remove(); }, 300);
                    }
                }, 5000);
            }
    </script>
</body>

</html>