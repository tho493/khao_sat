@extends('layouts.admin')

@section('title', 'Nhật ký hệ thống')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Nhật ký hoạt động</h1>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" name="bang_thaydoi">
                            <option value="">-- Tất cả bảng --</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}" {{ request('bang_thaydoi') == $table ? 'selected' : '' }}>
                                    {{ $table }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="hanhdong">
                            <option value="">-- Hành động --</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('hanhdong') == $action ? 'selected' : '' }}>
                                    {{ ucfirst($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="nguoi_thuchien_id">
                            <option value="">-- Người thực hiện --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('nguoi_thuchien_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->hoten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}"
                            placeholder="Từ ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Người thực hiện</th>
                                <th>Hành động</th>
                                <th>Bảng</th>
                                <th>ID bảng</th>
                                <th>Ghi chú</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                                <tr>
                                                    <td>{{ $log->id }}</td>
                                                    <td>{{ $log->thoigian->format('d/m/Y H:i:s') }}</td>
                                                    <td>{{ $log->nguoiThucHien->hoten ?? 'N/A' }}</td>
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
                                                    <td align="center">{{ $log->id_banghi }}</td>
                                                    <td>{{ $log->ghi_chu }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info" onclick="showLogDetail({{ $log->id }})">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    {{ $logs->withQueryString()->links() }}
                @endif
            </div>
        </div>

        <!-- Clear logs -->
        <div class="mt-4">
            <button class="btn btn-danger" onclick="showClearModal()">
                <i class="bi bi-trash"></i> Xóa log cũ
            </button>
            <a href="{{ route('admin.logs.system') }}" class="btn btn-info">
                <i class="bi bi-file-text"></i> Log hệ thống
            </a>
            <a href="{{ route('admin.logs.user') }}" class="btn btn-primary">
                <i class="bi bi-file-text"></i> Log người dùng
            </a>
        </div>
    </div>

    <!-- Modal chi tiết log -->
    <div class="modal fade" id="logDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logDetailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xóa log -->
    <div class="modal fade" id="clearLogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.logs.clear') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Xóa log cũ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="type" value="activity">
                        <div class="mb-3">
                            <label class="form-label">Xóa log trước ngày</label>
                            <input type="date" class="form-control" name="before_date"
                                max="{{ date('Y-m-d', strtotime('-1 day')) }}" required>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Thao tác này không thể hoàn tác!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showLogDetail(logId) {
            // Hiển thị loading hoặc xóa nội dung cũ
            document.getElementById('logDetailContent').innerHTML = '<div class="text-center py-3"><div class="spinner-border"></div></div>';

            // Gọi AJAX để lấy chi tiết log
            fetch(`{{ route('admin.logs.show', ':id') }}`.replace(':id', logId))
                .then(response => {
                    if (!response.ok) throw new Error('Không thể tải chi tiết log');
                    return response.json();
                })
                .then(data => {
                    let html = `
                                <table class="table table-sm">
                                    <tr>
                                        <td width="20%"><strong>Thời gian:</strong></td>
                                        <td>${data.thoigian || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Người thực hiện:</strong></td>
                                        <td>${data.nguoi_thuchien || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hành động:</strong></td>
                                        <td>${data.hanhdong || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bảng thay đổi:</strong></td>
                                        <td>${data.bang_thaydoi || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ID bản ghi:</strong></td>
                                        <td>${data.id_banghi || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ghi chú:</strong></td>
                                        <td>${data.ghi_chu || ''}</td>
                                    </tr>
                                </table>
                                <h6 class="mt-3">Nội dung cũ:</h6>
                                <pre>${typeof data.noidung_cu === 'object' ? JSON.stringify(data.noidung_cu, null, 2) : (data.noidung_cu || '')}</pre>
                                <h6 class="mt-3">Nội dung mới:</h6>
                                <pre>${typeof data.noidung_moi === 'object' ? JSON.stringify(data.noidung_moi, null, 2) : (data.noidung_moi || '')}</pre>
                            `;
                    document.getElementById('logDetailContent').innerHTML = html;
                })
                .catch(err => {
                    document.getElementById('logDetailContent').innerHTML = '<div class="alert alert-danger">Không thể tải chi tiết log.</div>';
                });

            const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
            modal.show();
        }

        function showClearModal() {
            const modal = new bootstrap.Modal(document.getElementById('clearLogModal'));
            modal.show();
        }
    </script>
@endpush