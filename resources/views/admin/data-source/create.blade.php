@extends('layouts.admin')

@section('title', 'Thêm Câu hỏi tùy chỉnh')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Thêm Câu hỏi tùy chỉnh</h1>
            <a href="{{ route('admin.data-source.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.data-source.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên câu hỏi tùy chỉnh <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Ví dụ: "Chương trình đào tạo", "Danh sách Khoa", "Lớp học". Tên này sẽ được
                            tự động chuyển thành slug.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu và tiếp tục</button>
                </form>
            </div>
        </div>
    </div>
@endsection