@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Quản lý người dùng</h1>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm người dùng
            </a>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="trangthai">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="1" {{ request('trangthai') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ request('trangthai') == '0' ? 'selected' : '' }}>Khóa</option>
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

        <!-- Users Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Trạng thái</th>
                                <th>Đăng nhập cuối</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->tendangnhap }}</td>
                                    <td>{{ $user->hoten }}</td>
                                    <td>{{ $user->email ?? 'Không có' }}</td>
                                    <td>{{ $user->sodienthoai ?? 'Không có' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->trangthai ? 'success' : 'secondary' }}">
                                            {{ $user->trangthai ? 'Hoạt động' : 'Khóa' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d/m/Y H:i') : 'Chưa từng đăng nhập' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary"
                                                title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    {{ $users->withQueryString()->links() }}
                @endif
                {{-- $users->links() --}}
            </div>
        </div>
    </div>
@endsection