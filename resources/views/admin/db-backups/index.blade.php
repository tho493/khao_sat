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
                <p class="text-muted mb-0">Các bản backup chỉ được lưu trữ tối đa 60 ngày</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Tổng số backup: <strong>{{ count($files) }}</strong></small>
                <br>
                <small id="header-old-backups-info" class="text-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <span id="header-old-backups-text"></span>
                </small>
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
                        <div id="cleanup-alert" class="alert alert-warning alert-sm mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="cleanup-message">Đang tính toán...</span>
                        </div>

                        <form method="POST" action="{{ route('admin.dbbackups.cleanup') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="days" class="form-label">Giữ lại backup trong (ngày)</label>
                                <select name="days" id="days" class="form-select">
                                    <option value="7">7 ngày</option>
                                    <option value="15">15 ngày</option>
                                    <option value="30" selected>30 ngày</option>
                                    <option value="60">60 ngày</option>
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
                            <button type="submit" id="cleanup-submit-btn" class="btn btn-warning w-100">
                                <i class="fas fa-trash-alt me-2"></i>Dọn dẹp Backup Cũ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách backup -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách Backup</h5>
                @if(count($files) > 0)
                    <div id="bulk-actions-toolbar" style="display: none;">
                        <button type="button" class="btn btn-danger btn-sm" id="bulk-delete-btn">
                            <i class="fas fa-trash-alt me-1"></i>Xóa đã chọn (<span id="selected-count">0</span>)
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body p-0">
                @if(count($files) > 0)
                    <div class="table-responsive">
                        <form id="bulk-delete-form" method="POST" action="{{ route('admin.dbbackups.bulk-delete') }}">
                            @csrf
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0" style="width: 40px;">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
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
                                                <input type="checkbox" name="files[]" value="{{ $f['name'] }}"
                                                    class="form-check-input backup-checkbox">
                                            </td>
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
                                                        href="{{ route('admin.dbbackups.download', $f['name']) }}"
                                                        title="Tải xuống">
                                                        Tải xuống
                                                    </a>

                                                    <button type="button" class="btn btn-outline-success btn-sm restore-btn"
                                                        title="Khôi phục" data-file-name="{{ $f['name'] }}">
                                                        Khôi phục
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn"
                                                        title="Xóa" data-file-name="{{ $f['name'] }}">
                                                        Xóa
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backupFiles = @json($files);
            const currentTime = Math.floor(Date.now() / 1000); // Timestamp hiện tại (giây)

            const daysSelect = document.getElementById('days');
            const cleanupAlert = document.getElementById('cleanup-alert');
            const cleanupMessage = document.getElementById('cleanup-message');
            const submitBtn = document.getElementById('cleanup-submit-btn');
            const confirmCheckbox = document.getElementById('confirm_cleanup');
            const headerOldBackupsInfo = document.getElementById('header-old-backups-info');
            const headerOldBackupsText = document.getElementById('header-old-backups-text');

            function calculateOldBackups(days) {
                const cutoffTime = currentTime - (days * 24 * 60 * 60);

                const oldFiles = backupFiles.filter(file => file.time < cutoffTime);
                const count = oldFiles.length;
                const totalSize = oldFiles.reduce((sum, file) => sum + file.size, 0);

                return { count, totalSize };
            }

            function updateCleanupInfo() {
                const selectedDays = parseInt(daysSelect.value);
                const { count, totalSize } = calculateOldBackups(selectedDays);

                if (count > 0) {
                    const sizeMB = (totalSize / (1024 * 1024)).toFixed(1);

                    // Cập nhật card cleanup
                    cleanupAlert.className = 'alert alert-warning alert-sm mb-3';
                    cleanupAlert.querySelector('i').className = 'fas fa-info-circle me-1';
                    cleanupMessage.innerHTML = `Có <strong>${count}</strong> backup cũ hơn <strong>${selectedDays}</strong> ngày (${sizeMB} MB)`;
                    submitBtn.disabled = false;

                    // Cập nhật header
                    headerOldBackupsInfo.style.display = 'inline';
                    headerOldBackupsText.textContent = `${count} backup cũ hơn ${selectedDays} ngày (${sizeMB} MB)`;
                } else {
                    // Cập nhật card cleanup
                    cleanupAlert.className = 'alert alert-success alert-sm mb-3';
                    cleanupAlert.querySelector('i').className = 'fas fa-check-circle me-1';
                    cleanupMessage.innerHTML = `Không có backup cũ hơn <strong>${selectedDays}</strong> ngày`;
                    submitBtn.disabled = true;
                    confirmCheckbox.checked = false;

                    // Ẩn thông báo ở header
                    headerOldBackupsInfo.style.display = 'none';
                }
            }

            // Lắng nghe sự kiện thay đổi
            daysSelect.addEventListener('change', updateCleanupInfo);

            // Cập nhật lần đầu
            updateCleanupInfo();
        });

        // Bulk Delete Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all');
            const backupCheckboxes = document.querySelectorAll('.backup-checkbox');
            const bulkActionsToolbar = document.getElementById('bulk-actions-toolbar');
            const selectedCountSpan = document.getElementById('selected-count');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const bulkDeleteForm = document.getElementById('bulk-delete-form');

            if (!selectAllCheckbox) return;

            function updateBulkActionsUI() {
                const checkedCount = document.querySelectorAll('.backup-checkbox:checked').length;
                selectedCountSpan.textContent = checkedCount;

                if (checkedCount > 0) {
                    bulkActionsToolbar.style.display = 'block';
                } else {
                    bulkActionsToolbar.style.display = 'none';
                }
            }

            // Select All checkbox
            selectAllCheckbox.addEventListener('change', function () {
                backupCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsUI();
            });

            // Individual checkboxes
            backupCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const allChecked = Array.from(backupCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(backupCheckboxes).some(cb => cb.checked);

                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;

                    updateBulkActionsUI();
                });
            });

            // Bulk Delete Button
            bulkDeleteBtn.addEventListener('click', function () {
                const checkedCount = document.querySelectorAll('.backup-checkbox:checked').length;

                if (checkedCount === 0) {
                    alert('Vui lòng chọn ít nhất một backup để xóa.');
                    return;
                }

                const confirmMessage = `Bạn có chắc chắn muốn xóa ${checkedCount} backup đã chọn?\n\nHành động này không thể hoàn tác!`;

                if (confirm(confirmMessage)) {
                    bulkDeleteForm.submit();
                }
            });
        });
    </script>
    <script>
        // Restore and Delete Button Handlers
        document.addEventListener('DOMContentLoaded', function () {
            // Restore button handlers
            document.querySelectorAll('.restore-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const fileName = this.getAttribute('data-file-name');

                    if (confirm(`Khôi phục từ ${fileName}? DỮ LIỆU HIỆN TẠI SẼ BỊ GHI ĐÈ!`)) {
                        // Create form dynamically
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('admin.dbbackups.restore') }}';

                        // Add CSRF token
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        // Add file input
                        const fileInput = document.createElement('input');
                        fileInput.type = 'hidden';
                        fileInput.name = 'file';
                        fileInput.value = fileName;
                        form.appendChild(fileInput);

                        // Add force input
                        const forceInput = document.createElement('input');
                        forceInput.type = 'hidden';
                        forceInput.name = 'force';
                        forceInput.value = '1';
                        form.appendChild(forceInput);

                        // Append to body and submit
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Delete button handlers
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const fileName = this.getAttribute('data-file-name');

                    if (confirm(`Xóa backup ${fileName}?`)) {
                        // Create form dynamically
                        // Fix: Create a dummy route first to avoid "Missing required parameter" error
                        // Then replace the dummy value in JS
                        let infoUrl = '{{ route('admin.dbbackups.destroy', ['file' => 'PLACEHOLDER']) }}';
                        const actionUrl = infoUrl.replace('PLACEHOLDER', encodeURIComponent(fileName));

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = actionUrl;

                        // Add CSRF token and method spoofing...
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection