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

        @if($isLocked)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <strong>Mẫu khảo sát đang bị khóa.</strong> Mẫu này đang được sử dụng trong một đợt khảo sát đang hoạt động.
                    <br>
                    Một số chức năng chỉnh sửa nội dung đã bị vô hiệu hóa để đảm bảo tính toàn vẹn dữ liệu.
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Form Thông tin mẫu -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin mẫu khảo sát</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.mau-khao-sat.update', $mauKhaoSat) }}"
                            id="formUpdateMau">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Tên mẫu khảo sát <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ten_mau') is-invalid @enderror"
                                        name="ten_mau" value="{{ old('ten_mau', $mauKhaoSat->ten_mau) }}" required
                                        @if($isLocked) disabled @endif>
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
                                <textarea class="form-control @error('mota') is-invalid @enderror" name="mota" rows="3"
                                    @if($isLocked) disabled @endif>{{ old('mota', $mauKhaoSat->mota) }} </textarea>
                                @error('mota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu thay
                                    đổi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card Câu hỏi Thông tin cá nhân -->
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i> Câu hỏi Thông tin cá nhân
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="showModalThemCauHoi(true)" @if($isLocked)
                        disabled @endif>
                            <i class="bi bi-plus"></i> Thêm
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- {{ $isLocked ? '' : 'sortable' }} //nếu lock không cho sắp xếp  -->
                         <div id="personal-questions-list" class="sortable" 
                            data-list-type="personal">
                            {{-- JS sẽ render nội dung ở đây --}}
                        </div>
                    </div>
                </div>

                <!-- Card Câu hỏi Nội dung khảo sát -->
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-patch-question-fill text-primary me-2"></i> Câu hỏi Nội dung Khảo
                            sát</h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="showModalThemCauHoi(false)" @if($isLocked) disabled
                        @endif>
                            <i class="bi bi-plus"></i> Thêm
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="survey-questions-list" class="{{ $isLocked ? '' : 'sortable' }}" data-list-type="survey">
                            <div id="questions-loading" class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
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
                                    <span class="badge bg-info"
                                        id="question-count">{{ $mauKhaoSat->cauHoi->count() ?? 0 }}</span>
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
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Lưu ý</strong>
                                <br>
                                Câu hỏi có điều kiện hiển thị cần cùng trang với câu hỏi điều kiện.
                            </div>
                        </div>
                        @if(isset($mauKhaoSat->dotKhaoSat) && $mauKhaoSat->dotKhaoSat->isNotEmpty())
                            @php
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
                        <!-- <input type="hidden" id="isPersonalInfo"> -->
                        <div class="mb-3">
                            <label for="noiDungCauHoi" class="form-label">Nội dung câu hỏi <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="noiDungCauHoi" rows="2" required
                                placeholder="Nhập nội dung câu hỏi..."></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="loaiCauHoi" class="form-label">Loại câu hỏi</label>
                                <select class="form-select" id="loaiCauHoi" onchange="togglePhuongAnContainer()">
                                    <option value="single_choice">Chọn một</option>
                                    <option value="multiple_choice">Chọn nhiều</option>
                                    <option value="text">Văn bản</option>
                                    <option value="likert">Thang đo Likert</option>
                                    <option value="rating">Đánh giá (1-5 sao)</option>
                                    <option value="date">Ngày tháng</option>
                                    <option value="number">Số</option>
                                    <option value="select_ctdt">Chọn chương trình đào tạo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="pageNumber" class="form-label">Trang số</label>
                                <input type="number" class="form-control" id="pageNumber" value="1" min="1">
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
                            <div id="danhSachPhuongAn"></div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="btnAddPhuongAn"
                                onclick="addPhuongAn()">
                                <i class="bi bi-plus"></i> Thêm phương án
                            </button>
                        </div>

                        <!-- điều kiện hiển thị -->
                        <div id="conditional-logic-container">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="enableConditionalLogic">
                                <label class="form-check-label" for="enableConditionalLogic">
                                    <strong>Bật điều kiện hiển thị</strong>
                                    <small class="d-block text-muted">Chỉ hiển thị câu hỏi này khi một câu trả lời khác được
                                        chọn.</small>
                                    <small class="d-block text-muted">Chỉ những câu hỏi có lựa chọn mới có thể làm câu hỏi
                                        điều kiện (single_choice, likert, rating).</small>
                                </label>
                            </div>
                            <div id="conditional-rules" class="p-3 border rounded bg-light" style="display: none;">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Nếu câu hỏi:</label>
                                        <select class="form-select form-select-sm" id="parentQuestion">
                                            <option value="">-- Chọn câu hỏi điều kiện --</option>
                                            @foreach($conditionalQuestions as $q)
                                                <option value="{{ $q->id }}"
                                                    data-options="{{ json_encode($q->phuongAnTraLoi) }}"
                                                    data-type="{{ $q->loai_cauhoi }}">
                                                    Câu: {{ Str::limit($q->noidung_cauhoi, 40) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="fw-bold mt-4">LÀ</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Phương án trả lời:</label>
                                        <select class="form-select form-select-sm" id="parentAnswer">
                                            {{-- Options sẽ được JavaScript điền vào --}}
                                        </select>
                                    </div>
                                </div>
                            </div>
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
    </form>
    <form id="formDeleteMau" action="{{ route('admin.mau-khao-sat.destroy', $mauKhaoSat) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        const mauKhaoSatId = {{ $mauKhaoSat->id }};
        const isLocked = {{ $isLocked ? 'true' : 'false' }};
        const conditionalQuestionsData = @json($conditionalQuestions);
    </script>
    <script type="module">
        import Sortable from 'https://cdn.jsdelivr.net/npm/sortablejs@latest/modular/sortable.esm.js';

        // === STATE MANAGEMENT ===
        let allQuestionsData = []; // Nguồn dữ liệu duy nhất
        const modalCauHoi = new bootstrap.Modal(document.getElementById('modalCauHoi'));

        // === HELPER FUNCTIONS ===
        function escapeHtml(text) {
            if (typeof text !== 'string') return '';
            var map = {
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function (m) { return map[m]; });
        }
        function getQuestionTypeName(type) {
            const names = {
                'single_choice': 'Chọn một',
                'multiple_choice': 'Chọn nhiều',
                'text': 'Văn bản',
                'likert': 'Thang đo Likert',
                'rating': 'Đánh giá',
                'date': 'Ngày tháng',
                'number': 'Số',
                'select_ctdt': 'Chọn chương trình đào tạo'
            };
            return names[type] || type;
        }
        function Str_limit(text, limit) {
            if (typeof text !== 'string') return '';
            return text.length > limit ? text.substring(0, limit) + '...' : text;
        }

        // === CORE FUNCTIONS ===
        function renderAllLists() {
            const personalList = $('#personal-questions-list');
            const surveyList = $('#survey-questions-list');
            personalList.empty();
            surveyList.empty();

            const personalQuestions = allQuestionsData.filter(q => q.is_personal_info).sort((a, b) => a.thutu - b.thutu);
            const surveyQuestions = allQuestionsData.filter(q => !q.is_personal_info).sort((a, b) => a.thutu - b.thutu);

            $('#question-count').text(allQuestionsData.length);

            if (personalQuestions.length === 0)
                personalList.html(`<div class="text-center text-muted p-3">Chưa có câu hỏi thông tin.</div>`);
            if (surveyQuestions.length === 0)
                surveyList.html(`<div class="text-center text-muted p-3">Chưa có câu hỏi khảo sát.</div>`);

            personalQuestions.forEach((q, i) => personalList.append(createQuestionHtml(q, i + 1)));
            surveyQuestions.forEach((q, i) => surveyList.append(createQuestionHtml(q, i + 1)));
        }

        function createQuestionHtml(cauHoi, stt) {
            let optionsHtml = '';
            if (['single_choice', 'multiple_choice', 'likert'].includes(cauHoi.loai_cauhoi) && (cauHoi.phuong_an_tra_loi?.length > 0)) {
                optionsHtml = '<ol class="mb-0 ps-3 small text-muted">';
                cauHoi.phuong_an_tra_loi.forEach(pa => {
                    optionsHtml += `<li>${escapeHtml(pa.noidung)}</li>`;
                });
                optionsHtml += '</ol>';
            }
            let extraInfoHtml = `
                                    <div class="mt-2 d-flex align-items-center gap-3 small text-muted">
                                        <div>
                                            <i class="bi bi-file-earmark-break me-1"></i>
                                            Trang: <strong>${cauHoi.page || 1}</strong>
                                        </div>
                                `;
            if (cauHoi.cau_dieukien_id && cauHoi.dieukien_hienthi) {
                try {
                    const condition = typeof cauHoi.dieukien_hienthi === 'string' ? JSON.parse(cauHoi.dieukien_hienthi) : cauHoi.dieukien_hienthi;
                    const parentQ = (conditionalQuestionsData || []).find(q => q.id == cauHoi.cau_dieukien_id);
                    const parentQuestionText = parentQ ? parentQ.noidung_cauhoi : `Câu hỏi #${cauHoi.cau_dieukien_id}`;
                    let conditionText = `<strong>${escapeHtml(condition.value)}</strong>`;
                    if (cauHoi.phuong_an_tra_loi_cha && cauHoi.phuong_an_tra_loi_cha.length > 0) {
                        const parentAnswer = cauHoi.phuong_an_tra_loi_cha.find(pa => pa.id == condition.value);
                        if (parentAnswer) {
                            conditionText = `<strong>"${escapeHtml(parentAnswer.noidung)}"</strong>`;
                        }
                    }
                    extraInfoHtml += `
                                            <div class="border-start ps-3">
                                                <i class="bi bi-magic me-1 text-info"></i>
                                                Hiện khi: <em>${escapeHtml(Str_limit(parentQuestionText, 25))}</em> là ${conditionText}
                                            </div>
                                        `;
                } catch (e) { console.log(e) }
            }
            extraInfoHtml += `</div>`;
            let buttons = '';
            if (!isLocked) {
                buttons = `
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary handle" title="Kéo để sắp xếp"><i class="bi bi-grip-vertical"></i></button>
                                            <button class="btn btn-outline-primary" onclick="showModalSuaCauHoi(${cauHoi.id})" title="Sửa"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-outline-danger" onclick="deleteCauHoi(${cauHoi.id})" title="Xóa"><i class="bi bi-trash"></i></button>
                                        </div>
                                    `;
            } else {
                buttons = `
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary handle" title="Kéo để sắp xếp"><i class="bi bi-grip-vertical"></i></button>
                                        </div>
                                    `;
            }
            return `
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
                                                    ${extraInfoHtml}
                                                </div>
                                                ${buttons}
                                            </div>
                                        </div>
                                    </div>
                                `;
        }

        function loadInitialQuestions() {
            $('#questions-loading').show();
            // $.get("{{-- route('admin.mau-khao-sat.questions', $mauKhaoSat->id) --}}")
            $.get("/admin/mau-khao-sat/{{ $mauKhaoSat->id }}/questions")
                .done(data => {
                    allQuestionsData = data;
                    renderAllLists();
                    $('#questions-loading').hide();
                })
                .fail(() => {
                    $('#personal-questions-list, #survey-questions-list').html('<div class="alert alert-danger">Không thể tải danh sách câu hỏi.</div>');
                });
        }

        // === MODAL HANDLING ===
        window.showModalThemCauHoi = function (isPersonalInfo = false) {
            $('#modalTitle').text('Thêm câu hỏi mới');
            $('#formCauHoi')[0].reset();
            $('#cauHoiId').val('');
            $('#validation-errors').addClass('d-none').html('');
            $('#formCauHoi').data('is-personal-info', !!isPersonalInfo);
            // $('#isPersonalInfo').prop('checked', isPersonalInfo);

            $('#enableConditionalLogic').prop('checked', false).trigger('change');
            $('#parentAnswer').html('');

            togglePhuongAnContainer();
            modalCauHoi.show();
        }

        window.showModalSuaCauHoi = function (cauHoiId) {
            if (isLocked) return alert('Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
            const cauHoi = allQuestionsData.find(q => q.id === cauHoiId);
            if (!cauHoi) return alert('Không tìm thấy dữ liệu câu hỏi');
            $('#modalTitle').text('Sửa câu hỏi');
            $('#formCauHoi')[0].reset();
            $('#validation-errors').addClass('d-none').html('');
            $('#cauHoiId').val(cauHoi.id);
            $('#formCauHoi').data('is-personal-info', !!cauHoi.is_personal_info);
            // $('#isPersonalInfo').prop('checked', !!cauHoi.is_personal_info);

            $('#noiDungCauHoi').val(cauHoi.noidung_cauhoi);
            $('#loaiCauHoi').val(cauHoi.loai_cauhoi);
            $('#pageNumber').val(cauHoi.page || 1);
            $('#batBuoc').prop('checked', !!cauHoi.batbuoc);

            // phương án trả lời
            const phuongAnContainer = $('#danhSachPhuongAn');
            phuongAnContainer.html('');
            const loaiHienTai = $('#loaiCauHoi').val();
            const container = $('#phuongAnContainer');
            if (['single_choice', 'multiple_choice', 'likert'].includes(loaiHienTai)) {
                container.show();
                if (cauHoi.phuong_an_tra_loi && cauHoi.phuong_an_tra_loi.length > 0) {
                    const isLikert = loaiHienTai === 'likert';
                    cauHoi.phuong_an_tra_loi
                        .sort((a, b) => a.thutu - b.thutu)
                        .forEach(pa => addPhuongAn(pa.noidung, isLikert));
                } else {
                    if (loaiHienTai === 'likert') {
                        const likertOptions = ['Rất không hài lòng', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
                        likertOptions.forEach(option => addPhuongAn(option, true));
                    } else {
                        addPhuongAn();
                        addPhuongAn();
                    }
                }
            } else {
                container.hide();
            }

            if (cauHoi.cau_dieukien_id && cauHoi.dieukien_hienthi) {
                $('#enableConditionalLogic').prop('checked', true).trigger('change');
                $('#parentQuestion').val(cauHoi.cau_dieukien_id).trigger('change');
                setTimeout(() => {
                    const condition = typeof cauHoi.dieukien_hienthi === 'string' ? JSON.parse(cauHoi.dieukien_hienthi) : cauHoi.dieukien_hienthi;
                    $('#parentAnswer').val(condition.value);
                }, 200);
            } else {
                $('#enableConditionalLogic').prop('checked', false).trigger('change');
            }

            modalCauHoi.show();
        }

        // === PHƯƠNG ÁN TRẢ LỜI functions (giữ nguyên) ===
        window.togglePhuongAnContainer = function () {
            const loai = $('#loaiCauHoi').val();
            const container = $('#phuongAnContainer');
            const isChoiceType = ['single_choice', 'multiple_choice', 'likert'].includes(loai);

            if (isChoiceType) {
                $('#danhSachPhuongAn').empty();
                container.show();
                if (loai === 'likert') {
                    const likertOptions = ['Rất không hài lòng', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
                    likertOptions.forEach(option => addPhuongAn(option, true));
                } else {
                    addPhuongAn();
                    addPhuongAn();
                }
            } else {
                container.hide();
            }
        }

        window.addPhuongAn = function (value = '', isReadonly = false) {
            const count = $('#danhSachPhuongAn .input-group').length + 1;
            const readonlyAttr = isReadonly ? 'readonly' : '';
            const html = `<div class="input-group mb-2"><span class="input-group-text">${count}</span><input type="text" class="form-control phuong-an" value="${value}" ${readonlyAttr}><button class="btn btn-outline-danger" type="button" onclick="removePhuongAn(this)"><i class="bi bi-trash"></i></button></div>`;
            $('#danhSachPhuongAn').append(html);
        }
        window.removePhuongAn = function (btn) {
            if ($('#danhSachPhuongAn .input-group').length > 2) {
                $(btn).closest('.input-group').remove();
                $('#danhSachPhuongAn .input-group').each(function (index) {
                    $(this).find('.input-group-text').text(index + 1);
                });
            } else {
                alert('Phải có ít nhất 2 phương án trả lời.');
            }
        }

        // === SAVE/DELETE ACTION ===
        window.saveCauHoi = function (event) {
            event.preventDefault();
            const btn = $('#btnSaveCauHoi');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');

            const cauHoiId = $('#cauHoiId').val();
            const data = {
                noidung_cauhoi: $('#noiDungCauHoi').val(),
                loai_cauhoi: $('#loaiCauHoi').val(),
                page: $('#pageNumber').val() || 1,
                batbuoc: $('#batBuoc').is(':checked') ? 1 : 0,
                phuong_an: [],
                is_personal_info: $('#formCauHoi').data('is-personal-info') ? 1 : 0,
            };
            $('.phuong-an').each(function () {
                if ($(this).val().trim() !== '') data.phuong_an.push($(this).val().trim());
            });
            if ($('#enableConditionalLogic').is(':checked') && $('#parentQuestion').val() && $('#parentAnswer').val()) {
                data.cau_dieukien_id = $('#parentQuestion').val();
                data.dieukien_hienthi = JSON.stringify({
                    value: $('#parentAnswer').val()
                });
            } else {
                data.cau_dieukien_id = null;
                data.dieukien_hienthi = null;
            }
            const url = cauHoiId ? `/admin/cau-hoi/${cauHoiId}` : `/admin/mau-khao-sat/{{ $mauKhaoSat->id }}/cau-hoi`;
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

        window.deleteCauHoi = function (id) {
            if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) return;
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
                return;
            }
            $.ajax({
                url: `{{ url('admin/cau-hoi') }}/${id}`, method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    allQuestionsData = allQuestionsData.filter(q => q.id !== id);
                    renderAllLists();
                },
                error: function (xhr) { alert('Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại')); }
            });
        }

        window.copyMauKhaoSat = function () {
            if (confirm('Bạn có chắc chắn muốn sao chép mẫu này?')) $('#formCopyMau').submit();
        }
        window.deleteMauKhaoSat = function () {
            if (confirm('Bạn có chắc chắn muốn xóa mẫu khảo sát này? Hành động này không thể hoàn tác!')) $('#formDeleteMau').submit();
        }

        $('#enableConditionalLogic').on('change', function () {
            $('#conditional-rules').toggle(this.checked);
        });

        $('#parentQuestion').on('change', function () {
            const selectedOption = $(this).find('option:selected');
            const parentAnswerSelect = $('#parentAnswer');
            parentAnswerSelect.html('<option value="">-- Chọn phương án --</option>'); // Reset

            const questionType = selectedOption.data('type');

            if (questionType === 'rating') {
                for (let i = 1; i <= 5; i++) {
                    parentAnswerSelect.append(`<option value="${i}">${i} sao</option>`);
                }
            } else {
                const options = selectedOption.data('options');
                if (options && Array.isArray(options)) {
                    options.sort((a, b) => a.thutu - b.thutu).forEach(function (opt) {
                        parentAnswerSelect.append(`<option value="${opt.id}">${escapeHtml(opt.noidung)}</option>`);
                    });
                }
            }
        });

        // === SORTABLE ===
        function initializeSortable() {
            document.querySelectorAll('.sortable').forEach(el => {
                Sortable.create(el, {
                    handle: '.handle', animation: 150, ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        const listType = $(el).data('list-type');
                        const questionsInList = (listType === 'personal') ? allQuestionsData.filter(q => q.is_personal_info) : allQuestionsData.filter(q => !q.is_personal_info);
                        const order = Array.from(el.children).map(item => parseInt(item.dataset.id));

                        // Cập nhật lại thứ tự trong mảng JS trước
                        order.forEach((id, index) => {
                            const question = questionsInList.find(q => q.id === id);
                            if (question) question.thutu = index + 1;
                        });

                        renderAllLists();

                        // Gửi thứ tự mới lên server
                        $.ajax({
                            url: "/admin/cau-hoi/update-order",
                            method: 'POST', data: { order: order },
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            error: () => alert('Lỗi khi lưu thứ tự, vui lòng tải lại trang.'),
                        });
                    },
                });
            });
        }

        // --- Điểm khởi chạy chính ---
        $(document).ready(function () {
            loadInitialQuestions();
            initializeSortable();
        });
    </script>
@endpush