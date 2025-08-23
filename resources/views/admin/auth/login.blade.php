<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống khảo sát</title>
    <!-- <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            /* Màu đỏ đậm hơn */
            animation: pulse 1.5s infinite;
        }

        .blocker-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 20px;
            color: #ffffff;
            /* SỬA LỖI 2: Đảm bảo tiêu đề màu trắng rõ ràng */
        }

        .blocker-message {
            font-size: 1.2rem;
            margin-top: 15px;

            /* SỬA LỖI 3: Đặt màu chữ rõ ràng, không bị mờ */
            color: #e2e8f0;
            opacity: 1;
            /* Đảm bảo không bị trong suốt */
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

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px 30px;
            backdrop-filter: blur(10px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            color: #666;
        }

        .form-control {
            border-left: none;
        }

        .alert {
            border-radius: 8px;
            font-size: 14px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section i {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div id="devtools-blocker">
        <div class="blocker-content">
            <div class="blocker-icon">
                <img src="/image/mim_cry.gif" alt="Lỗi truy cập">
            </div>
            <h1 class="blocker-title">CÓ BIẾN RỒI!!!</h1>
            <p id="blocker-message" class="blocker-message">
                Đóng DevTools và tải lại trang để tiếp tục.
            </p>
        </div>
    </div>

    <div class="login-container" id="main-content">
        <div class="login-card">
            <div class="logo-section">
                <i class="bi bi-clipboard-data"></i>
            </div>

            <div class="login-header">
                <h3>Đăng nhập hệ thống</h3>
                <p>Vui lòng đăng nhập để quản lý khảo sát</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField() !!}

                <div class="mb-3">
                    <label for="tendangnhap" class="form-label">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control @error('tendangnhap') is-invalid @enderror"
                            id="tendangnhap" name="tendangnhap" value="{{ old('tendangnhap') }}"
                            placeholder="Nhập tên đăng nhập" required autofocus>
                    </div>
                    @error('tendangnhap')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="matkhau" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control @error('matkhau') is-invalid @enderror" id="matkhau"
                            name="matkhau" placeholder="Nhập mật khẩu" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('matkhau')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                </div> -->

                <div class="mb-3 d-flex justify-content-center">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                    @error('g-recaptcha-response')
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Đăng nhập
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('khao-sat.index') }}">
                    <i class="bi bi-arrow-left me-1"></i>
                    Quay lại trang khảo sát
                </a>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-white small mb-0">
                &copy; {{ date('Y') }} Hệ thống khảo sát - Trường Đại học Sao Đỏ. <br> Mọi quyền được bảo lưu.
            </p>
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
        });

        // Auto-hide alerts
        setTimeout(function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    <script src="/js/protected.js"></script>
</body>

</html>