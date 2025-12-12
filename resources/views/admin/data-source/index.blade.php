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
                                <th class="text-center">Số lượng đáp án</th>
                                <th class="text-center">Số lượng khảo sát sử dụng</th>
                                <th class="text-center" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataSources as $dataSource)
                                @php
                                    $surveyCount = $dataSource->questions->pluck('mau_khaosat_id')->unique()->count();
                                @endphp
                                <tr>
                                    <td>{{ $dataSource->name }}</td>
                                    <td><code>{{ $dataSource->slug }}</code></td>
                                    <td class="text-center">{{ $dataSource->values_count }}</td>
                                    <td class="text-center">
                                        @if($surveyCount > 0)
                                            <span class="badge bg-info">{{ $surveyCount }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.data-source.edit', $dataSource) }}"
                                                class="btn btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($surveyCount > 0)
                                                <button type="button" class="btn btn-outline-secondary" disabled
                                                    title="Không thể xóa vì đang được sử dụng trong {{ $surveyCount }} mẫu khảo sát.">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.data-source.destroy', $dataSource) }}" method="POST"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi tùy chỉnh này? Tất cả các giá trị liên quan cũng sẽ bị xóa.');"
                                                    style="display: inline-block;">
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