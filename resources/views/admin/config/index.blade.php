{{-- resources/views/admin/config/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Cấu hình hệ thống')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Cấu hình hệ thống</h1>
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Quản lý Template Email</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                    <i class="bi bi-plus-circle"></i> Thêm mới
                </button>
            </div>
            <div class="card-body">
                <div class="accordion" id="emailTemplatesAccordion">
                    @forelse($emailTemplates as $template)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#template-{{ $template->id }}">
                                    {{ $template->ten_template }}
                                    <small class="text-muted ms-2">(`{{ $template->ma_template }}`)</small>
                                </button>
                            </h2>
                            <div id="template-{{ $template->id }}" class="accordion-collapse collapse"
                                data-bs-parent="#emailTemplatesAccordion">
                                <div class="accordion-body">
                                    <form method="POST" action="{{ route('admin.config.email-template.update', $template) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label class="form-label">Tên Template</label>
                                            <input type="text" class="form-control" name="ten_template"
                                                value="{{ $template->ten_template }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tiêu đề Email</label>
                                            <input type="text" class="form-control" name="tieude"
                                                value="{{ $template->tieude }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nội dung</label>
                                            <textarea class="form-control" name="noidung" rows="6"
                                                required>{{ $template->noidung }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Các biến có thể sử dụng</label>
                                            <input type="text" class="form-control" name="bien_template"
                                                value="{{ implode(', ', $template->bien_template ?? []) }}"
                                                placeholder="VD: ho_ten, link_khaosat">
                                            <small class="text-muted">Các biến cách nhau bởi dấu phẩy (,)</small>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDeleteTemplate('{{ $template->id }}', '{{ $template->ten_template }}')">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-secondary me-2"
                                                    onclick="testEmail({{ $template->id }})">
                                                    <i class="bi bi-send"></i> Test
                                                </button>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-save"></i> Lưu
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="delete-template-form-{{ $template->id }}"
                                        action="{{ route('admin.config.email-template.destroy', $template) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted">Chưa có template email nào.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Template Email -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.config.email-template.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm Template Email mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ten_template') is-invalid @enderror"
                                name="ten_template" value="{{ old('ten_template') }}"
                                placeholder="VD: Thư mời tham gia khảo sát" required>
                            @error('ten_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mã Template (Key) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ma_template') is-invalid @enderror"
                                name="ma_template" value="{{ old('ma_template') }}" placeholder="VD: invite_survey"
                                required>
                            @error('ma_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Chữ thường, không dấu, không khoảng trắng. VD: `invite_survey`</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề Email <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('tieude') is-invalid @enderror" name="tieude"
                                value="{{ old('tieude') }}" placeholder="VD: Thư mời tham gia khảo sát {ten_khaosat}"
                                required>
                            @error('tieude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('noidung') is-invalid @enderror" name="noidung" rows="6"
                                required>{{ old('noidung') }}</textarea>
                            @error('noidung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Các biến có thể sử dụng</label>
                            <input type="text" class="form-control @error('bien_template') is-invalid @enderror"
                                name="bien_template" value="{{ old('bien_template') }}"
                                placeholder="VD: ho_ten, ten_khaosat, link_khaosat">
                            @error('bien_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Các biến cách nhau bởi dấu phẩy (,)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Thêm mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ... Modal test email giữ nguyên ... --}}
@endsection

@push('scripts')
    {{-- ... script test email giữ nguyên ... --}}
    <script>
        function confirmDeleteTemplate(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa template "${name}"?`)) {
                document.getElementById('delete-template-form-' + id).submit();
            }
        }

        // Giữ lại modal nếu có lỗi validation khi thêm mới template
        @if($errors->has('ma_template') || $errors->has('ten_template'))
            document.addEventListener('DOMContentLoaded', function () {
                var addTemplateModal = new bootstrap.Modal(document.getElementById('addTemplateModal'));
                addTemplateModal.show();
            });
        @endif
    </script>
@endpush