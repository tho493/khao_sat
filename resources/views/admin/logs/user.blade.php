@extends('layouts.admin')

@section('title', 'Nhật ký hoạt động của tôi')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Nhật ký hoạt động của tôi</h1>
            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Tất cả log
            </a>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Tổng hoạt động</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-success">{{ $stats['create'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Tạo mới</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-info">{{ $stats['update'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Cập nhật</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-danger">{{ $stats['delete'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Xóa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Hành động</th>
                                <th>Bảng</th>
                                <th>ID</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                                <tr>
                                                    <td>{{ $log->thoigian ? $log->thoigian->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ 
                                                                                                                                                                                        $log->hanhdong == 'create' ? 'success' :
                                ($log->hanhdong == 'update' ? 'info' :
                                    ($log->hanhdong == 'delete' ? 'danger' : 'secondary')) 
                                                                                                                                                                                    }}">
                                                            {{ ucfirst($log->hanhdong) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $log->bang_thaydoi }}</td>
                                                    <td>{{ $log->id_banghi }}</td>
                                                    <td>{{ $log->ghi_chu }}</td>
                                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted">Chưa có hoạt động nào</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @if ($logs->hasPages())
                        {{ $logs->withQueryString()->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection