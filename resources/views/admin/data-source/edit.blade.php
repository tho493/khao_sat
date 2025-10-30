@extends('layouts.admin')

@section('title', 'Sửa Câu hỏi tùy chỉnh: ' . $dataSource->name)

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Sửa Câu hỏi tùy chỉnh: {{ $dataSource->name }}</h1>
            <a href="{{ route('admin.data-source.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Các giá trị của Câu hỏi tùy chỉnh</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nhãn (Label)</th>
                                        <th>Giá trị (Value)</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataSource->values as $value)
                                                    <tr>
                                                        <form action="{{ route('admin.data-source.value.update', $value) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <td><input type="text" name="label" class="form-control form-control-sm"
                                                                    value="{{ $value->label }}"></td>
                                                            <td><input type="text" name="value" class="form-control form-control-sm"
                                                                    value="{{ $value->value }}"></td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button type="submit" class="btn btn-outline-success" title="Lưu"><i
                                                                            class="bi bi-save"></i></button>
                                                        </form>
                                                        <form action="{{ route('admin.data-source.value.destroy', $value) }}" method="POST"
                                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa giá trị này?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Xóa"><i
                                                                    class="bi bi-trash"></i></button>
                                                        </form>
                                        </div>
                                        </td>
                                        </tr>
                                    @empty
                            <tr>
                                <td colspan="3" class="text-center">Chưa có giá trị nào.</td>
                            </tr>
                        @endforelse
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Thêm giá trị mới</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.data-source.value.store', $dataSource) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nhãn (Label) <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" required>
                            <div class="form-text">Đây là những gì người dùng sẽ thấy trong danh sách thả xuống.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá trị (Value) <span class="text-danger">*</span></label>
                            <input type="text" name="value" class="form-control" required>
                            <div class="form-text">Giá trị này sẽ được lưu trữ trong kết quả khảo sát.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm giá trị</button>
                    </form>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Sửa thông tin Câu hỏi tùy chỉnh</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.data-source.update', $dataSource) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Tên câu hỏi tùy chỉnh</label>
                            <input type="text" name="name" class="form-control" value="{{ $dataSource->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" value="{{ $dataSource->slug }}" disabled>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection