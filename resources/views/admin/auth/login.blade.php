<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - Hệ thống khảo sát</title>
    
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteKey = env('RECAPTCHA_SITE_KEY');
        if (in_array(request()->ip(), ['127.0.0.1', '::1']) || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            if (empty($siteKey) || (!str_starts_with($siteKey, '1x') && !str_starts_with($siteKey, '2x') && !str_starts_with($siteKey, '3x'))) {
                $siteKey = '1x00000000000000000000AA';
            }
        }
    @endphp
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
            margin: 0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* Tăng shadow tạo chiều sâu */
            padding: 2.5rem 2rem;
            border: 1px solid #e5e7eb; /* Thêm border tinh tế */
        }

        .logo-section img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .login-header h3 {
            font-weight: 600; /* Giảm weight xuống 600 */
            color: #111827;
            font-size: 1.5rem;
            margin-bottom: 0.4rem;
        }
        
        .login-header p {
            color: #6b7280; /* Màu text phụ */
            font-size: 0.95rem;
            line-height: 1.6; /* Tăng line-height */
            font-weight: 400;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        /* Đồng nhất chiều cao cho input và button */
        .form-control, .input-group-text, .btn-login {
            height: 46px; 
        }

        .form-control {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #1f2937;
            padding: 0.5rem 1rem; /* Giảm padding để fit với height 46px */
            box-shadow: none;
            transition: all 0.2s ease;
        }
        
        .input-group-text {
            background-color: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        
        /* Trạng thái lỗi (Red Border) */
        .input-error {
            border-color: #ef4444 !important;
        }
        .input-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-size: 0.95rem;
        }

        .btn-login {
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-login:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-login:disabled {
            background: #93c5fd;
            cursor: not-allowed;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        
        .back-link a:hover {
            color: #111827;
        }
        
        .footer-text {
            text-align: center;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 1.25rem; /* Đưa gần với card hơn */
            opacity: 0.8; /* Làm mờ đi một chút */
        }

        /* reCAPTCHA fix khoảng cách */
        .recaptcha-container {
            display: flex;
            justify-content: center;
            margin: 1.25rem 0; 
            min-height: 78px; /* Giữ sẵn khoảng trống để không giật UI */
            transform: scale(0.96);
            transform-origin: center;
        }

        /* Dark Mode styles for Login Page */
        html.dark body {
            background-color: #0f172a !important; /* Slate 900 */
            color: #cbd5e1 !important;
        }

        html.dark .login-card {
            background: #1e293b !important; /* Slate 800 */
            border-color: rgba(255, 255, 255, 0.05) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        html.dark .login-header h3 {
            color: #f8fafc !important;
        }

        html.dark .login-header p {
            color: #94a3b8 !important;
        }

        html.dark .form-label {
            color: #cbd5e1 !important;
        }

        html.dark .form-control {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }

        html.dark .form-control:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }

        html.dark .input-group-text {
            background-color: #334155 !important; /* Slate 700 */
            color: #94a3b8 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        html.dark #togglePassword {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        html.dark #togglePassword i {
            color: #94a3b8 !important;
        }

        html.dark .back-link a {
            color: #94a3b8 !important;
        }

        html.dark .back-link a:hover {
            color: #f8fafc !important;
        }

        html.dark .footer-text {
            color: #64748b !important;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-section text-center mb-4">
                <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ">
            </div>

            <div class="login-header text-center mb-4">
                <h3>Đăng nhập Quản trị</h3>
                <p>Hệ thống Khảo sát Trực tuyến</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 small p-3 text-center mb-4 rounded-3 bg-danger bg-opacity-10 text-danger fade show">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="tendangnhap" class="form-label">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text {{ $errors->any() ? 'input-error bg-danger bg-opacity-10 text-danger' : '' }}"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control {{ $errors->any() ? 'input-error' : '' }}" id="tendangnhap" name="tendangnhap" value="{{ old('tendangnhap') }}" placeholder="Nhập tên đăng nhập" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="matkhau" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text {{ $errors->any() ? 'input-error bg-danger bg-opacity-10 text-danger' : '' }}"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-end-0 {{ $errors->any() ? 'input-error' : '' }}" id="matkhau" name="matkhau" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary bg-white border-start-0 {{ $errors->any() ? 'input-error' : '' }}" style="border-color: #d1d5db;" type="button" id="togglePassword" tabindex="-1">
                            <i class="bi bi-eye text-muted" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="recaptcha-container">
                    <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-response-field-name="g-recaptcha-response"></div>
                </div>

                <button type="submit" class="btn btn-login" id="loginBtn">
                    Đăng nhập
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('khao-sat.index') }}">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
        
        <div class="footer-text">
            &copy; {{ date('Y') }} Trường Đại học Sao Đỏ.
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('matkhau');
            const icon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
            // Giữ focus lại cho ô input sau khi click
            passwordInput.focus();
        });

        // Form submission state
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang đăng nhập...';
        });
    </script>
</body>
</html>