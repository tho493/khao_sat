<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảm ơn - Hệ thống khảo sát</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thank-you-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="thank-you-card">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h1 class="mt-4 mb-3">Cảm ơn bạn!</h1>
            <p class="lead mb-4">
                Chúng tôi đã nhận được phản hồi của bạn.
            </p>
            <div class="d-grid gap-2 d-md-block">
                <a href="{{ route('khao-sat.index') }}" class="btn btn-primary">
                    <i class="bi bi-house"></i> Về trang chủ
                </a>
                <!-- <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i> In xác nhận
                </button> -->
            </div>

            <hr class="my-4">

            <p class="small text-muted mb-0">
                Thời gian: {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>