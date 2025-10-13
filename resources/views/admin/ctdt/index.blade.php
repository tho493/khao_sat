@extends('layouts.admin')
@section('title', 'Quản lý Chương trình đào tạo')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Quản lý Chương trình đào tạo (CTĐT)</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCtdtModal">
                <i class="bi bi-plus-circle"></i> Thêm CTĐT
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.ctdt.index') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Tìm theo mã hoặc tên CTĐT..."
                            value="{{ request('search') }}">
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
                                <th style="width: 150px;">Mã CTĐT</th>
                                <th>Tên Chương trình đào tạo</th>
                                <th class="text-center" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ctdts as $ctdt)
                                <tr>
                                    <td><code>{{ $ctdt->mactdt }}</code></td>
                                    <td>{{ $ctdt->tenctdt }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Sửa"
                                                onclick="openEditModal('{{ json_encode($ctdt) }}')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.ctdt.destroy', $ctdt->mactdt) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa CTĐT này?')">
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
                                    <td colspan="3" class="text-center py-4">Chưa có dữ liệu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $ctdts->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm -->
    <div class="modal fade" id="addCtdtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.ctdt.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm CTĐT mới</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mã CTĐT <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mactdt') is-invalid @enderror" name="mactdt"
                                value="{{ old('mactdt') }}" required>
                            @error('mactdt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tên CTĐT <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('tenctdt') is-invalid @enderror" name="tenctdt"
                                value="{{ old('tenctdt') }}" required>
                            @error('tenctdt')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    <div class="modal fade" id="editCtdtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCtdtForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa Chương trình đào tạo</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mã CTĐT</label>
                            <input type="text" class="form-control" id="edit_mactdt" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tên CTĐT <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_tenctdt" name="tenctdt" required>
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
        function openEditModal(ctdtJson) {
            // Parse chuỗi JSON thành object
            const ctdt = JSON.parse(ctdtJson);

            // Cập nhật action của form
            const form = document.getElementById('editCtdtForm');
            form.action = `{{ route('admin.ctdt.update', $ctdt->mactdt) }}`;

            // Điền dữ liệu vào các input
            document.getElementById('edit_mactdt').value = ctdt.mactdt;
            document.getElementById('edit_tenctdt').value = ctdt.tenctdt;

            // Mở modal
            const editModal = new bootstrap.Modal(document.getElementById('editCtdtModal'));
            editModal.show();
        }
    </script>
@endpush