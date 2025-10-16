<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
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
                    <a href="{{ route('admin.ctdt.index') }}"
                        class="{{ request()->routeIs('admin.ctdt.*') ? 'active' : '' }}" title="Chương trình đào tạo">
                        <i class="bi bi-journal-bookmark-fill"></i> <span>Chương trình ĐT</span>
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
</body>

</html>