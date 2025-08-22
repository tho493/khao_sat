@extends('layouts.admin')

@section('title', 'Tạo mẫu khảo sát')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.mau-khao-sat.index') }}">Mẫu khảo sát</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Tạo mẫu khảo sát mới</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.mau-khao-sat.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Tên mẫu khảo sát <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ten_mau') is-invalid @enderror"
                                    name="ten_mau" value="{{ old('ten_mau') }}"
                                    placeholder="VD: Khảo sát sinh viên về chất lượng giảng dạy" required>
                                @error('ten_mau')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control @error('mota') is-invalid @enderror" name="mota" rows="3"
                                    placeholder="Mô tả ngắn gọn về mục đích của mẫu khảo sát...">{{ old('mota') }}</textarea>
                                @error('mota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-end">
                                <a href="{{ route('admin.mau-khao-sat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Tạo mẫu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h6 class="card-title">Hướng dẫn</h6>
                        <ol class="mb-0">
                            <li>Nhập tên mẫu khảo sát rõ ràng, dễ hiểu</li>
                            <li>Sau khi tạo mẫu, bạn sẽ được chuyển đến trang thêm câu hỏi</li>
                            <li>Mẫu khảo sát mặc định ở trạng thái "Nháp"</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection