@extends('layouts.admin')
@section('title', 'Quản lý Năm học')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Quản lý Năm học</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNamHocModal">
                <i class="bi bi-plus-circle"></i> Thêm Năm học
            </button>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Năm học</th>
                                <th class="text-center">Số đợt khảo sát</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($namHocs as $namHoc)
                                <tr>
                                    <td><strong>{{ $namHoc->namhoc }}</strong></td>
                                    <td class="text-center"><span class="badge bg-info">{{ $namHoc->dot_khao_sat_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($namHoc->trangthai)
                                            <span class="badge bg-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">Đã ẩn</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Sửa"
                                                onclick="openEditModal({{ $namHoc }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @if($namHoc->dot_khao_sat_count == 0)
                                                <form action="{{ route('admin.nam-hoc.destroy', $namHoc) }}" method="POST"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa năm học {{ $namHoc->namhoc }}?')">
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
                                    <td colspan="4" class="text-center py-4">Chưa có dữ liệu năm học.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $namHocs->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm -->
    <div class="modal fade" id="addNamHocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.nam-hoc.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm Năm học mới</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Năm học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="namhoc" placeholder="VD: 2024-2025" required
                                pattern="\d{4}-\d{4}">
                            <small class="form-text text-muted">Phải đúng định dạng YYYY-YYYY.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="trangthai" value="1" id="trangthai_add"
                                checked>
                            <label class="form-check-label" for="trangthai_add">Hoạt động</label>
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

    <!-- Modal Sửa -->
    <div class="modal fade" id="editNamHocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editNamHocForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa Năm học</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Năm học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_namhoc" name="namhoc" required
                                pattern="\d{4}-\d{4}">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="trangthai" value="1" id="edit_trangthai">
                            <label class="form-check-label" for="edit_trangthai">Hoạt động</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEditModal(namHoc) {
            const form = document.getElementById('editNamHocForm');
            form.action = `{{ route('admin.nam-hoc.update', $namHoc->id) }}`;
            document.getElementById('edit_namhoc').value = namHoc.namhoc;
            document.getElementById('edit_trangthai').checked = namHoc.trangthai;

            const editModal = new bootstrap.Modal(document.getElementById('editNamHocModal'));
            editModal.show();
        }

        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                var addModal = new bootstrap.Modal(document.getElementById('addNamHocModal'));
                addModal.show();
            });
        @endif
    </script>
@endpush