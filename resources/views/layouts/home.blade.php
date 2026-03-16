<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
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

    <link rel="stylesheet" href="/css/splash-screen.css">
    <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script>

    {{-- CSS for Glassmorphism & Improvements --}}
    <link rel="stylesheet" href="/css/home.css">

    {{-- Christmas Theme CSS --}}
    @if (date('m') == 12 && date('d') >= 22 && date('d') <= 25)
        <link rel="stylesheet" href="/css/christmas-theme.css">
    @endif

    {{-- Happy New Year 2025 Theme CSS (Dec 28 - Jan 5) --}}
    @if ((date('m') == 12 && date('d') >= 29) || (date('m') == 1 && date('d') <= 3))
        <link rel="stylesheet" href="/css/newyear-theme.css">
    @endif


    <!-- CSS NProgress -->
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />

    {{-- CSS SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Tailwind CSS & Scripts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
    @include('layouts.splash-screen')
    <script src="/js/splash-screen.js"></script>

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
                        <a href="https://github.com/tho493" target="_blank" class="no-underline transition">
                            tho493
                        </a>
                    </span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Interactive Effects Script --}}
    <script src="/js/interactive-effects.js"></script>

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script>
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
    @if (date('m') == 12 && date('d') >= 22 && date('d') <= 25)
        <script src="/js/christmas-theme.js"></script>
    @endif

    {{-- Happy New Year 2025 Theme JS (Dec 28 - Jan 5) --}}
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
            const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';

            // Tạo vùng chứa toast nếu chưa có
            let toastContainer = document.getElementById('custom-toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'custom-toast-container';
                toastContainer.style.position = 'fixed';
                toastContainer.style.top = '24px';
                toastContainer.style.right = '24px';
                toastContainer.style.zIndex = 1080;
                toastContainer.style.maxWidth = '350px';
                document.body.appendChild(toastContainer);
            }

            // Tạo HTML toast
            const toastId = 'toast-' + Date.now() + Math.floor(Math.random() * 10000);
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center ${alertClass} border-0 show mb-2 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${iconClass} me-2 fs-5"></i>
                            <strong>${title}:</strong> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            // Thêm toast vào vùng chứa
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            // Bắt sự kiện tắt bằng nút close
            const toastElem = document.getElementById(toastId);
            toastElem.querySelector('.btn-close').onclick = function () {
                toastElem.classList.remove('show');
                setTimeout(() => toastElem.remove(), 400);
            };

            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                if (toastElem) {
                    toastElem.classList.remove('show');
                    setTimeout(() => { if (toastElem) toastElem.remove(); }, 400);
                }
            }, 5000);
        }
    </script>
</body>

</html>