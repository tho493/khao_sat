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

            <!-- Thống kê -->
            <!-- <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê phản hồi</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="mb-0">{{ $thongKe['tong_phieu'] ?? 0 }}</h3>
                            <p class="text-muted">Tổng số phiếu</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="mb-0 text-success">{{ $thongKe['phieu_hoan_thanh'] ?? 0 }}</h3>
                            <p class="text-muted">Hoàn thành</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="mb-0 text-info">{{ $thongKe['ty_le'] ?? 0 }}%</h3>
                            <p class="text-muted">Tỷ lệ hoàn thành</p>
                        </div>
                    </div>

                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $thongKe['ty_le'] ?? 0 }}%">
                            {{ $thongKe['ty_le'] ?? 0 }}%
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Thống kê theo đơn vị -->
            @if(!empty($thongKeTheoDonVi))
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Thống kê theo đơn vị</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Đơn vị</th>
                                        <th>Tổng phiếu</th>
                                        <th>Hoàn thành</th>
                                        <th>Tỷ lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($thongKeTheoDonVi as $item)
                                        <tr>
                                            <td>{{ $item->donvi ?? 'Không xác định' }}</td>
                                            <td>{{ $item->tong_phieu ?? 0 }}</td>
                                            <td>{{ $item->phieu_hoanthanh ?? 0 }}</td>
                                            <td>{{ $item->ty_le ?? 0 }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
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
</div>

<script>
function copyLink() {
    const input = document.getElementById('surveyLink');
    input.select();
    document.execCommand('copy');
    alert('Đã copy link khảo sát!');
}
</script>
@endsection