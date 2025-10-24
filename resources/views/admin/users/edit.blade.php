@extends('layouts.admin')

@section('title', 'Sửa người dùng')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Quản lý người dùng</a></li>
                <li class="breadcrumb-item active">Sửa: {{ $user->hoten }}</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Sửa thông tin người dùng</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" value="{{ $user->tendangnhap }}" disabled>
                                <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('hoten') is-invalid @enderror" name="hoten"
                                    value="{{ old('hoten', $user->hoten) }}" required>
                                @error('hoten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">

                                <label class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control @error('sodienthoai') is-invalid @enderror"
                                    name="sodienthoai" value="{{ old('sodienthoai', $user->sodienthoai) }}">
                                @error('sodienthoai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">

                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-control @error('trangthai') is-invalid @enderror" name="trangthai"
                                    required>
                                    <option value=1 {{ old('trangthai', $user->trangthai) == 1 ? 'selected' : '' }}>
                                        Hoạt động</option>
                                    <option value=0 {{ old('trangthai', $user->trangthai) == 0 ? 'selected' : '' }}>
                                        Khóa</option>
                                </select>
                                @error('trangthai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Đổi mật khẩu</h6>
                            <p class="text-muted small">Để trống nếu không muốn đổi mật khẩu</p>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('matkhau') is-invalid @enderror"
                                        name="matkhau" id="matkhau">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                    @error('matkhau')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h6 class="card-title">Thông tin</h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Tên đăng nhập:</td>
                                <td>{{ $user->tendangnhap }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Họ tên:</td>
                                <td>{{ $user->hoten }}</td>
                            </tr>
                        </table>

                        @if($user->tendangnhap === auth()->user()->tendangnhap)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Đây là tài khoản của bạn
                            </div>
                        @endif
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