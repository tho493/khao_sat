@extends('layouts.home')

@section('title', $message)

@push('styles')
    <style>
        .closed-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            text-align: center;
        }

        .closed-card {
            background: white;
            border-radius: 15px;
            padding: 40px 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            animation: fadeInUp 0.6s ease-out;
        }

        .icon-wrapper {
            font-size: 80px;
            line-height: 1;
            margin-bottom: 20px;
        }

        .icon-expired {
            color: #dc3545;
        }

        /* Màu đỏ cho hết hạn/đóng */
        .icon-not-started {
            color: #0dcaf0;
        }

        /* Màu xanh dương cho chưa bắt đầu */
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
    </style>
@endpush

@section('content')
    <div class="closed-container">
        <div class="closed-card">
            <div class="icon-wrapper">
                {{-- Chọn icon dựa vào lý do --}}
                @if($reason == 'not_started_yet')
                    <i class="bi bi-hourglass-split icon-not-started"></i>
                @else
                    <i class="bi bi-lock-fill icon-expired"></i>
                @endif
            </div>

            <h1 class="mb-3">{{ $message ?? 'Không thể truy cập' }}</h1>

            <p class="lead text-muted">
                Bạn không thể tham gia vào đợt khảo sát:
                <br>
                <strong class="text-dark">"{{ $dotKhaoSat->ten_dot }}"</strong>
            </p>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Thời gian bắt đầu:</strong></p>
                    <p>
                        {{\Carbon\Carbon::parse($dotKhaoSat->tungay)->format('H:i, d/m/Y')}}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Thời gian kết thúc:</strong></p>
                    <p>
                        {{\Carbon\Carbon::parse($dotKhaoSat->denngay)->format('H:i, d/m/Y')}}
                    </p>
                </div>
            </div>

            @if($reason == 'not_started_yet')
                <div class="alert alert-info mt-3 text-blue-400">
                    Vui lòng quay lại sau thời gian bắt đầu để tham gia.
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('khao-sat.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left "></i> Quay về danh sách khảo sát
                </a>
            </div>
        </div>
    </div>
@endsection