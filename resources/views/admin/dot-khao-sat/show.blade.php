@extends('layouts.admin')

@section('title', 'Chi tiết đợt khảo sát')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dot-khao-sat.index') }}">Đợt khảo sát</a></li>
            <li class="breadcrumb-item active">{{ $dotKhaoSat->ten_dot }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Thông tin đợt -->
            <div class="card shadow mb-4">
                <div class="row">
                        <div class="card-body col-lg-8">
                        <h5 class="mb-0 card-header">Thông tin đợt khảo sát</h5>
                        <table class="table table-sm">
                            <tr>
                                <td width="30%"><strong>Tên đợt:</strong></td>
                                <td>{{ $dotKhaoSat->ten_dot }}</td>
                            </tr>
                            <tr>
                                <td><strong>Mẫu khảo sát:</strong></td>
                                <td>{{ $dotKhaoSat->mauKhaoSat->ten_mau ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Năm học:</strong></td>
                                <td>{{ $dotKhaoSat->namHoc->namhoc ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Thời gian:</strong></td>
                                <td>
                                {{ $dotKhaoSat->tungay->format('H:i d/m/Y') }} - 
                                {{ $dotKhaoSat->denngay->format('H:i d/m/Y') }}
                                    @if($dotKhaoSat->trangthai == 'active')
                                        <span class="badge bg-warning ms-2">{{ $dotKhaoSat->denngay->diffForHumans(now(), null, true, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Trạng thái:</strong></td>
                                <td>
                                    @switch($dotKhaoSat->trangthai)
                                        @case('active')
                                            <span class="badge bg-success">Đang hoạt động</span>
                                            @break
                                        @case('draft')
                                            <span class="badge bg-warning">Nháp</span>
                                            @break
                                        @case('closed')
                                             @php
                                                $isClosedEarly = now()->lt($dotKhaoSat->denngay);
                                            @endphp
                                            
                                            @if($isClosedEarly)
                                                <span class="badge bg-danger">Dừng sớm</span>
                                            @else
                                                <span class="badge bg-secondary">Đã kết thúc</span>
                                            @endif
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                            @if($dotKhaoSat->mota)
                                <tr>
                                    <td><strong>Mô tả:</strong></td>
                                    <td>{{ $dotKhaoSat->mota }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    @if(isset($dotKhaoSat) && $dotKhaoSat->image_url)
                        <div class="col-lg-4">
                            <h5 class="mb-0 card-header">Ảnh hiện tại:</h5>
                            <div>
                                <img src="{{ $dotKhaoSat->image }}" alt="Ảnh đại diện" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Thao tác -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="card-title">Thao tác</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.dot-khao-sat.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại danh sách
                        </a>

                        @if($dotKhaoSat->trangthai == 'draft')
                            <form action="{{ route('admin.dot-khao-sat.activate', $dotKhaoSat) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play"></i> Kích hoạt đợt khảo sát
                                </button>
                            </form>
                        @elseif($dotKhaoSat->trangthai == 'active')
                            <a href="{{ route('khao-sat.show', $dotKhaoSat) }}" 
                               class="btn btn-primary" target="_blank">
                                <i class="bi bi-link-45deg"></i> Xem form khảo sát
                            </a>
                            
                            <button class="btn btn-info" onclick="copyLink()">
                                <i class="bi bi-clipboard"></i> Copy link khảo sát
                            </button>

                            <form action="{{ route('admin.dot-khao-sat.close', $dotKhaoSat) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Bạn có chắc chắn muốn đóng đợt khảo sát này?')">
                                    <i class="bi bi-stop"></i> Đóng đợt khảo sát
                                </button>
                            </form>
                        @endif

                        @if($thongKe['tong_phieu'] > 0)
                            <a href="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}" 
                               class="btn btn-info">
                                <i class="bi bi-graph-up"></i> Xem báo cáo
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Link khảo sát -->
            @if($dotKhaoSat->trangthai == 'active')
                <div class="card shadow">
                    <div class="card-body">
                        <h6 class="card-title">Link khảo sát</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" id="surveyLink" 
                                   value="{{ route('khao-sat.show', $dotKhaoSat) }}" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyLink()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Chia sẻ link này cho người tham gia khảo sát</small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Thống kê theo câu hỏi custom_select (thông tin cá nhân) -->
    @if(!empty($thongKeCustomSelect) && count($thongKeCustomSelect) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thống kê theo thông tin cá nhân (Custom Select)</h5>
                    </div>
                    <div class="card-body">
                        @foreach($thongKeCustomSelect as $stats)
                            <div class="mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <h6 class="mb-3">
                                    <i class="bi bi-question-circle"></i> {{ $stats['cau_hoi']->noidung_cauhoi }}
                                    <span class="badge bg-secondary ms-2">Tổng: {{ $stats['total'] }} phiếu</span>
                                </h6>
                                
                                @if($stats['total'] > 0 && count($stats['data']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%">STT</th>
                                                    <th width="50%">Giá trị</th>
                                                    <th width="20%" class="text-center">Số lượng</th>
                                                    <th width="25%" class="text-center">Tỷ lệ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stats['data'] as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $item->label }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-info">{{ $item->so_luong }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: {{ $item->ty_le }}%"
                                                                     aria-valuenow="{{ $item->ty_le }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                    {{ $item->ty_le }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle"></i> Chưa có dữ liệu trả lời cho câu hỏi này.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function copyLink() {
    const input = document.getElementById('surveyLink');
    input.select();
    document.execCommand('copy');
    alert('success', 'Thành công', 'Đã copy link khảo sát!');
}
</script>
@endsection