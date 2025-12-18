@extends('layouts.home')

@section('title', $message)

@push('styles')
    <style>
        .closed-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            padding: 20px;
            background: rgba(245, 246, 250, 0.8);
        }

        .closed-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 50px;
            max-width: 650px;
            width: 100%;
            box-shadow: 0 10px 45px rgba(0, 0, 0, 0.12);
            animation: fadeInUp 0.5s ease-out;
        }

        .icon-wrapper {
            font-size: 95px;
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
        }

        .icon-expired {
            background: linear-gradient(135deg, #ff4d4f, #ff7875);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .icon-not-started {
            background: linear-gradient(135deg, #0dcaf0, #4dd5ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .closed-card h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .survey-info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px 20px;
            margin-top: 20px;
            border-left: 4px solid #0d6efd;
        }

        .back-btn {
            padding: 12px 25px;
            font-size: 17px;
            border-radius: 10px;
            transition: all 0.25s ease;
        }

        .back-btn:hover {
            transform: translateX(-3px);
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush


@section('content')
    <div class="closed-container">
        <div class="closed-card">

            {{-- Icon --}}
            <div class="icon-wrapper">
                @if($reason == 'not_started_yet')
                    <i class="bi bi-hourglass-split icon-not-started"></i>
                @else
                    <i class="bi bi-lock-fill icon-expired"></i>
                @endif
            </div>

            {{-- Tiêu đề --}}
            <h1>{{ $message ?? 'Không thể truy cập khảo sát' }}</h1>

            {{-- Nội dung --}}
            <p class="lead text-muted">
                Bạn không thể tham gia vào đợt khảo sát:
                <br>
                <strong class="text-dark">“{{ $dotKhaoSat->ten_dot }}”</strong>
            </p>

            {{-- Lý do --}}
            @if($reason == 'not_started_yet')
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> Khảo sát chưa bắt đầu. Vui lòng quay lại sau.
                </div>
            @else
                <div class="alert alert-danger mt-3">
                    <i class="bi bi-x-circle"></i> Khảo sát đã kết thúc hoặc đã bị đóng.
                </div>
            @endif

            {{-- Thời gian khảo sát --}}
            <div class="survey-info-box mt-4">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 fw-bold">Bắt đầu:</p>
                        <p class="text-muted">
                            {{ \Carbon\Carbon::parse($dotKhaoSat->tungay)->format('H:i, d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <p class="mb-1 fw-bold">Kết thúc:</p>
                        <p class="text-muted">
                            {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('H:i, d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Nút quay lại --}}
            <div class="text-center mt-4">
                <a href="{{ route('khao-sat.index') }}" class="btn btn-primary back-btn">
                    <i class="bi bi-arrow-left"></i> Quay về danh sách khảo sát
                </a>
            </div>

        </div>
    </div>
@endsection