@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Header với tiêu đề và thống kê -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Quản lý Sao lưu CSDL</h2>
                <p class="text-muted mb-0">Tạo, tải lên và khôi phục bản sao lưu database</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Tổng số backup: <strong>{{ count($files) }}</strong></small>
                @if($oldBackupsCount > 0)
                    <br>
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        {{ $oldBackupsCount }} backup cũ hơn 30 ngày
                        ({{ number_format($oldBackupsSize / (1024 * 1024), 1) }} MB)
                    </small>
                @endif
            </div>
        </div>

        <!-- Cards cho các chức năng -->
        <div class="row g-4 mb-4">
            <!-- Tạo backup -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Tạo Backup Mới</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.dbbackups.create') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên file (tuỳ chọn)</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Ví dụ: backup_thang12">
                                <div class="form-text">Để trống sẽ tự động tạo tên theo thời gian</div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="gzip" name="gzip" value="1" checked>
                                    <label class="form-check-label" for="gzip">
                                        <i class="fas fa-compress me-1"></i>Nén file (Gzip)
                                    </label>
                                </div>
                                <div class="form-text">Giảm kích thước file nhưng tăng thời gian tạo</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Tạo Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tải lên backup -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Tải lên Backup</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.dbbackups.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="backup" class="form-label">Chọn file backup</label>
                                <input type="file" name="backup" id="backup" accept=".sql,.gz" class="form-control"
                                    required>
                                <div class="form-text">Chấp nhận file .sql hoặc .sql.gz</div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="upload_restore" name="restore"
                                        value="1">
                                    <label class="form-check-label" for="upload_restore">
                                        <i class="fas fa-redo me-1"></i>Khôi phục ngay sau khi tải lên
                                    </label>
                                </div>
                                <div class="form-text">Tự động restore database sau khi upload thành công</div>
                            </div>
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Tải lên Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cleanup backup cũ -->
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-broom me-2"></i>Dọn dẹp Backup Cũ</h5>
                    </div>
                    <div class="card-body">
                        @if($oldBackupsCount > 0)
                            <div class="alert alert-warning alert-sm mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Có <strong>{{ $oldBackupsCount }}</strong> backup cũ hơn 30 ngày
                                ({{ number_format($oldBackupsSize / (1024 * 1024), 1) }} MB)
                            </div>
                        @else
                            <div class="alert alert-success alert-sm mb-3">
                                <i class="fas fa-check-circle me-1"></i>
                                Không có backup cũ nào để dọn dẹp
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.dbbackups.cleanup') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="days" class="form-label">Giữ lại backup trong (ngày)</label>
                                <select name="days" id="days" class="form-select">
                                    <option value="7">7 ngày</option>
                                    <option value="15">15 ngày</option>
                                    <option value="30" selected>30 ngày</option>
                                    <option value="60">60 ngày</option>
                                    <option value="90">90 ngày</option>
                                </select>
                                <div class="form-text">Backup cũ hơn sẽ bị xóa</div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm_cleanup" name="confirm"
                                        value="1" required>
                                    <label class="form-check-label" for="confirm_cleanup">
                                        Tôi hiểu rằng hành động này không thể hoàn tác
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning w-100" @if($oldBackupsCount == 0) disabled @endif>
                                <i class="fas fa-trash-alt me-2"></i>Dọn dẹp Backup Cũ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách backup -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách Backup</h5>
            </div>
            <div class="card-body p-0">
                @if(count($files) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0"><i class="fas fa-file me-1"></i>File</th>
                                    <th class="border-0"><i class="fas fa-weight me-1"></i>Kích thước</th>
                                    <th class="border-0"><i class="fas fa-clock me-1"></i>Thời gian</th>
                                    <th class="border-0 text-end"><i class="fas fa-cogs me-1"></i>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($files as $f)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if(str_ends_with(strtolower($f['name']), '.gz'))
                                                    <i class="fas fa-file-archive text-warning me-2"></i>
                                                @else
                                                    <i class="fas fa-file-code text-info me-2"></i>
                                                @endif
                                                <span class="fw-medium">{{ $f['name'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ number_format($f['size'] / 1024, 1) }} KB
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::createFromTimestamp($f['time'])->format('d/m/Y H:i:s') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-outline-primary btn-sm"
                                                    href="{{ route('admin.dbbackups.download', $f['name']) }}" title="Tải xuống">
                                                    Tải xuống
                                                </a>

                                                <button type="button" class="btn btn-outline-success btn-sm" title="Khôi phục"
                                                    onclick="if(confirm('Khôi phục từ {{ $f['name'] }}? DỮ LIỆU HIỆN TẠI SẼ BỊ GHI ĐÈ!')){ document.getElementById('restore-form-{{ md5($f['name']) }}').submit(); }">
                                                    Khôi phục
                                                </button>
                                                <form id="restore-form-{{ md5($f['name']) }}" method="POST"
                                                    action="{{ route('admin.dbbackups.restore') }}" class="d-none">
                                                    @csrf
                                                    <input type="hidden" name="file" value="{{ $f['name'] }}">
                                                    <input type="hidden" name="force" value="1">
                                                </form>

                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Xóa"
                                                    onclick="if(confirm('Xóa backup {{ $f['name'] }}?')){ document.getElementById('delete-form-{{ md5($f['name']) }}').submit(); }">
                                                    Xóa
                                                </button>
                                                <form id="delete-form-{{ md5($f['name']) }}" method="POST"
                                                    action="{{ route('admin.dbbackups.destroy', $f['name']) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-database text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Chưa có bản backup nào</h5>
                        <p class="text-muted">Tạo backup đầu tiên để bảo vệ dữ liệu của bạn</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .card {
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
@endsection