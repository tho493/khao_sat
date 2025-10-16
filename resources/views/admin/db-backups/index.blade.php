@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Sao lưu CSDL</h3>
            <form method="POST" action="{{ route('admin.dbbackups.create') }}" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Tên file (tuỳ chọn)">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="gzip" name="gzip" value="1" checked>
                    <label class="form-check-label" for="gzip">Gzip</label>
                </div>
                <button class="btn btn-primary btn-sm">Tạo backup</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">Danh sách backup</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Kích thước</th>
                            <th>Thời gian</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($files as $f)
                            <tr>
                                <td>{{ $f['name'] }}</td>
                                <td>{{ number_format($f['size'] / 1024, 1) }} KB</td>
                                <td>{{ \Carbon\Carbon::createFromTimestamp($f['time'])->format('Y-m-d H:i:s') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-link btn-sm"
                                        href="{{ route('admin.dbbackups.download', $f['name']) }}">Tải</a>

                                    <form method="POST" action="{{ route('admin.dbbackups.destroy', $f['name']) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa backup {{ $f['name'] }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link btn-sm text-danger">Xoá</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.dbbackups.restore') }}" class="d-inline"
                                        onsubmit="return confirm('Khôi phục từ {{ $f['name'] }}? DỮ LIỆU HIỆN TẠI SẼ BỊ GHI ĐÈ!');">
                                        @csrf
                                        <input type="hidden" name="file" value="{{ $f['name'] }}">
                                        <input type="hidden" name="force" value="1">
                                        <button class="btn btn-link btn-sm">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">Chưa có bản backup.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection