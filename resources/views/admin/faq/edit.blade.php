@extends('layouts.admin')
@section('title', 'Sửa FAQ Chatbot')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.faq.index') }}">Quản lý FAQ</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Sửa câu hỏi FAQ</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.faq.update', $faq) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Câu hỏi mẫu (Tùy chọn)</label>
                                <input type="text" class="form-control" name="question"
                                    value="{{ old('question', $faq->question) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Từ khóa (cách nhau bởi dấu phẩy) <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('keywords') is-invalid @enderror"
                                    name="keywords" value="{{ old('keywords', $faq->keywords) }}" required>
                                @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Câu trả lời của Bot <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('answer') is-invalid @enderror" name="answer" rows="5"
                                    required>{{ old('answer', $faq->answer) }}</textarea>
                                @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_enabled" value="1"
                                    id="is_enabled_edit" {{ old('is_enabled', $faq->is_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_enabled_edit">Bật (Enabled)</label>
                            </div>
                            <div class="text-end mt-4">
                                <a href="{{ route('admin.faq.index') }}" class="btn btn-secondary">Hủy</a>
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection