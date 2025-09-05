@extends('layouts.admin')
@section('title', 'Quản lý FAQ Chatbot')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Quản lý FAQ Chatbot</h1>
            {{-- Nút "Thêm" sẽ mở modal --}}
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                <i class="bi bi-plus-circle"></i> Thêm FAQ mới
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.faq.index') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search"
                            placeholder="Tìm kiếm từ khóa, câu hỏi, câu trả lời..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
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
                                <th>Câu hỏi mẫu</th>
                                <th>Từ khóa</th>
                                <th>Câu trả lời</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                <tr>
                                    <td>{{ $faq->question ?? '(Không có)' }}</td>
                                    <td>
                                        @foreach(explode(',', $faq->keywords) as $keyword)
                                            <span class="badge bg-secondary">{{ trim($keyword) }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ Str::limit($faq->answer, 80) }}</td>
                                    <td class="text-center">
                                        @if($faq->is_enabled)
                                            <span class="badge bg-success">Đang bật</span>
                                        @else
                                            <span class="badge bg-danger">Đã tắt</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.faq.edit', $faq) }}" class="btn btn-outline-primary"
                                                title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.faq.destroy', $faq) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
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
                                    <td colspan="5" class="text-center py-4">Không có dữ liệu FAQ nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $faqs->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm FAQ -->
    <div class="modal fade" id="addFaqModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.faq.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm FAQ mới</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Câu hỏi mẫu (Tùy chọn)</label>
                            <input type="text" class="form-control" name="question" value="{{ old('question') }}"
                                placeholder="VD: Thông tin của tôi có an toàn không?">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Từ khóa (cách nhau bởi dấu phẩy) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('keywords') is-invalid @enderror" name="keywords"
                                value="{{ old('keywords') }}" placeholder="VD: bảo mật, an toàn, thông tin" required>
                            @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Câu trả lời của Bot <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('answer') is-invalid @enderror" name="answer" rows="5"
                                required>{{ old('answer') }}</textarea>
                            @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="is_enabled_add"
                                checked>
                            <label class="form-check-label" for="is_enabled_add">Bật (Enabled)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection