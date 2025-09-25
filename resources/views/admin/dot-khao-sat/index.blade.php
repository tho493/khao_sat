@extends('layouts.admin')

@section('title', 'Quản lý đợt khảo sát')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Quản lý đợt khảo sát</h1>
        <a href="{{ route('admin.dot-khao-sat.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tạo đợt mới
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dot-khao-sat.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tìm kiếm theo tên đợt..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trangthai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="draft" {{ request('trangthai') == 'draft' ? 'selected' : '' }}>Nháp</option>
                        <option value="active" {{ request('trangthai') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="closed" {{ request('trangthai') == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="namhoc_id">
                        <option value="">-- Tất cả năm học --</option>
                        @foreach($namHocs as $nh)
                            <option value="{{ $nh->id }}" {{ request('namhoc_id') == $nh->id ? 'selected' : '' }}>
                                {{ $nh->namhoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đợt</th>
                            <th>Mẫu khảo sát</th>
                            <th>Thời gian</th>
                            <th>Tiến độ</th>
                            <th>Trạng thái</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dotKhaoSats as $dot)
                            <tr>
                                <td>{{ $dot->id }}</td>
                                <td>
                                    <strong>{{ $dot->ten_dot }}</strong>
                                    @if($dot->mota)
                                        <br><small class="text-muted">{{ Str::limit($dot->mota, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $dot->mauKhaoSat->ten_mau ?? 'N/A' }}</td>
                                <td>
                                    <small>
                                        {{ $dot->tungay }} - 
                                        {{ $dot->denngay }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $dot->phieu_hoan_thanh ?? 0 }} phiếu
                                    </small>
                                </td>
                                <td>
                                    @switch($dot->trangthai)
                                        @case('active')
                                            <span class="badge bg-success">Đang hoạt động</span>
                                            @break
                                        @case('draft')
                                            <span class="badge bg-warning">Nháp</span>
                                            @break
                                        @case('closed')
                                            <!-- <span class="badge bg-secondary">Đã đóng</span> -->
                                             @php
                                                $isClosedEarly = now()->lt($dot->denngay);
                                            @endphp
                                            
                                            @if($isClosedEarly)
                                                <span class="badge bg-danger">Dừng sớm</span>
                                            @else
                                                <span class="badge bg-secondary">Đã kết thúc</span>
                                            @endif
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('admin.dot-khao-sat.show', $dot) }}" 
                                       class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @if($dot->trangthai == 'draft')
                                        <form action="{{ route('admin.dot-khao-sat.activate', $dot) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                    title="Kích hoạt">
                                                <i class="bi bi-play"></i>
                                            </button>
                                        </form>
                                    @elseif($dot->trangthai == 'active')
                                        <form action="{{ route('admin.dot-khao-sat.close', $dot) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Đóng" onclick="return confirm('Bạn có chắc chắn muốn đóng đợt khảo sát này?')">
                                                <i class="bi bi-stop"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.dot-khao-sat.edit', $dot) }}" 
                                       class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted">Không có dữ liệu</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                @if ($dotKhaoSats->hasPages())
                    {{ $dotKhaoSats->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection