<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Hệ thống khảo sát</title>
    <style>
        /* View Transitions API styles for circular theme transition */
        ::view-transition-old(root),
        ::view-transition-new(root) {
            animation: none;
            mix-blend-mode: normal;
        }

        ::view-transition-old(root) {
            z-index: 1;
        }

        ::view-transition-new(root) {
            z-index: 9999;
        }
    </style>
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
    {{--
    @yield('splash-screen')
    --}}

    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}"
                    class="d-flex align-items-center text-decoration-none components w-100">
                    <img src="/image/logo.png" alt="Logo"
                        style="height: 36px; width: 36px; min-width: 36px; object-fit: contain;">
                    <!-- <span class="logo-text fs-5 fw-bold text-dark">Survey SDU</span> -->
                </a>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi {{ request()->routeIs('admin.dashboard') ? 'bi-grid-fill' : 'bi-grid' }}"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.mau-khao-sat.index') }}"
                        class="{{ request()->routeIs('admin.mau-khao-sat.*') ? 'active' : '' }}">
                        <i
                            class="bi {{ request()->routeIs('admin.mau-khao-sat.*') ? 'bi-file-earmark-text-fill' : 'bi-file-earmark-text' }}"></i>
                        <span>Mẫu khảo sát</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.dot-khao-sat.index') }}"
                        class="{{ request()->routeIs('admin.dot-khao-sat.*') ? 'active' : '' }}">
                        <i
                            class="bi {{ request()->routeIs('admin.dot-khao-sat.*') ? 'bi-calendar-check-fill' : 'bi-calendar-check' }}"></i>
                        <span>Đợt khảo sát</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.bao-cao.index') }}"
                        class="{{ request()->routeIs('admin.bao-cao.*') ? 'active' : '' }}">
                        <i
                            class="bi {{ request()->routeIs('admin.bao-cao.*') ? 'bi-bar-chart-fill' : 'bi-bar-chart-line' }}"></i>
                        <span>Báo cáo</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.data-source.index') }}"
                        class="{{ request()->routeIs('admin.data-source.*') ? 'active' : '' }}"
                        title="Câu hỏi tùy chỉnh">
                        <i
                            class="bi {{ request()->routeIs('admin.data-source.*') ? 'bi-database-fill' : 'bi-database' }}"></i>
                        <span>Câu hỏi tùy chỉnh</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.nam-hoc.index') }}"
                        class="{{ request()->routeIs('admin.nam-hoc.*') ? 'active' : '' }}" title="Năm học">
                        <i
                            class="bi {{ request()->routeIs('admin.nam-hoc.*') ? 'bi-calendar3-fill' : 'bi-calendar3' }}"></i>
                        <span>Năm học</span>
                    </a>
                </li>
                <hr class="my-2" style="border-color: rgba(0,0,0,0.05); margin: 8px 12px;">
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi {{ request()->routeIs('admin.users.*') ? 'bi-people-fill' : 'bi-people' }}"></i>
                        <span>Người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.logs.index') }}"
                        class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                        <i
                            class="bi {{ request()->routeIs('admin.logs.*') ? 'bi-journal-richtext' : 'bi-journal-text' }}"></i>
                        <span>Nhật ký</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.dbbackups.index') }}"
                        class="{{ request()->routeIs('admin.dbbackups.*') ? 'active' : '' }}">
                        <i
                            class="bi {{ request()->routeIs('admin.dbbackups.*') ? 'bi-device-hdd-fill' : 'bi-device-hdd' }}"></i>
                        <span>Sao lưu CSDL</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar Footer (Profile / Dropup) -->
            <div class="sidebar-footer dropup">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                    id="sidebarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-container d-flex align-items-center">
                        <i class="bi bi-person-circle fs-4"></i>
                    </div>
                    <span class="user-name ms-3 text-truncate">{{ auth()->user()->hoten ?? 'Admin' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark shadow glass-effect" aria-labelledby="sidebarUserDropdown"
                    style="border-radius: 12px; margin-bottom: 10px; width: calc(100% - 24px); left: 12px;">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('admin.users.edit', auth()->user()->id) }}">
                            <i class="bi bi-person me-2"></i> Thông tin cá nhân
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.15);">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="dropdown-item py-2 text-danger d-flex align-items-center border-0 bg-transparent w-100">
                                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- Backdrop cho mobile --}}
        <div id="sidebar-backdrop" class="sidebar-backdrop d-lg-none"></div>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light top-navbar">
                <span id="text-admin-panel" class="fs-5 fw-bold position-absolute start-50 translate-middle-x">Admin
                    Panel</span>
                <button id="mobileSidebarToggle" class="btn btn-link d-lg-none me-3" aria-label="Mở menu">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <div class="ms-auto d-flex align-items-center gap-2">
                    {{-- Nút chuyển đổi Theme --}}
                    <button id="adminThemeToggle" class="btn btn-link p-2" aria-label="Đổi chế độ sáng/tối"
                        style="font-size: 1.25rem; color: inherit; text-decoration: none;">
                        <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
                        <i class="bi bi-moon-stars-fill" id="themeIconDark" style="display:none;"></i>
                    </button>
                    <div class="profile-dropdown d-lg-none">
                        <div class="dropdown">
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
            if (arguments.length === 1) {
                message = type;
                type = 'danger';
                title = 'Thông báo';
            } else {
                type = type != null ? type : 'success';
                title = title != null ? title : 'Thông báo';
            }
            const alertBg = type === 'success' ? 'background:#059669;' : 'background:#e11d48;';
            const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';

            let toastContainer = document.getElementById('custom-toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'custom-toast-container';
                Object.assign(toastContainer.style, { position: 'fixed', top: '24px', right: '24px', zIndex: '99999', maxWidth: '350px' });
                document.body.appendChild(toastContainer);
            }

            const toastId = 'toast-' + Date.now() + Math.floor(Math.random() * 10000);
            const toastHtml = `
                <div id="${toastId}" style="${alertBg} color:#fff; padding:12px 16px; border-radius:12px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; min-width:280px; max-width:350px; box-shadow:0 10px 25px rgba(0,0,0,0.3); transition:opacity 0.3s,transform 0.3s;" role="alert">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="bi ${iconClass}" style="font-size:1.1rem;"></i>
                        <div style="font-size:0.875rem;font-weight:500;"><strong>${title}:</strong> ${message}</div>
                    </div>
                    <button onclick="this.parentElement.style.opacity='0';setTimeout(()=>this.parentElement.remove(),300)" style="background:none;border:none;color:rgba(255,255,255,0.7);font-size:1.1rem;cursor:pointer;margin-left:12px;" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            setTimeout(() => {
                const el = document.getElementById(toastId);
                if (el) { el.style.opacity = '0'; setTimeout(() => { if (el) el.remove(); }, 300); }
            }, 5000);
        }

        // Theme toggle logic
        (function () {
            const iconLight = document.getElementById('themeIconLight');
            const iconDark = document.getElementById('themeIconDark');
            const toggleBtn = document.getElementById('adminThemeToggle');

            function updateIcons() {
                const isDark = document.documentElement.classList.contains('dark');
                if (iconLight && iconDark) {
                    iconLight.style.display = isDark ? 'inline' : 'none';
                    iconDark.style.display = isDark ? 'none' : 'inline';
                }
            }
            updateIcons();

            if (toggleBtn) {
                // Thêm CSS transition cho icon
                if (iconLight) iconLight.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                if (iconDark) iconDark.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';

                function toggleTheme() {
                    const isDark = document.documentElement.classList.contains('dark');
                    if (isDark) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    updateIcons();
                }

                toggleBtn.addEventListener('click', function (event) {
                    const activeIcon = document.documentElement.classList.contains('dark') ? iconLight : iconDark;

                    // Hiệu ứng xoay và thu nhỏ icon trước
                    if (activeIcon) {
                        activeIcon.style.transform = 'rotate(180deg) scale(0.3)';
                    }

                    setTimeout(() => {
                        // Nếu trình duyệt hỗ trợ View Transitions API
                        if (document.startViewTransition) {
                            const rect = toggleBtn.getBoundingClientRect();
                            const x = rect.left + rect.width / 2;
                            const y = rect.top + rect.height / 2;

                            const endRadius = Math.hypot(
                                Math.max(x, window.innerWidth - x),
                                Math.max(y, window.innerHeight - y)
                            );

                            const transition = document.startViewTransition(() => {
                                toggleTheme();
                            });

                            transition.ready.then(() => {
                                const clipPath = [
                                    `circle(0px at ${x}px ${y}px)`,
                                    `circle(${endRadius}px at ${x}px ${y}px)`
                                ];

                                document.documentElement.animate(
                                    {
                                        clipPath: clipPath
                                    },
                                    {
                                        duration: 500,
                                        easing: 'ease-in-out',
                                        pseudoElement: '::view-transition-new(root)'
                                    }
                                );
                            });
                        } else {
                            // Fallback cho trình duyệt không hỗ trợ (ví dụ: Firefox)
                            const overlay = document.createElement('div');
                            Object.assign(overlay.style, {
                                position: 'fixed',
                                inset: '0',
                                zIndex: '999999',
                                pointerEvents: 'none',
                                opacity: '0',
                                transition: 'opacity 0.25s ease-in-out',
                                backgroundColor: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.15)' : 'rgba(15, 23, 42, 0.15)',
                                backdropFilter: 'blur(4px)',
                                webkitBackdropFilter: 'blur(4px)'
                            });
                            document.body.appendChild(overlay);

                            requestAnimationFrame(() => {
                                overlay.style.opacity = '1';
                            });

                            setTimeout(() => {
                                toggleTheme();
                                requestAnimationFrame(() => {
                                    overlay.style.opacity = '0';
                                });
                                setTimeout(() => overlay.remove(), 250);
                            }, 150);
                        }

                        // Khôi phục lại icon sau khi chuyển đổi hoàn tất
                        setTimeout(() => {
                            const newActiveIcon = document.documentElement.classList.contains('dark') ? iconLight : iconDark;
                            if (newActiveIcon) {
                                newActiveIcon.style.transform = 'rotate(360deg) scale(1)';
                                setTimeout(() => {
                                    newActiveIcon.style.transition = 'none';
                                    newActiveIcon.style.transform = 'none';
                                    setTimeout(() => {
                                        newActiveIcon.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                                    }, 50);
                                }, 400);
                            }
                        }, 50);

                    }, 150);
                });
            }
        })();
    </script>
</body>

</html>