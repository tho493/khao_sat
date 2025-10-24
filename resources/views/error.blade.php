<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lỗi {{ $statusCode ?? 'Không xác định' }} - Hệ thống khảo sát</title>
    <meta name="description" content="Đã xảy ra lỗi trong quá trình xử lý yêu cầu của bạn.">
    <meta name="keywords" content="error page, system error, {{ $statusCode ?? 'error' }}">
    <style>
        :root {
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Prompt:wght@400;700&display=swap');
        }

        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            color: #acbac3;
            font-family: "Prompt", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        .container {
            text-align: center;
        }

        #warning-icon {
            font-size: 80px;
        }

        #warning-number {
            font-size: 55px;
            color: #e99415;
        }

        #countdown {
            font-size: 30px;
            font-weight: bold;
            color: #e99415;
        }

        .error-message {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            color: #856404;
        }

        h1 {
            font-size: 36px;
            margin: 20px 0;
        }

        p {
            font-size: 18px;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>

<body>
    <div class="container">
        <h1 class="warning-title">
            <span id="warning-icon">
                @if(($statusCode ?? 500) >= 500)
                    🔧
                @elseif(($statusCode ?? 404) == 403)
                    🔒
                @elseif(($statusCode ?? 419) == 419)
                    ⏰
                @else
                    ⚠️
                @endif
            </span>
            <span id="warning-number">
                {{ $statusCode ?? '500' }}
            </span>
            @php
                $currentStatusCode = $statusCode ?? 500;
                if (isset($exception)) {
                    if (method_exists($exception, 'getStatusCode')) {
                        $currentStatusCode = $exception->getStatusCode();
                    } elseif (method_exists($exception, 'getCode')) {
                        $currentStatusCode = $exception->getCode() ?: 500;
                    }
                }

                $messages = [
                    400 => 'Yêu cầu không hợp lệ',
                    401 => 'Không có quyền truy cập',
                    403 => 'Truy cập bị từ chối',
                    404 => 'Trang bạn truy cập không tồn tại',
                    405 => 'Phương thức không được phép',
                    419 => 'Phiên làm việc đã hết hạn',
                    422 => 'Dữ liệu không hợp lệ',
                    429 => 'Quá nhiều yêu cầu',
                    500 => 'Lỗi máy chủ nội bộ',
                    502 => 'Cổng kết nối không hợp lệ',
                    503 => 'Dịch vụ tạm thời không khả dụng',
                    504 => 'Hết thời gian chờ'
                ];
                $errorMessage = $message ?? ($messages[$currentStatusCode] ?? 'Đã xảy ra lỗi không xác định');
            @endphp
            {{ $errorMessage }}
        </h1>

        @if($statusCode == 419)
            <div class="error-message">
                <strong>Phiên làm việc hết hạn:</strong> Vui lòng làm mới trang và thử lại. Điều này thường xảy ra khi bạn ở
                trên trang quá lâu.
                <br><br>
            </div>
        @elseif($statusCode >= 500)
            <div class="error-message">
                <strong>Lỗi máy chủ:</strong> Đã xảy ra lỗi trong quá trình xử lý yêu cầu của bạn. Vui lòng thử lại sau.
            </div>
        @elseif($statusCode == 403)
            <div class="error-message">
                <strong>Không có quyền truy cập:</strong> Bạn không có quyền truy cập vào trang này.
            </div>
        @elseif($statusCode == 404)
            <div class="error-message">
                <strong>Không tìm thấy:</strong> Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.
            </div>
        @else
            <div class="error-message">
                <strong>Lỗi:</strong> {{ $message }}
            </div>
        @endif

        <p class="warning-message">Bạn sẽ được chuyển về trang chủ sau <span id="countdown">5</span> giây...</p>

        <a href="{{ url('/') }}" class="back-button">Về trang chủ ngay</a>
    </div>
    <script>
        // Countdown timer
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        // Kiểm tra nếu là lỗi 419 và Safari iOS
        const statusCode = {{ $statusCode ?? 500 }};
        const isIosSafari = /iPhone|iPad|iPod/i.test(navigator.userAgent) && /Safari/i.test(navigator.userAgent);

        if (statusCode === 419 && isIosSafari) {
            // Đối với Safari iOS và lỗi 419, thử làm mới token trước
            tryRefreshToken();
        } else {
            // Bình thường countdown
            startCountdown();
        }

        function tryRefreshToken() {
            // Thử refresh CSRF token
            fetch('/refresh-csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.csrf_token) {
                        // Cập nhật meta tag
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            metaTag.setAttribute('content', data.csrf_token);
                        }

                        // Thử reload trang
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        startCountdown();
                    }
                })
                .catch(() => {
                    startCountdown();
                });
        }

        function startCountdown() {
            const interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = '/';
                }
                countdownElement.textContent = countdown;
            }, 1000);
        }
    </script>
</body>

</html>