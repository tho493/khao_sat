<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title', "Trang chủ") - Hệ thống khảo sát trực tuyến </title>
    <!-- <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script> -->

    {{-- Tailwind via CDN for quick prototyping; replace with @vite for production --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .container-narrow {
            max-width: 85%;
        }

        .shadow-soft {
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06)
        }

        header {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        #devtools-blocker {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 32, 44, 0.95);
            /* Nền đen mờ đậm hơn */

            /* SỬA LỖI 1: SỬ DỤNG Z-INDEX CAO NHẤT CÓ THỂ */
            z-index: 2147483647;
            /* Số z-index cao nhất để đảm bảo luôn nằm trên cùng */

            display: none;
            justify-content: center;
            align-items: center;
            color: white;
            /* Màu chữ mặc định cho các phần tử con */
            text-align: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .blocker-content {
            max-width: 600px;
            /* Đảm bảo nội dung không bị trong suốt */
            opacity: 1;
        }

        .blocker-icon {
            font-size: 80px;
            color: #e53e3e;
            animation: pulse 1.5s infinite;
        }

        .blocker-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 20px;
            color: #ffffff;
        }

        .blocker-message {
            font-size: 1.2rem;
            margin-top: 15px;

            color: #e2e8f0;
            opacity: 1;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="bg-white text-slate-800">
    <div id="devtools-blocker">
        <div class="blocker-content">
            <div class="blocker-icon">
                <img src="/image/mim_cry.gif" alt="Lỗi truy cập" loop=infinite>
            </div>
            <h1 class="blocker-title">CÓ BIẾN RỒI!!!</h1>
            <p id="blocker-message" class="blocker-message">
                Đóng DevTools và tải lại trang để tiếp tục.
            </p>
        </div>
    </div>

    <div id="main-content">
        {{-- Top bar --}}
        <header class="bg-[#1f66b3] text-white">
            <div class="mx-auto px-2" style="max-width: 90%;">
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <a href="/" class="h-10 w-10 rounded-full bg-white/95 grid place-items-center">
                            <span><img src="../image/logo.png" alt=""></span>
                        </a>
                        <p class="hidden sm:block text-sm md:text-base font-semibold tracking-wide">
                            CHẤT LƯỢNG TOÀN DIỆN · HỢP TÁC SÂU RỘNG · PHÁT TRIỂN BỀN VỮNG
                        </p>
                    </div>
                    <nav>
                        <a href="https://saodo.edu.vn/vi/about/Gioi-thieu-ve-truong-Dai-hoc-Sao-Do.html"
                            class="text-white/90 hover:text-white text-sm font-medium">GIỚI THIỆU</a>
                    </nav>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        @yield('content')

        {{-- Footer --}}
        <footer class="border-t border-slate-200 py-8 bg-white">
            <div
                class="mx-auto container-narrow px-4 text-slate-700 text-sm flex flex-col md:flex-row md:justify-between md:items-start gap-8">
                <div class="md:w-2/3 mb-4 md:mb-0">
                    <div class="font-semibold text-base mb-2 text-[#1f66b3] flex items-center gap-3">
                        Trường Đại học Sao Đỏ
                        <a href="/admin"
                            class="ml-3 px-3 py-1 rounded bg-[#1f66b3] text-white text-xs font-medium hover:bg-[#174a7e] transition"
                            title="Truy cập trang quản trị">
                            <i class="bi bi-shield-lock-fill mr-1"></i> Quản trị
                        </a>
                    </div>
                    <div class="mb-1"><i class="bi bi-geo-alt-fill mr-1"></i> Địa chỉ: Số 76, Nguyễn Thị Duệ, KDC Thái
                        Học 2, P. Chu Văn An, TP. Hải Phòng</div>
                    <div class="mb-1"><i class="bi bi-telephone-fill mr-1"></i> Điện thoại: (0220) 3882 402</div>
                    <div class="mb-1"><i class="bi bi-printer-fill mr-1"></i> Fax: (0220) 3882 921</div>
                    <div class="mb-1"><i class="bi bi-envelope-fill mr-1"></i> Email: <a href="mailto:info@saodo.edu.vn"
                            class="text-[#1f66b3] hover:underline">info@saodo.edu.vn</a></div>
                    <div class="mb-2"><i class="bi bi-globe2 mr-1"></i> Website: <a href="https://saodo.edu.vn"
                            class="text-[#1f66b3] hover:underline" target="_blank">https://saodo.edu.vn</a></div>
                    <div class="text-slate-500 mt-4">
                        © {{ date('Y') }} Trường Đại học Sao Đỏ · Hệ thống khảo sát trực tuyến
                    </div>
                </div>
                <div class="md:w-1/3 flex justify-center md:justify-end">
                    <iframe
                        src="https://www.google.com/maps?q=Trường+Đại+học+Sao+Đỏ,+Số+24,+Đường+Thái+Học+2,+Phường+Sao+Đỏ,+Chí+Linh,+Hải+Dương&output=embed"
                        width="300" height="180" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="Bản đồ Trường Đại học Sao Đỏ"></iframe>
                </div>
            </div>
        </footer>
    </div>

    <!-- <script src="/js/protected.js"></script> -->
</body>

</html>