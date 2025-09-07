@extends('layouts.admin')

@section('title', 'Chỉnh sửa mẫu khảo sát')

@push('styles')
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: #f0f0f0;
        }

        .question-item {
            cursor: grab;
        }

        .handle {
            cursor: move;
        }
    </style>
@endpush

@section('content')
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.mau-khao-sat.index') }}">Mẫu khảo sát</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa: {{ $mauKhaoSat->ten_mau }}</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Thông tin mẫu -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin mẫu khảo sát</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.mau-khao-sat.update', $mauKhaoSat) }}"
                                id="formUpdateMau">
                                @csrf
                                {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField('update_thongtin_maukahosat') !!}
                                @method('PUT')
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Tên mẫu khảo sát <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('ten_mau') is-invalid @enderror"
                                            name="ten_mau" value="{{ old('ten_mau', $mauKhaoSat->ten_mau) }}" required @if($isLocked) disabled @endif>
                                        @error('ten_mau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Trạng thái</label>
                                        <select class="form-select @error('trangthai') is-invalid @enderror" name="trangthai">
                                            <option value="draft" {{ old('trangthai', $mauKhaoSat->trangthai) == 'draft' ? 'selected' : '' }}>Nháp</option>
                                            <option value="active" {{ old('trangthai', $mauKhaoSat->trangthai) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="inactive" {{ old('trangthai', $mauKhaoSat->trangthai) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                        </select>
                                        @error('trangthai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control @error('mota') is-invalid @enderror" name="mota"
                                        rows="3" @if($isLocked) disabled @endif >{{ old('mota', $mauKhaoSat->mota) }} </textarea>
                                    @error('mota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu thay
                                        đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danh sách câu hỏi -->
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Danh sách câu hỏi (<span id="question-count">{{ $mauKhaoSat->cauHoi->count() ?? 0 }}</span> câu)</h5>
                            <button class="btn btn-primary btn-sm"
                                onclick="@if($isLocked) alert('Mẫu khảo sát đang bị khóa, không thể thêm câu hỏi.'); @else showModalThemCauHoi(); @endif">
                                <i class="bi bi-plus"></i> Thêm câu hỏi
                            </button>
                        </div>
                        <div class="card-body">
                            {{-- DIV NÀY JAVASCRIPT ĐIỀN MẤY CÂU HỎI --}}
                            <div id="danhSachCauHoi" class="{{ $isLocked ? '' : 'sortable' }}">
                                <div id="questions-loading" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Đang tải câu hỏi...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Thông tin -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Thông tin</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="text-muted">ID:</td>
                                    <td><strong>{{ $mauKhaoSat->id }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Số câu hỏi:</td>
                                    <td>
                                        <span class="badge bg-info">{{ $mauKhaoSat->cauHoi->count() ?? 0 }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Người tạo:</td>
                                    <td>{{ $mauKhaoSat->nguoiTao->hoten ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ngày tạo:</td>
                                    <td>{{ $mauKhaoSat->created_at ? $mauKhaoSat->created_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cập nhật:</td>
                                    <td>{{ $mauKhaoSat->updated_at ? $mauKhaoSat->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                            </table>

                            @if($isLocked)
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                    <div>
                                        <strong>Mẫu khảo sát đang bị khóa.</strong> Mẫu này đang được sử dụng trong ít nhất một đợt
                                        khảo sát đang hoạt
                                        động. <br>
                                        Bạn chỉ có thể thay đổi <strong>trạng thái</strong> và <strong>thứ tự câu hỏi</strong> của
                                        mẫu. Các thông tin khác và danh sách
                                        câu hỏi sẽ không
                                        thể chỉnh sửa.
                                    </div>
                                </div>
                            @endif

                            @if(isset($mauKhaoSat->dotKhaoSat) && $mauKhaoSat->dotKhaoSat->isNotEmpty())
                                @php
    // Sử dụng các phương thức của Collection để lọc và đếm
    $activeCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'active')->count();
    $draftCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'draft')->count();
    $closedCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'closed')->count();
    $totalCount = $mauKhaoSat->dotKhaoSat->count();
                                @endphp

                                <div class="alert alert-info">
                                    <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle-fill"></i> Tình trạng sử dụng</h6>
                                    <p class="mb-2">Mẫu khảo sát này đang được sử dụng trong tổng cộng
                                        <strong>{{ $totalCount }}</strong> đợt khảo sát:
                                    </p>
                                    <ul class="list-unstyled mb-0">
                                        @if($activeCount > 0)
                                            <li>
                                                <span class="badge bg-success me-1">{{ $activeCount }}</span>
                                                đợt đang <strong>hoạt động</strong>.
                                                <span class="text-danger small">(Không nên thay đổi câu hỏi)</span>
                                            </li>
                                        @endif
                                        @if($draftCount > 0)
                                            <li>
                                                <span class="badge bg-warning me-1">{{ $draftCount }}</span>
                                                đợt ở trạng thái <strong>nháp</strong>.
                                            </li>
                                        @endif
                                        @if($closedCount > 0)
                                            <li>
                                                <span class="badge bg-secondary me-1">{{ $closedCount }}</span>
                                                đợt đã <strong>đóng</strong>.
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Thao tác -->
                    <div class="card shadow">
                        <div class="card-body">
                            <h6 class="card-title">Thao tác</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.mau-khao-sat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                                </a>

                                <button type="button" class="btn btn-info" onclick="copyMauKhaoSat()">
                                    <i class="bi bi-files"></i> Sao chép mẫu này
                                </button>

                                @if($mauKhaoSat->trangthai == 'active' && ($mauKhaoSat->cauHoi->count() ?? 0) > 0)
                                    <a href="{{ route('admin.dot-khao-sat.create') }}?mau_khaosat_id={{ $mauKhaoSat->id }}"
                                        class="btn btn-success">
                                        <i class="bi bi-calendar-plus"></i> Tạo đợt khảo sát
                                    </a>
                                @endif

                                @if(($mauKhaoSat->dotKhaoSat->count() ?? 0) == 0)
                                    <button type="button" class="btn btn-danger" onclick="deleteMauKhaoSat()">
                                        <i class="bi bi-trash"></i> Xóa mẫu này
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal thêm/sửa câu hỏi -->
            <div class="modal fade" id="modalCauHoi" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Thêm câu hỏi mới</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="validation-errors" class="alert alert-danger d-none"></div>
                            <!-- thông báo lỗi hiển thị ở id validation-errors -->

                            <form id="formCauHoi" onsubmit="saveCauHoi(event)">
                                <input type="hidden" id="cauHoiId">

                                <div class="mb-3">
                                    <label for="noiDungCauHoi" class="form-label">Nội dung câu hỏi <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="noiDungCauHoi" rows="2" required
                                        placeholder="Nhập nội dung câu hỏi..."></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="loaiCauHoi" class="form-label">Loại câu hỏi</label>
                                        <select class="form-select" id="loaiCauHoi" onchange="togglePhuongAnContainer()">
                                            <option value="single_choice">Chọn một</option>
                                            <option value="multiple_choice">Chọn nhiều</option>
                                            <option value="text">Văn bản</option>
                                            <option value="likert">Thang đo Likert (5 mức)</option>
                                            <option value="rating">Đánh giá (1-5 sao)</option>
                                            <option value="date">Ngày tháng</option>
                                            <option value="number">Số</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="thuTu" class="form-label">Thứ tự</label>
                                        <input type="number" class="form-control" id="thuTu" value="0" min="0">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="batBuoc" checked>
                                            <label class="form-check-label" for="batBuoc">Bắt buộc trả lời</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="phuongAnContainer">
                                    <label class="form-label">Phương án trả lời <span id="phuongAnRequired"
                                            class="text-danger">*</span></label>
                                    <div id="danhSachPhuongAn">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="btnAddPhuongAn"
                                        onclick="addPhuongAn()">
                                        <i class="bi bi-plus"></i> Thêm phương án
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" id="btnSaveCauHoi" onclick="saveCauHoi(event)">
                                <i class="bi bi-save"></i> Lưu câu hỏi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forms ẩn -->
            <form id="formCopyMau" action="{{ route('admin.mau-khao-sat.copy', $mauKhaoSat) }}" method="POST"
                style="display: none;">
                @csrf
                {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField('copy_template') !!}
            </form>
            <form id="formDeleteMau" action="{{ route('admin.mau-khao-sat.destroy', $mauKhaoSat) }}" method="POST"
                style="display: none;">
                @csrf 
                {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField('delete_form') !!}
                @method('DELETE')</form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        const mauKhaoSatId = {{ $mauKhaoSat->id }};
        const isLocked = {{ $isLocked ? "true" : "false" }};
        const modalCauHoi = new bootstrap.Modal(document.getElementById('modalCauHoi'));
        let questionsData = [];

        // =======================================================
        // ==         HELPER FUNCTIONS                          ==
        // =======================================================

        /**
         * Chuyển đổi các ký tự HTML đặc biệt để tránh lỗi XSS
         */
        function escapeHtml(text) {
            if (typeof text !== 'string') return '';
            var map = {
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function (m) { return map[m]; });
        }

        /**
         * Chuyển đổi 'key' của loại câu hỏi thành tên tiếng Việt
         */
        function getQuestionTypeName(type) {
            const names = {
                'single_choice': 'Chọn một',
                'multiple_choice': 'Chọn nhiều',
                'text': 'Văn bản',
                'likert': 'Thang đo Likert',
                'rating': 'Đánh giá',
                'date': 'Ngày tháng',
                'number': 'Số'
            };
            return names[type] || type;
        }

        // =======================================================
        // ==         RENDER QUESTIONS                          ==
        // =======================================================
        function renderQuestions() {
            const container = $('#danhSachCauHoi');
            container.empty(); // Xóa nội dung cũ

            $('#question-count').text(questionsData.length); // Cập nhật số lượng

            if (questionsData.length === 0) {
                container.html(`
            <div class="text-center py-5">
                <i class="bi bi-question-circle fs-1 text-muted"></i>
                <p class="text-muted">Chưa có câu hỏi nào</p>
                <button class="btn btn-primary" onclick="showModalThemCauHoi()"><i class="bi bi-plus"></i> Thêm câu hỏi đầu tiên</button>
            </div>
        `);
                return;
            }

            // Sắp xếp dữ liệu trước khi render
            questionsData.sort((a, b) => a.thutu - b.thutu);

            $.each(questionsData, function (index, cauHoi) {
                const stt = index + 1;
                let optionsHtml = '';
                if (['single_choice', 'multiple_choice', 'likert'].includes(cauHoi.loai_cauhoi) && cauHoi.phuong_an_tra_loi.length > 0) {
                    optionsHtml = '<ol class="mb-0 ps-3 small text-muted">';
                    $.each(cauHoi.phuong_an_tra_loi, function (i, pa) {
                        optionsHtml += `<li>${escapeHtml(pa.noidung)}</li>`;
                    });
                    optionsHtml += '</ol>';
                }

            const questionHtml = `
            <div class="card mb-3 question-item" data-id="${cauHoi.id}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">Câu ${stt}</span>
                                <h6 class="mb-0">${escapeHtml(cauHoi.noidung_cauhoi)} ${cauHoi.batbuoc ? '<span class="text-danger">*</span>' : ''}</h6>
                            </div>
                            <div class="mb-2"><span class="badge bg-info">${getQuestionTypeName(cauHoi.loai_cauhoi)}</span></div>
                            ${optionsHtml}
                        </div>
                        ${!isLocked ? `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary handle" title="Kéo để sắp xếp"><i class="bi bi-grip-vertical"></i></button>
                            <button class="btn btn-outline-primary" onclick="showModalSuaCauHoi(${cauHoi.id})" title="Sửa"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteCauHoi(${cauHoi.id})" title="Xóa"><i class="bi bi-trash"></i></button>
                        </div>`
                        : ` <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary handle" title="Kéo để sắp xếp"><i class="bi bi-grip-vertical"></i></button>
                        </div >`
                        }
                    </div>
                </div>
            </div>
        `;
                container.append(questionHtml);
            });
        }

        function loadInitialQuestions() {
            $.get(`/admin/mau-khao-sat/${mauKhaoSatId}/questions`)
                .done(function (data) {
                    questionsData = data;
                    renderQuestions();
                })
                .fail(function () {
                    $('#danhSachCauHoi').html('<div class="alert alert-danger">Không thể tải danh sách câu hỏi.</div>');
                });
        }

        // --- LOGIC CHUNG CHO MODAL ---
        function showModalThemCauHoi() {
            $('#modalTitle').text('Thêm câu hỏi mới');
            $('#formCauHoi')[0].reset();
            $('#cauHoiId').val('');
            $('#batBuoc').prop('checked', true);
            $('#validation-errors').addClass('d-none').html('');
            $('#danhSachPhuongAn').html('');
            togglePhuongAnContainer();
            modalCauHoi.show();
        }

        function showModalSuaCauHoi(cauHoiId) {
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
                return;
            }

            $.get(`/admin/cau-hoi/${cauHoiId}`, function (cauHoi) {
                $('#modalTitle').text('Sửa câu hỏi');
                $('#formCauHoi')[0].reset();
                $('#validation-errors').addClass('d-none').html('');

                $('#cauHoiId').val(cauHoi.id);
                $('#noiDungCauHoi').val(cauHoi.noidung_cauhoi);
                $('#loaiCauHoi').val(cauHoi.loai_cauhoi);
                $('#thuTu').val(cauHoi.thutu);
                $('#batBuoc').prop('checked', cauHoi.batbuoc);

                const phuongAnContainer = $('#danhSachPhuongAn');
                phuongAnContainer.html(''); // Xóa các phương án cũ
                togglePhuongAnContainer(); // Cập nhật hiển thị container

                if (cauHoi.phuong_an_tra_loi && cauHoi.phuong_an_tra_loi.length > 0) {
                    const isLikert = cauHoi.loai_cauhoi === 'likert';
                    // Sắp xếp phương án theo thứ tự trước khi hiển thị
                    cauHoi.phuong_an_tra_loi.sort((a, b) => a.thutu - b.thutu).forEach(function (pa) {
                        addPhuongAn(pa.noidung, isLikert);
                    });
                }

                modalCauHoi.show();
            }).fail(function (xhr) {
                console.error(xhr);
                alert('Không thể tải dữ liệu câu hỏi. Vui lòng kiểm tra Console (F12).');
            });
        }


        function togglePhuongAnContainer() {
            const loai = $('#loaiCauHoi').val();
            const container = $('#phuongAnContainer');
            const isChoiceType = ['single_choice', 'multiple_choice', 'likert'].includes(loai);

            if (isChoiceType) {
                container.show();
                if ($('#danhSachPhuongAn').is(':empty')) {
                    if (loai === 'likert') {
                        const likertOptions = ['Rất không hài lòng', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
                        likertOptions.forEach(option => addPhuongAn(option, true));
                    } else {
                        addPhuongAn(''); 
                        addPhuongAn('');
                    }
                }
            } else {
                container.hide();
            }
        }

        function addPhuongAn(value = '', isReadonly = false) {
            const count = $('#danhSachPhuongAn .input-group').length + 1;
            const readonlyAttr = isReadonly ? 'readonly' : '';
            const html = `<div class="input-group mb-2"><span class="input-group-text">${count}</span><input type="text" class="form-control phuong-an" value="${value}" ${readonlyAttr}><button class="btn btn-outline-danger" type="button" onclick="removePhuongAn(this)"><i class="bi bi-trash"></i></button></div>`;
            $('#danhSachPhuongAn').append(html);
        }

        function removePhuongAn(btn) {
            if ($('#danhSachPhuongAn .input-group').length > 2) {
                $(btn).closest('.input-group').remove();
                $('#danhSachPhuongAn .input-group').each(function (index) {
                    $(this).find('.input-group-text').text(index + 1);
                });
            } else {
                alert('Phải có ít nhất 2 phương án trả lời.');
            }
        }

        function saveCauHoi(event) {
            event.preventDefault();
            const btn = $('#btnSaveCauHoi');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');

            const cauHoiId = $('#cauHoiId').val();
            const data = {
                noidung_cauhoi: $('#noiDungCauHoi').val(),
                loai_cauhoi: $('#loaiCauHoi').val(),
                thutu: $('#thuTu').val() || 0,
                batbuoc: $('#batBuoc').is(':checked') ? 1 : 0,
                phuong_an: []
            };

            $('.phuong-an').each(function () {
                if ($(this).val().trim() !== '') data.phuong_an.push($(this).val().trim());
            });

            const url = cauHoiId ? `/admin/cau-hoi/${cauHoiId}` : `/admin/mau-khao-sat/${mauKhaoSatId}/cau-hoi`;
            const method = cauHoiId ? 'PUT' : 'POST';

            $.ajax({
                url: url, method: method, data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        modalCauHoi.hide();
                        loadInitialQuestions();
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errorHtml = '<ul>';
                        $.each(xhr.responseJSON.errors, (key, value) => { errorHtml += `<li>${value[0]}</li>`; });
                        errorHtml += '</ul>';
                        $('#validation-errors').html(errorHtml).removeClass('d-none');
                    } else {
                        alert('Đã xảy ra lỗi không mong muốn.');
                    }
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="bi bi-save"></i> Lưu câu hỏi');
                }
            });
        }

        // --- CÁC HÀM KHÁC ---
        function deleteCauHoi(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) return;
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
                return;
            }
            
            $.ajax({
                url: `/admin/cau-hoi/${id}`, method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    questionsData = questionsData.filter(q => q.id !== id);
                    renderQuestions();
                },
                error: function (xhr) { alert('Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại')); }
            });
        }

        function copyMauKhaoSat() {
            if (confirm('Bạn có chắc chắn muốn sao chép mẫu này?')) $('#formCopyMau').submit();
        }

        function deleteMauKhaoSat() {
            if (confirm('Bạn có chắc chắn muốn xóa mẫu khảo sát này? Hành động này không thể hoàn tác!')) $('#formDeleteMau').submit();
        }

    // =======================================================
    // ==         KHỞI CHẠY                                 ==
    // =======================================================
    $(document).ready(function () {
        loadInitialQuestions();
        const el = document.getElementById('danhSachCauHoi');
        Sortable.create(el, {
            handle: '.handle', animation: 150, ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                const order = Array.from(el.children).map(item => parseInt(item.dataset.id));
                const sortableInstance = Sortable.get(el);
                sortableInstance.option("disabled", true);

                $.ajax({
                    url: "{{ route('admin.cau-hoi.update-order') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    method: 'POST', data: { order: order },
                    success: function (response) {
                        order.forEach((id, index) => {
                            const question = questionsData.find(q => q.id === id);
                            if (question) question.thutu = index + 1;
                        });
                        renderQuestions();
                    },
                    error: function () {
                        alert('Lỗi khi cập nhật thứ tự. Vui lòng tải lại trang.');
                    },
                    complete: function () {
                        sortableInstance.option("disabled", false);
                    }
                });
            },
        });
    });
    </script>
@endpush