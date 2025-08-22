@extends('layouts.admin')

@section('title', 'Thêm người dùng')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Quản lý người dùng</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Thêm người dùng mới</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tendangnhap') is-invalid @enderror"
                                    name="tendangnhap" value="{{ old('tendangnhap') }}" placeholder="vidu: nguyenvana"
                                    required autofocus>
                                @error('tendangnhap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Chỉ chứa chữ cái, số và dấu gạch dưới</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('matkhau') is-invalid @enderror"
                                        name="matkhau" id="matkhau" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                    @error('matkhau')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('hoten') is-invalid @enderror" name="hoten"
                                    value="{{ old('hoten') }}" placeholder="vidu: Nguyễn Văn A" required>
                                @error('hoten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-end">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h6 class="card-title">Lưu ý</h6>
                        <ul class="mb-0">
                            <li>Tên đăng nhập không thể thay đổi sau khi tạo</li>
                            <li>Mật khẩu sẽ được mã hóa khi lưu</li>
                            <li>Tất cả người dùng đều có quyền admin</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('matkhau');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
@endpush