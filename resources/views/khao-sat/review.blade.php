@extends('layouts.home')
@section('title', 'Xem lại đáp án khảo sát')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Chi tiết câu trả lời</h2>
                        <p class="text-muted mb-0">Đợt khảo sát:
                            <strong>{{ $data['phieu_info']['ten_dot'] ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    <button class="btn btn-light border" onclick="window.print()">
                        <i class="bi bi-printer"></i> In trang này
                    </button>
                </div>

                <!-- Thông tin người trả lời -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin của bạn</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><small class="text-muted">Mã định danh:</small></p>
                                <p><strong>{{ $data['phieu_info']['ma_nguoi_traloi'] ?? 'N/A' }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><small class="text-muted">Họ tên:</small></p>
                                <p><strong>{{ $data['phieu_info']['hoten'] ?? 'N/A' }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><small class="text-muted">Đơn vị:</small></p>
                                <p><strong>{{ $data['phieu_info']['donvi'] ?? 'N/A' }}</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><small class="text-muted">Thời gian nộp:</small></p>
                                <p><strong>{{ $data['phieu_info']['thoi_gian_nop'] ?? 'N/A' }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách câu hỏi và câu trả lời -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Nội dung đã trả lời</h5>
                    </div>
                    <div class="card-body">
                        @forelse($data['answers'] as $index => $answer)
                            <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <p class="fw-bold mb-2">Câu {{ $index + 1 }}: {{ $answer['cau_hoi'] }}</p>
                                <div class="ps-3">
                                    <p class="text-primary fst-italic mb-0">
                                        <i class="bi bi-chat-right-quote me-2"></i>
                                        {{ $answer['cau_tra_loi'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">Không có dữ liệu câu trả lời.</p>
                        @endforelse
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('khao-sat.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection