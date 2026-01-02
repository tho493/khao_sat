<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - Hệ thống khảo sát</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script>
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            color: white;
            background-size: 200% 200%;
            background-image: linear-gradient(
                45deg, 
                #667eea, 
                #764ba2, 
                #25dce2, 
                #667eea
            );
            animation: gradientAnimation 10s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.35);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: white;
        }

        .login-header h3 {
            font-weight: 700;
            color: #ffffff;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .form-control, .input-group-text {
            background-color: rgba(255, 255, 255, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: white !important;
            border-radius: 0.5rem;
        }
        
        .form-control::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: rgba(255, 255, 255, 0.6);
            opacity: 1; /* Firefox */
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }
        
        .input-group-text {
            border-right: none;
        }
        .form-control {
            border-left: none;
        }

        .btn-login {
            background: #ffffff;
            color: #3B82F6; /* Màu xanh dương đậm */
            border: none;
            border-radius: 0.5rem;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-login:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-link a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link a:hover {
            color: #ffffff;
        }
        
        .logo-section img {
            width: 80px;
            height: 80px;
        }

        /* Cookie Consent */
        .cookie-consent {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1001;
            transform: translateY(200%);
            transition: transform 0.5s ease-in-out;
        }

        .cookie-consent.show {
            transform: translateY(0);
        }

        .cookie-consent p {
            margin: 0;
            font-size: 14px;
        }

        .cookie-consent-buttons button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s;
        }

        .cookie-consent-buttons button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container" id="main-content">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="login-card p-4 p-md-5">
                    <div class="logo-section text-center mb-4">
                        <img src="image/logo.png" alt="Logo Trường Đại học Sao Đỏ">
                    </div>

                    <div class="login-header text-center mb-4">
                        <h3>Đăng nhập Quản trị</h3>
                        <p>Hệ thống Khảo sát Trực tuyến</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger bg-danger/50 text-white border-0 small p-2 text-center mb-3">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="/login" id="loginForm">
                        @csrf

                        <div class="mb-3">
                            <label for="tendangnhap" class="form-label">Tên đăng nhập</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control"
                                    id="tendangnhap" name="tendangnhap" value="{{ old('tendangnhap') }}"
                                    placeholder="Tên đăng nhập của bạn" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="matkhau" class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="matkhau"
                                    name="matkhau" placeholder="Nhập mật khẩu" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border: 1px solid rgba(255, 255, 255, 0.3) !important; border-left: none !important; color: white;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="g-recaptcha" data-theme="dark" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                        </div>

                        <button type="submit" class="btn btn-login" id="loginBtn">
                            Đăng nhập
                        </button>
                    </form>

                    <div class="back-link text-center mt-4">
                        <a href="{{ route('khao-sat.index') }}">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-white/70 small mb-0">
                        &copy; {{ date('Y') }} Trường Đại học Sao Đỏ.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="cookie-consent" class="cookie-consent">
        <p>Trang web này sử dụng cookie để đảm bảo bạn có trải nghiệm tốt nhất. Vui lòng chấp nhận để tiếp tục.</p>
        <div class="cookie-consent-buttons">
            <button id="cookie-accept">Chấp nhận</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('matkhau');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Form submission
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng nhập...';
        
        // Đảm bảo CSRF token được gửi đúng cách cho iOS WebKit
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = document.getElementById('loginForm');
        
        // Thêm CSRF token vào form nếu chưa có
        if (!form.querySelector('input[name="_token"]')) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);
        }
    });

    // Auto-hide alerts
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Cookie Consent
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
            cookieConsent.classList.remove('show');
        });
    });
</script>
</body>
</html>