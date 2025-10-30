@extends('layouts.admin')

@section('title', 'Quản lý câu hỏi tùy chỉnh')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Quản lý câu hỏi tùy chỉnh</h1>
            <a href="{{ route('admin.data-source.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm câu hỏi tùy chỉnh
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.data-source.index') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên hoặc slug..."
                            value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tên câu hỏi tùy chỉnh</th>
                                <th>Slug</th>
                                <th class="text-center">Số lượng giá trị</th>
                                <th class="text-center" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataSources as $dataSource)
                                <tr>
                                    <td>{{ $dataSource->name }}</td>
                                    <td><code>{{ $dataSource->slug }}</code></td>
                                    <td class="text-center">{{ $dataSource->values_count }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.data-source.edit', $dataSource) }}"
                                                class="btn btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.data-source.destroy', $dataSource) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi tùy chỉnh này? Tất cả các giá trị liên quan cũng sẽ bị xóa.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Chưa có dữ liệu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $dataSources->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
@endsection