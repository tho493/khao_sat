@extends('layouts.admin')@section('title', 'Log hệ thống')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Log hệ thống</h1>
            <div>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Log hoạt động
                </a>
                <button class="btn btn-danger" onclick="showClearSystemLogModal()">
                    <i class="bi bi-trash"></i> Xóa log cũ
                </button>
            </div>
        </div>

        <!-- File selector -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.logs.system') }}" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Chọn file log</label>
                        <select class="form-select" name="file" onchange="this.form.submit()">
                            @foreach($logFiles as $file)
                                <option value="{{ $file }}" {{ $selectedFile == $file ? 'selected' : '' }}>
                                    {{ $file }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thông tin file</label>
                        <div class="text-muted">
                            @if(isset($fileInfo))
                                Kích thước: {{ number_format($fileInfo['size'] / 1024, 2) }} KB |
                                Cập nhật: {{ date('d/m/Y H:i', $fileInfo['modified']) }}
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- Log content -->
        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nội dung log: {{ $selectedFile }}</h5>
                    <div>
                        <button class="btn btn-sm btn-info" onclick="refreshLog()">
                            <i class="bi bi-arrow-clockwise"></i> Làm mới
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="downloadLog()">
                            <i class="bi bi-download"></i> Tải xuống
                        </button>
                    </div>
                </div>
            </div>

            @if(isset($error) && $error)
                <div class="alert alert-danger">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Lỗi đọc file log!</h5>
                    <p>{{ $error }}</p>
                    <hr>
                    <p class="mb-0">Vui lòng kiểm tra lại quyền truy cập của thư mục `storage/logs` và các file bên trong.</p>
                </div>
            @endif

            <div class="card-body">
                <div class="log-viewer">
                    @if(empty($logs) && empty($error))
                        <div class="text-center py-4">
                            <i class="bi bi-file-text fs-1 text-muted"></i>
                            <p class="text-muted">File log trống hoặc không thể đọc</p>
                        </div>
                    @elseif(!empty($logs))
                        <div class="table-responsive">
                            <table class="table table-sm table-hover log-table">
                                <thead>
                                    <tr>
                                        <th width="150">Thời gian</th>
                                        <th width="100">Level</th>
                                        <th>Message</th>
                                        <th width="80">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                                        <tr class="log-{{ strtolower($log['level'] ?? 'info') }}">
                                                            <td>
                                                                <small>{{ $log['timestamp'] ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge bg-{{ 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        $log['level'] == 'ERROR' ? 'danger' :
                                        ($log['level'] == 'WARNING' ? 'warning' :
                                            ($log['level'] == 'INFO' ? 'info' :
                                                ($log['level'] == 'DEBUG' ? 'secondary' : 'primary'))) 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    }}">
                                                                    {{ $log['level'] ?? 'INFO' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="log-message">
                                                                    {{ Str::limit($log['message'] ?? '', 100) }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if(!empty($log['context']) || strlen($log['message'] ?? '') > 100)
                                                                    <button class="btn btn-sm btn-outline-info" onclick="showLogContext({{ $index }})">
                                                                        <i class="bi bi-eye"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal chi tiết log -->
    <div class="modal fade" id="logContextModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logContextContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xóa system log -->
    <div class="modal fade" id="clearSystemLogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.logs.clear') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Xóa log hệ thống cũ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="type" value="system">
                        <div class="mb-3">
                            <label class="form-label">Xóa file log trước ngày</label>
                            <input type="date" class="form-control" name="before_date"
                                max="{{ date('Y-m-d', strtotime('-1 day')) }}" required>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Cảnh báo:</strong> Thao tác này sẽ xóa vĩnh viễn các file log!
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

    <!-- Hidden log data for modal -->
    <script type="text/javascript">
        const logData = @json($logs);
    </script>
@endsection

@push('styles')
    <style>
        .log-viewer {
            max-height: 600px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .log-table {
            font-size: 0.875rem;
        }

        .log-message {
            word-break: break-word;
            white-space: pre-wrap;
        }

        .log-error {
            background-color: #fee;
        }

        .log-warning {
            background-color: #ffeaa7;
        }

        .log-info {
            background-color: #e3f2fd;
        }

        .log-debug {
            background-color: #f5f5f5;
        }

        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function showLogContext(index) {
            const log = logData[index];
            let content = `
                                                                    <table class="table table-sm">
                                                                        <tr>
                                                                            <td width="20%"><strong>Thời gian:</strong></td>
                                                                            <td>${log.timestamp || 'N/A'}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Level:</strong></td>
                                                                            <td><span class="badge bg-${getLogLevelClass(log.level)}">${log.level || 'INFO'}</span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Environment:</strong></td>
                                                                            <td>${log.environment || 'N/A'}</td>
                                                                        </tr>
                                                                    </table>

                                                                    <h6 class="mt-3">Message:</h6>
                                                                    <pre>${log.message || ''}</pre>
                                                                `;

            if (log.context) {
                content += `
                                                                        <h6 class="mt-3">Context:</h6>
                                                                        <pre>${log.context}</pre>
                                                                    `;
            }

            $('#logContextContent').html(content);
            $('#logContextModal').modal('show');
        }

        function getLogLevelClass(level) {
            switch (level) {
                case 'ERROR': return 'danger';
                case 'WARNING': return 'warning';
                case 'INFO': return 'info';
                case 'DEBUG': return 'secondary';
                default: return 'primary';
            }
        }

        function refreshLog() {
            location.reload();
        }

        function downloadLog() {
            const file = '{{ $selectedFile }}';
            window.location.href = `/admin/logs/download?file=${file}`;
        }

        function showClearSystemLogModal() {
            $('#clearSystemLogModal').modal('show');
        }
    </script>
@endpush