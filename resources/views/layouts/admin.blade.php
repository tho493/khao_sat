<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Hệ thống khảo sát</title>
    @stack('styles')
    <!-- CSS NProgress -->
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="/css/admin.css">
</head>

<body>
    @yield('splash-screen')

    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}"
                    class="d-flex align-items-center justify-content-center text-decoration-none components">
                    <img src="/image/logo.png" alt="Logo" style="height: 40px;" class="logo-collapsed">
                    <div class="logo-expanded align-items-center gap-2">
                        <img src="/image/logo.png" alt="Logo" style="height: 40px;">
                    </div>
                </a>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.mau-khao-sat.index') }}"
                        class="{{ request()->routeIs('admin.mau-khao-sat.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text-fill"></i> <span>Mẫu khảo sát</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.dot-khao-sat.index') }}"
                        class="{{ request()->routeIs('admin.dot-khao-sat.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check-fill"></i> <span>Đợt khảo sát</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.bao-cao.index') }}"
                        class="{{ request()->routeIs('admin.bao-cao.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> <span>Báo cáo</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.data-source.index') }}"
                        class="{{ request()->routeIs('admin.data-source.*') ? 'active' : '' }}"
                        title="Câu hỏi tùy chỉnh">
                        <i class="bi bi-database-fill"></i> <span>Câu hỏi tùy chỉnh</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.nam-hoc.index') }}"
                        class="{{ request()->routeIs('admin.nam-hoc.*') ? 'active' : '' }}" title="Năm học">
                        <i class="bi bi-calendar-range-fill"></i> <span>Năm học</span>
                    </a>
                </li>
                <hr class="my-3" style="border-color: rgba(0,0,0,0.07);">
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> <span>Người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.faq.index') }}"
                        class="{{ request()->routeIs('admin.faq.*') ? 'active' : '' }}" title="FAQ Chatbot">
                        <i class="bi bi-chat-left-dots-fill"></i> <span>FAQ Chatbot</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.logs.index') }}"
                        class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> <span>Nhật ký</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.dbbackups.index') }}"
                        class="{{ request()->routeIs('admin.dbbackups.*') ? 'active' : '' }}">
                        <i class="bi bi-database-fill"></i> <span>Sao lưu CSDL</span>
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Backdrop cho mobile --}}
        <div id="sidebar-backdrop" class="sidebar-backdrop d-lg-none"></div>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light top-navbar" style="border-left: none !important">
                <span id="text-admin-panel" class="fs-5 fw-bold">Admin Panel</span>
                <button id="mobileSidebarToggle" class="btn btn-link d-lg-none me-3" aria-label="Mở menu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <div class="ms-auto profile-dropdown">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                            <span class="fw-medium">{{ auth()->user()->hoten ?? 'Admin' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-effect">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.users.edit', auth()->user()->id) }}">
                                    <i class="bi bi-person me-2"></i> Thông tin cá nhân
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main id="main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        $(document).on('page:fetch', function () { NProgress.start(); });
        $(document).on('page:change', function () { NProgress.done(); });
        $(document).on('page:restore', function () { NProgress.remove(); });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const toggleBtn = document.getElementById('mobileSidebarToggle');
            const textAdminPanel = document.getElementById('text-admin-panel');
            const isDesktop = () => window.innerWidth >= 992;

            function setupDesktopHover() {
                sidebar.addEventListener('mouseenter', () => sidebar.classList.remove('collapsed'));
                sidebar.addEventListener('mouseleave', () => sidebar.classList.add('collapsed'));
            }

            function removeDesktopHover() {
                $(sidebar).off('mouseenter mouseleave');
            }

            function handleResize() {
                if (isDesktop()) {
                    sidebar.classList.add('collapsed');
                    sidebar.classList.remove('active');
                    backdrop.classList.remove('show');
                    document.body.style.overflow = '';
                    setupDesktopHover();
                } else {
                    textAdminPanel.style = "display: none;"
                    sidebar.classList.remove('collapsed');
                    removeDesktopHover();
                }
            }

            // Mobile toggle
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    backdrop.classList.toggle('show');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
                });
            }
            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    backdrop.classList.remove('show');
                    document.body.style.overflow = '';
                });
            }

            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
    @stack('scripts')

    <style>
        #cookie-consent {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1050;
            max-width: 380px;
            transition: opacity 0.5s, transform 0.5s;
            opacity: 0;
            transform: translateY(100%);
        }

        #cookie-consent.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    <div id="cookie-consent">
        <div class="card glass-effect shadow-lg border-0">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-cookie fs-3 text-primary me-3"></i>
                <div>
                    <p class="card-text mb-2">
                        Trang web này sử dụng cookie để đảm bảo bạn có trải nghiệm tốt nhất. Vui lòng chấp nhận để tiếp
                        tục.
                    </p>
                    <div class="d-flex justify-content-end">
                        <button id="cookie-accept" class="btn btn-primary btn-sm px-3">Chấp nhận</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cookieConsent = document.getElementById('cookie-consent');
            const acceptButton = document.getElementById('cookie-accept');

            if (!localStorage.getItem('cookie_accepted')) {
                setTimeout(() => {
                    cookieConsent.classList.add('show');
                }, 1000);
            }

            acceptButton.addEventListener('click', function () {
                localStorage.setItem('cookie_accepted', 'true');
                cookieConsent.classList.remove('show'); // Bắt đầu hiệu ứng ẩn
                setTimeout(() => cookieConsent.style.display = 'none', 500); // Ẩn hoàn toàn sau khi hiệu ứng kết thúc
            });
        });

        // Hàm hiển thị thông báo
        function alert(type, title, message) {
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