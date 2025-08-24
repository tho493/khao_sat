@extends('layouts.admin')

@section('title', 'Quản lý mẫu khảo sát')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Quản lý mẫu khảo sát</h1>
        <a href="{{ route('admin.mau-khao-sat.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tạo mẫu mới
        </a>
    </div>

    <!-- Search -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.mau-khao-sat.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Tìm kiếm theo tên mẫu..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="nguoi_tao">
                            <option value="">-- Người tạo --</option>
                            @foreach($dsNguoiTao as $id => $ten)
                                <option value="{{ $id }}" {{ request('nguoi_tao') == $id ? 'selected' : '' }}>
                                    {{ $ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="trangthai">
                            <option value="">-- Trạng thái --</option>
                            <option value="active" {{ request('trangthai') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="draft" {{ request('trangthai') == 'draft' ? 'selected' : '' }}>Nháp</option>
                            <option value="closed" {{ request('trangthai') == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="ngay_tao" value="{{ request('ngay_tao') }}" placeholder="Ngày tạo">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên mẫu</th>
                            <th>Số câu hỏi</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mauKhaoSats as $mau)
                            <tr>
                                <td>{{ $mau->id }}</td>
                                <td>
                                    <strong>{{ $mau->ten_mau }}</strong>
                                    @if($mau->mota)
                                        <br><small class="text-muted">{{ Str::limit($mau->mota, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $mau->cauHoi->count() }} câu
                                    </span>
                                </td>
                                <td>
                                    @switch($mau->trangthai)
                                        @case('active')
                                            <span class="badge bg-success">Hoạt động</span>
                                            @break
                                        @case('draft')
                                            <span class="badge bg-warning">Nháp</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Không hoạt động</span>
                                    @endswitch
                                </td>
                                <td value="{{ $mau->nguoiTao->id }}">
                                    {{ $mau->nguoiTao->hoten ?? 'N/A' }} 
                                </td>
                                <td>{{ $mau->created_at ? $mau->created_at->format('d/m/Y') : '' }}</td>
                                <td>
                                    <a href="{{ route('admin.mau-khao-sat.edit', $mau) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                   {{-- 
                                   @if($mau->dotKhaoSat->count() == 0)
                                       <button type="button" class="btn btn-sm btn-outline-danger" 
                                               onclick="deleteMauKhaoSat({{ $mau->id }}, '{{ $mau->ten_mau }}')"
                                               title="Xóa">
                                           <i class="bi bi-trash"></i>
                                       </button>
                                   @endif
                                   --}}
                                    
                                    {{-- <form id="delete-form-{{ $mau->id }}" 
                                          action="{{ route('admin.mau-khao-sat.destroy', $mau) }}" 
                                          method="POST" style="display: none;">
                                        @csrf
                                         {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField('delete_form') !!} 
                                        @method('DELETE')
                                    </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted">Không có dữ liệu</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                @if ($mauKhaoSats->hasPages())
                    {{ $mauKhaoSats->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>



function deleteMauKhaoSat(id, name) {
    if (confirm(`Bạn có chắc chắn muốn xóa mẫu khảo sát "${name}"?\nHành động này không thể hoàn tác!`)) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush