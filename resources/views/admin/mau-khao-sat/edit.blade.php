@extends('layouts.admin')

@section('title', 'Chỉnh sửa mẫu khảo sát')

@push('styles')
    <style>
        /* Question Palette Sidebar */
        .question-palette {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 20px;
        }
        
        /* Fixed state when scrolling */
        .question-palette.palette-fixed {
            position: fixed !important;
            top: 20px !important;
            max-height: calc(100vh - 40px) !important;
            overflow-y: auto !important;
            z-index: 100 !important;
        }

        .palette-header {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }

        .question-type-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: grab;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .question-type-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .question-type-item:active {
            cursor: grabbing;
        }

        .question-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }

        .question-type-info {
            flex: 1;
        }

        .question-type-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0;
        }

        .question-type-desc {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
        }

        /* Question Type Colors */
        .qtype-single_choice .question-type-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .qtype-multiple_choice .question-type-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .qtype-text .question-type-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .qtype-likert .question-type-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .qtype-rating .question-type-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .qtype-date .question-type-icon { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        .qtype-number .question-type-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .qtype-custom_select .question-type-icon { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

        /* Page Container Styles */
        .page-container {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 0;
            transition: all 0.3s ease;
        }

        .page-container.drag-over {
            border-color: #0dcaf0;
            background: #e7f6f9;
            box-shadow: 0 0 15px rgba(13, 202, 240, 0.3);
        }

        .page-header {
            background: #56CCF2;
            color: white;
            padding: 12px 20px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .page-body {
            padding: 20px;
            min-height: 120px;
            position: relative;
            transition: all 0.3s ease;
        }

        .page-body.empty {
            background: #fff;
            border: 2px dashed #dee2e6;
            border-radius: 6px;
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }

        .page-body.empty::before {
            content: "Kéo loại câu hỏi từ bảng bên phải vào đây";
            display: block;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .page-body.drag-over {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        /* Question Item Styles */
        .question-item {
            cursor: grab;
            transition: all 0.2s ease;
        }

        .question-item:active {
            cursor: grabbing;
        }

        .question-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Sortable Drag States */
        .sortable-ghost {
            opacity: 0.4;
            background: #e3f2fd;
        }

        .sortable-drag {
            opacity: 0.8;
            transform: rotate(3deg);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .sortable-chosen {
            background: #f0f9ff;
        }

        /* Handle Styles */
        .handle {
            cursor: move;
            color: #6c757d;
            transition: color 0.2s;
        }

        .handle:hover {
            color: #0d6efd;
        }

        /* Inline Form Styles */
        .question-form-inline {
            background: #fff;
            border: 2px solid #0d6efd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .question-form-inline .form-label {
            font-weight: 600;
            margin-bottom: 5px;
        }

        /* Personal Questions List */
        #personal-questions-list {
            min-height: 100px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        #personal-questions-list.drag-over {
            background: #e3f2fd;
            border: 2px dashed #2196f3;
            border-radius: 6px;
        }
        
        /* Force proper scrolling */
        body, html {
            overflow-y: auto !important;
            height: auto !important;
        }
        
        .container-fluid {
            overflow: visible !important;
        }
        
        /* Fix parent containers for sticky to work */
        .row {
            overflow: visible !important;
        }
        
        .col-lg-4 {
            position: static !important;
            overflow: visible !important;
        }
        
        .col-lg-8 {
            position: static !important;
        }
        
        #main-content, #content, #wrapper {
            overflow-x: hidden !important;
            overflow-y: visible !important;
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

        <!-- Form Thông tin mẫu - Full width, collapsible -->
        <div class="card shadow mb-4">
            <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#surveyInfoCollapse">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin mẫu khảo sát</h5>
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="surveyInfoCollapse">
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
                            <textarea class="form-control @error('mota') is-invalid @enderror" name="mota" rows="2"
                                @if($isLocked) disabled @endif>{{ old('mota', $mauKhaoSat->mota) }}</textarea>
                            @error('mota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thông tin và Thao tác -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow">
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
                                    <span class="badge bg-info" id="question-count">{{ $mauKhaoSat->cauHoi->count() ?? 0 }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Người tạo:</td>
                                <td>{{ $mauKhaoSat->nguoiTao->hoten ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Ngày tạo:</td>
                                <td>{{ $mauKhaoSat->created_at ? $mauKhaoSat->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cập nhật:</td>
                                <td>{{ $mauKhaoSat->updated_at ? $mauKhaoSat->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                        @if(isset($mauKhaoSat->dotKhaoSat) && $mauKhaoSat->dotKhaoSat->isNotEmpty())
                            @php
                                $activeCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'active')->count();
                                $draftCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'draft')->count();
                                $closedCount = $mauKhaoSat->dotKhaoSat->where('trangthai', 'closed')->count();
                                $totalCount = $mauKhaoSat->dotKhaoSat->count();
                            @endphp
                            <div class="alert alert-info">
                                <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle-fill"></i> Tình trạng sử dụng</h6>
                                <p class="mb-2">Mẫu khảo sát này đang được sử dụng trong tổng cộng <strong>{{ $totalCount }}</strong> đợt khảo sát:</p>
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
            </div>
            <div class="col-md-6">
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
                                <a href="{{ route('admin.dot-khao-sat.create') }}?mau_khaosat_id={{ $mauKhaoSat->id }}" class="btn btn-success">
                                    <i class="bi bi-calendar-plus"></i> Tạo đợt khảo sát
                                </a>
                            @endif
                            @if(($mauKhaoSat->dotKhaoSat->count() ?? 0) == 0)
                                <button type="button" class="btn btn-danger" onclick="deleteMauKhaoSat()">
                                    <i class="bi bi-trash"></i> Xóa mẫu này
                                </button>
                            @endif
                        </div>
                        <div class="alert alert-info d-flex align-items-center mt-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Lưu ý</strong>
                                <br>
                                Câu hỏi có điều kiện hiển thị cần cùng trang với câu hỏi điều kiện.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Card Câu hỏi Thông tin cá nhân -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i> Câu hỏi Thông tin cá nhân
                        </h5>
                    </div>
                    <div class="card-body">
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
                    </div>
                    <div class="card-body">
                        <div id="survey-questions-container">
                            <div id="questions-loading" class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Question Palette Sidebar -->
                <div class="question-palette" @if($isLocked) style="display: none;" @endif>
                    <div class="palette-header">
                        <i class="bi bi-palette me-2"></i>Loại Câu Hỏi
                    </div>
                    
                    <div class="question-type-item qtype-single_choice" draggable="true" data-question-type="single_choice">
                        <div class="question-type-icon">
                            <i class="bi bi-ui-radios"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Chọn một</p>
                            <p class="question-type-desc">Radio buttons</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-multiple_choice" draggable="true" data-question-type="multiple_choice">
                        <div class="question-type-icon">
                            <i class="bi bi-ui-checks"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Chọn nhiều</p>
                            <p class="question-type-desc">Checkboxes</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-text" draggable="true" data-question-type="text">
                        <div class="question-type-icon">
                            <i class="bi bi-input-cursor-text"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Văn bản</p>
                            <p class="question-type-desc">Text input</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-likert" draggable="true" data-question-type="likert">
                        <div class="question-type-icon">
                            <i class="bi bi-bar-chart-steps"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Thang Likert</p>
                            <p class="question-type-desc">5-point scale</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-rating" draggable="true" data-question-type="rating">
                        <div class="question-type-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Đánh giá</p>
                            <p class="question-type-desc">Star rating 1-5</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-date" draggable="true" data-question-type="date">
                        <div class="question-type-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Ngày tháng</p>
                            <p class="question-type-desc">Date picker</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-number" draggable="true" data-question-type="number">
                        <div class="question-type-icon">
                            <i class="bi bi-123"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Số</p>
                            <p class="question-type-desc">Number input</p>
                        </div>
                    </div>

                    <div class="question-type-item qtype-custom_select" draggable="true" data-question-type="custom_select">
                        <div class="question-type-icon">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div class="question-type-info">
                            <p class="question-type-name">Danh sách</p>
                            <p class="question-type-desc">Từ nguồn dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Logic Điều Kiện -->
    <div class="modal fade" id="conditionalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-magic me-2"></i>Cài đặt Logic Điều Kiện</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="conditionalQuestionId">
                    <div id="conditional-logic-container">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="enableConditionalLogic">
                            <label class="form-check-label" for="enableConditionalLogic">
                                <strong>Bật điều kiện hiển thị</strong>
                                <small class="d-block text-muted">Chỉ hiển thị câu hỏi này khi một câu trả lời khác được chọn.</small>
                                <small class="d-block text-muted">Chỉ những câu hỏi có lựa chọn mới có thể làm câu hỏi điều kiện (single_choice, multiple_choice, likert, rating).</small>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveConditionalLogic()">Lưu</button>
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
        
        // Handle palette scroll behavior
        document.addEventListener('DOMContentLoaded', function() {
            const palette = document.querySelector('.question-palette');
            if (!palette) return;
            
            // Get initial position and dimensions
            const paletteParent = palette.parentElement;
            let paletteOffsetTop = 0;
            let paletteWidth = 0;
            let paletteLeft = 0;
            
            function updatePalettePosition() {
                if (!palette.classList.contains('palette-fixed')) {
                    const rect = palette.getBoundingClientRect();
                    const parentRect = paletteParent.getBoundingClientRect();
                    paletteOffsetTop = rect.top + window.pageYOffset;
                    paletteWidth = rect.width;
                    paletteLeft = rect.left;
                }
            }
            
            // Initial measurement
            updatePalettePosition();
            
            // Handle scroll
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > paletteOffsetTop - 100) {
                    if (!palette.classList.contains('palette-fixed')) {
                        palette.classList.add('palette-fixed');
                        palette.style.width = paletteWidth + 'px';
                        palette.style.left = paletteLeft + 'px';
                    }
                } else {
                    if (palette.classList.contains('palette-fixed')) {
                        palette.classList.remove('palette-fixed');
                        palette.style.width = '';
                        palette.style.left = '';
                    }
                }
            });
            
            // Update on resize
            window.addEventListener('resize', updatePalettePosition);
        });
    
    </script>
    <script type="module">
        import Sortable from 'https://cdn.jsdelivr.net/npm/sortablejs@latest/modular/sortable.esm.js';

        // === STATE MANAGEMENT ===
        let allQuestionsData = []; // Nguồn dữ liệu duy nhất

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
                'custom_select': 'Lựa chọn từ danh sách'
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
            const surveyContainer = $('#survey-questions-container');
            personalList.empty();
            surveyContainer.empty();

            const personalQuestions = allQuestionsData.filter(q => q.is_personal_info).sort((a, b) => a.thutu - b.thutu);
            const surveyQuestions = allQuestionsData.filter(q => !q.is_personal_info).sort((a, b) => a.thutu - b.thutu);

            $('#question-count').text(allQuestionsData.length);

            // Render Personal Info Questions
            personalList.empty();
            if (personalQuestions.length === 0) {
                personalList.html(`
                    <div id="personal-placeholder" class="text-center text-muted p-4">
                        <i class="bi bi-arrow-left-right fs-1 mb-2 d-block opacity-25"></i>
                        <p class="mb-0">Kéo loại câu hỏi từ bên phải vào đây</p>
                    </div>
                `);
            } else {
                personalQuestions.forEach((q, i) => personalList.append(createQuestionHtml(q, i + 1)));
            }

            // Render Survey Questions grouped by Page
            surveyContainer.html(''); // Clear container
            
            // Group questions by page
            const questionsByPage = {};
            surveyQuestions.forEach(q => {
                const page = q.page || 1;
                if (!questionsByPage[page]) {
                    questionsByPage[page] = [];
                }
                questionsByPage[page].push(q);
            });

            // Ensure at least page 1 exists (even if empty)
            if (Object.keys(questionsByPage).length === 0) {
                questionsByPage[1] = [];
            }

            // Sort pages numerically
            const sortedPages = Object.keys(questionsByPage).sort((a, b) => parseInt(a) - parseInt(b));

            // Render each page
            sortedPages.forEach(pageNum => {
                const questions = questionsByPage[pageNum];
                const pageHtml = createPageContainerHtml(pageNum, questions);
                surveyContainer.append(pageHtml);
            });

            // Re-initialize sortable after rendering
            initializeSortable();
            
            // Setup drag-and-drop from palette
            checkAndAddEmptyPage();
            setupPaletteDragHandlers();
            setupPageDropZones();
        }

        function createPageContainerHtml(pageNum, questions) {
            const questionCount = questions.length;
            const isEmpty = questionCount === 0;
            
            let questionsHtml = '';
            if (isEmpty) {
                questionsHtml = '<div class="text-center text-muted">Chưa có câu hỏi trong trang này</div>';
            } else {
                questions.forEach((q, idx) => {
                    questionsHtml += createQuestionHtml(q, idx + 1);
                });
            }

            return `
                <div class="page-container" data-page="${pageNum}">
                    <div class="page-header">
                        <div class="page-title">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Trang ${pageNum}
                            <span class="badge bg-light text-dark ms-2">${questionCount} câu hỏi</span>
                        </div>
                    </div>
                    <div class="page-body ${isEmpty ? 'empty' : ''} sortable-page" data-page="${pageNum}">
                        ${questionsHtml}
                    </div>
                </div>
            `;
        }

        function createQuestionHtml(cauHoi, stt) {
            let duplicateCheckHtml = (cauHoi.check_duplicate == true || cauHoi.check_duplicate == 1 || cauHoi.check_duplicate === '1') 
                ? `<span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Kiểm tra trùng lặp</span>`
                : '';
            
            let optionsHtml = '';
            if (
                ['single_choice', 'multiple_choice', 'likert'].includes(cauHoi.loai_cauhoi)
                && (cauHoi.phuong_an_tra_loi?.length > 0)
            ) {
                optionsHtml = '<ol class="mb-0 ps-3 small text-muted">';
                cauHoi.phuong_an_tra_loi.forEach(pa => {
                    optionsHtml += `<li>${escapeHtml(pa.noidung)}</li>`;
                });
                optionsHtml += '</ol>';
            }
            let extraInfoHtml = `
                                    <div class="mt-2 d-flex align-items-center gap-3 small text-muted">
                                        ${duplicateCheckHtml}
                                `;
            if (cauHoi.loai_cauhoi === 'custom_select' && cauHoi.data_source) {
                 extraInfoHtml += `<div class="border-start ps-3"><i class="bi bi-database me-1"></i>Nguồn: <strong>${cauHoi.data_source.name}</strong></div>`;
            }
            if (cauHoi.cau_dieukien_id && cauHoi.dieukien_hienthi) {
                try {
                    const condition = typeof cauHoi.dieukien_hienthi === 'string' ? JSON.parse(cauHoi.dieukien_hienthi) : cauHoi.dieukien_hienthi;
                    const parentQ = allQuestionsData.find(q => q.id == cauHoi.cau_dieukien_id);
                    const parentQuestionText = parentQ ? parentQ.noidung_cauhoi : `Câu hỏi #${cauHoi.cau_dieukien_id}`;
                    let conditionText = `<strong>${escapeHtml(condition.value)}</strong>`;
                    
                    // Try to find answer text from parent question's options
                    if (parentQ && parentQ.phuong_an_tra_loi && parentQ.phuong_an_tra_loi.length > 0) {
                        const parentAnswer = parentQ.phuong_an_tra_loi.find(pa => pa.noidung == condition.value);
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
                                            <button class="btn btn-outline-primary" onclick="editQuestionInline(${cauHoi.id})" title="Sửa"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-outline-info" onclick="showConditionalModal(${cauHoi.id})" title="Logic điều kiện"><i class="bi bi-magic"></i></button>
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

        // === INLINE EDITING (Temporary stubs - will be implemented fully) ===
        window.addNewPersonalQuestion = function() {
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa, không thể thêm câu hỏi mới.');
                return;
            }
            alert('Tính năng thêm câu hỏi inline đang được phát triển.');
        }
        
        
        window.editQuestionInline = function(questionId) {
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
                return;
            }

            // Find question data
            const question = allQuestionsData.find(q => q.id === questionId);
            if (!question) {
                alert('Không tìm thấy câu hỏi!');
                return;
            }

            // Remove any existing edit forms
            document.querySelectorAll('.question-form-inline').forEach(f => f.remove());

            // Hide the question card
            const questionCard = document.querySelector(`.question-item[data-id="${questionId}"]`);
            if (!questionCard) return;
            
            questionCard.style.display = 'none';

            const formId = 'edit-form-' + questionId;
            const questionType = question.loai_cauhoi;
            const needsOptions = ['single_choice', 'multiple_choice', 'likert'].includes(questionType);
            const needsDataSource = questionType === 'custom_select';
            
            let optionsHtml = '';
            if (needsOptions) {
                if (questionType === 'likert') {
                    optionsHtml = `
                        <div class="mb-3">
                            <label class="form-label">Phương án (Likert)</label>
                            <div class="small text-muted">Thang đo Likert chuẩn 5 mức</div>
                        </div>
                    `;
                } else {
                    optionsHtml = '<div class="mb-3"><label class="form-label">Phương án trả lời <span class="text-danger">*</span></label><div id="' + formId + '-options">';
                    
                    if (question.phuong_an_tra_loi && question.phuong_an_tra_loi.length > 0) {
                        question.phuong_an_tra_loi.forEach((opt, idx) => {
                            optionsHtml += `
                                <div class="input-group mb-2">
                                    <span class="input-group-text">${idx + 1}</span>
                                    <input type="text" class="form-control option-input" value="${escapeHtml(opt.noidung)}">
                                    <button class="btn btn-outline-danger" type="button" onclick="removeEditOption(this, '${formId}')"><i class="bi bi-trash"></i></button>
                                </div>
                            `;
                        });
                    } else {
                        optionsHtml += `
                            <div class="input-group mb-2"><span class="input-group-text">1</span><input type="text" class="form-control option-input"></div>
                            <div class="input-group mb-2"><span class="input-group-text">2</span><input type="text" class="form-control option-input"></div>
                        `;
                    }
                    
                    optionsHtml += '</div><button type="button" class="btn btn-sm btn-secondary" onclick="addOptionToForm(\'' + formId + '\')"><i class="bi bi-plus"></i> Thêm phương án</button></div>';
                }
            }

            let dataSourceHtml = '';
            if (needsDataSource) {
                dataSourceHtml = `
                    <div class="mb-3">
                        <label class="form-label">Nguồn dữ liệu <span class="text-danger">*</span></label>
                        <select class="form-select" id="${formId}-datasource">
                            <option value="">-- Chọn nguồn dữ liệu --</option>
                            @foreach($dataSources as $ds)
                                <option value="{{ $ds->id }}" ${question.data_source_id == {{ $ds->id }} ? 'selected' : ''}>{{ $ds->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            }

            const formHtml = `
                <div class="question-form-inline" id="${formId}">
                    <h6 class="mb-3">
                        <i class="bi bi-pencil me-2"></i>
                        Sửa câu hỏi: ${getQuestionTypeName(questionType)}
                    </h6>
                    <div class="mb-3">
                        <label class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="${formId}-content" rows="2">${escapeHtml(question.noidung_cauhoi)}</textarea>
                    </div>
                    ${optionsHtml}
                    ${dataSourceHtml}
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${formId}-required" ${question.batbuoc ? 'checked' : ''}>
                            <label class="form-check-label" for="${formId}-required">
                                Bắt buộc trả lời
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${formId}-checkdup" ${question.check_duplicate ? 'checked' : ''}>
                            <label class="form-check-label" for="${formId}-checkdup">
                                Kiểm tra trùng lặp
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveEditedQuestion(${questionId}, '${formId}', '${questionType}')">
                            <i class="bi bi-save"></i> Lưu thay đổi
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit(${questionId}, '${formId}')">
                            <i class="bi bi-x"></i> Hủy
                        </button>
                    </div>
                </div>
            `;

            questionCard.insertAdjacentHTML('afterend', formHtml);
            document.getElementById(`${formId}-content`).focus();
        }

        window.removeEditOption = function(btn, formId) {
            const container = document.getElementById(`${formId}-options`);
            if (container.querySelectorAll('.input-group').length > 2) {
                btn.closest('.input-group').remove();
                // Renumber
                Array.from(container.querySelectorAll('.input-group')).forEach((group, idx) => {
                    group.querySelector('.input-group-text').textContent = idx + 1;
                });
            } else {
                alert('Phải có ít nhất 2 phương án!');
            }
        }

        window.cancelEdit = function(questionId, formId) {
            document.getElementById(formId)?.remove();
            const questionCard = document.querySelector(`.question-item[data-id="${questionId}"]`);
            if (questionCard) questionCard.style.display = '';
        }

        window.saveEditedQuestion = function(questionId, formId, questionType) {
            const content = document.getElementById(`${formId}-content`).value.trim();
            if (!content) {
                alert('Vui lòng nhập nội dung câu hỏi!');
                return;
            }

            const question = allQuestionsData.find(q => q.id === questionId);
            if (!question) return;

            const data = {
                noidung_cauhoi: content,
                loai_cauhoi: questionType,
                page: question.page,
                batbuoc: document.getElementById(`${formId}-required`).checked ? 1 : 0,
                check_duplicate: document.getElementById(`${formId}-checkdup`).checked ? 1 : 0,
                phuong_an: [],
                is_personal_info: question.is_personal_info ? 1 : 0,
                data_source_id: null,
                cau_dieukien_id: question.cau_dieukien_id || null,
                dieukien_hienthi: question.dieukien_hienthi || null
            };

            // Collect options
            if (['single_choice', 'multiple_choice'].includes(questionType)) {
                const options = Array.from(document.querySelectorAll(`#${formId} .option-input`))
                    .map(input => input.value.trim())
                    .filter(val => val !== '');
                
                if (options.length < 2) {
                    alert('Vui lòng nhập ít nhất 2 phương án!');
                    return;
                }
                data.phuong_an = options;
            } else if (questionType === 'likert') {
                data.phuong_an = ['Rất không hài lòng', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
            } else if (questionType === 'custom_select') {
                const dsId = document.getElementById(`${formId}-datasource`)?.value;
                if (!dsId) {
                    alert('Vui lòng chọn nguồn dữ liệu!');
                    return;
                }
                data.data_source_id = dsId;
            }

            // Save via AJAX
            $.ajax({
                url: `/admin/cau-hoi/${questionId}`,
                method: 'PUT',
                data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        cancelEdit(questionId, formId);
                        loadInitialQuestions();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = Object.values(xhr.responseJSON.errors).flat();
                        alert('Lỗi: \\n' + errors.join('\\n'));
                    } else {
                        alert('Đã xảy ra lỗi khi lưu câu hỏi!');
                    }
                }
            });
        }


        window.deleteCauHoi = function (id) {
            if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) return;
            if (isLocked) {
                alert('error', 'Lỗi', 'Mẫu khảo sát đang bị khóa, không thể chỉnh sửa câu hỏi này.');
                return;
            }
            $.ajax({
                url: `{{ url('admin/cau-hoi') }}/${id}`, method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    allQuestionsData = allQuestionsData.filter(q => q.id !== id);
                    renderAllLists();
                },
                error: function (xhr) { alert('error', 'Lỗi', 'Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại')); }
            });
        }

        // === DRAG AND DROP FROM PALETTE ===
        let draggedQuestionType = null;
        let draggedFromPalette = false;

        // Setup palette drag events
        function setupPaletteDragHandlers() {
            document.querySelectorAll('.question-type-item').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    draggedQuestionType = this.dataset.questionType;
                    draggedFromPalette = true;
                    e.dataTransfer.effectAllowed = 'copy';
                    this.style.opacity = '0.5';
                });

                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '';
                    draggedQuestionType = null;
                    draggedFromPalette = false;
                });
            });
        }

        // Setup page drop zones
        function setupPageDropZones() {
            // Handle personal questions list
            const personalList = document.getElementById('personal-questions-list');
            if (personalList) {
                personalList.addEventListener('dragover', function(e) {
                    if (draggedFromPalette) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'copy';
                        this.classList.add('drag-over');
                    }
                });

                personalList.addEventListener('dragleave', function(e) {
                    if (e.target === this) {
                        this.classList.remove('drag-over');
                    }
                });

                personalList.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    if (draggedFromPalette && draggedQuestionType) {
                        createInlineQuestionForm(draggedQuestionType, 1, this, true);
                    }
                });
            }

            // Handle survey page containers
            document.querySelectorAll('.page-body').forEach(pageBody => {
                pageBody.addEventListener('dragover', function(e) {
                    if (draggedFromPalette || e.dataTransfer.types.includes('text/plain')) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'copy';
                        this.classList.add('drag-over');
                    }
                });

                pageBody.addEventListener('dragleave', function(e) {
                    if (e.target === this) {
                        this.classList.remove('drag-over');
                    }
                });

                pageBody.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    if (draggedFromPalette && draggedQuestionType) {
                        const pageNum = parseInt(this.dataset.page);
                        createInlineQuestionForm(draggedQuestionType, pageNum, this, false);
                    }
                });
            });
        }

        // Create inline form for new question
        function createInlineQuestionForm(questionType, pageNum, container, isPersonalInfo = false) {
            if (isLocked) {
                alert('Mẫu khảo sát đang bị khóa!');
                return;
            }

            // Remove any existing forms
            document.querySelectorAll('.question-form-inline').forEach(f => f.remove());

            const formId = 'inline-form-' + Date.now();
            const needsOptions = ['single_choice', 'multiple_choice', 'likert'].includes(questionType);
            const needsDataSource = questionType === 'custom_select';
            
            let optionsHtml = '';
            if (needsOptions) {
                if (questionType === 'likert') {
                    optionsHtml = `
                        <div class="mb-3">
                            <label class="form-label">Phương án (Likert)</label>
                            <div class="small text-muted">Thang đo Likert chuẩn 5 mức</div>
                        </div>
                    `;
                } else {
                    optionsHtml = `
                        <div class="mb-3">
                            <label class="form-label">Phương án trả lời <span class="text-danger">*</span></label>
                            <div id="${formId}-options">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">1</span>
                                    <input type="text" class="form-control option-input" placeholder="Phương án 1">
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">2</span>
                                    <input type="text" class="form-control option-input" placeholder="Phương án 2">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addOptionToForm('${formId}')">
                                <i class="bi bi-plus"></i> Thêm phương án
                            </button>
                        </div>
                    `;
                }
            }

            let dataSourceHtml = '';
            if (needsDataSource) {
                dataSourceHtml = `
                    <div class="mb-3">
                        <label class="form-label">Nguồn dữ liệu <span class="text-danger">*</span></label>
                        <select class="form-select" id="${formId}-datasource">
                            <option value="">-- Chọn nguồn dữ liệu --</option>
                            @foreach($dataSources as $ds)
                                <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            }

            const questionTypeLabel = isPersonalInfo ? 'Tạo câu hỏi thông tin cá nhân' : 'Tạo câu hỏi';
            const formHtml = `
                <div class="question-form-inline" id="${formId}">
                    <h6 class="mb-3">
                        <i class="bi bi-plus-circle me-2"></i>
                        ${questionTypeLabel}: ${getQuestionTypeName(questionType)}
                    </h6>
                    <div class="mb-3">
                        <label class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="${formId}-content" rows="2" placeholder="Nhập nội dung câu hỏi..."></textarea>
                    </div>
                    ${optionsHtml}
                    ${dataSourceHtml}
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${formId}-required" checked>
                            <label class="form-check-label" for="${formId}-required">
                                Bắt buộc trả lời
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${formId}-checkdup">
                            <label class="form-check-label" for="${formId}-checkdup">
                                Kiểm tra trùng lặp
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveInlineQuestion('${formId}', '${questionType}', ${pageNum}, ${isPersonalInfo})">
                            <i class="bi bi-save"></i> Lưu câu hỏi
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelInlineForm('${formId}')">
                            <i class="bi bi-x"></i> Hủy
                        </button>
                    </div>
                </div>
            `;

            // Insert at the end of page body
            container.insertAdjacentHTML('beforeend', formHtml);

            // Hide placeholder if creating in personal list
            if (isPersonalInfo) {
                const placeholder = document.getElementById('personal-placeholder');
                if (placeholder) placeholder.style.display = 'none';
            }

            // Focus on content field
            document.getElementById(`${formId}-content`).focus();
        }

        window.addOptionToForm = function(formId) {
            const container = document.getElementById(`${formId}-options`);
            const count = container.querySelectorAll('.input-group').length + 1;
            const html = `
                <div class="input-group mb-2">
                    <span class="input-group-text">${count}</span>
                    <input type="text" class="form-control option-input" placeholder="Phương án ${count}">
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        window.cancelInlineForm = function(formId) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            // Check if this was in personal list
            const isInPersonalList = form.closest('#personal-questions-list') !== null;
            
            form.remove();
            
            // Show placeholder again if personal list is now empty
            if (isInPersonalList) {
                const personalList = document.getElementById('personal-questions-list');
                const hasQuestions = personalList.querySelector('.question-item') !== null;
                const placeholder = document.getElementById('personal-placeholder');
                
                if (!hasQuestions && placeholder) {
                    placeholder.style.display = 'block';
                }
            }
        }

        window.saveInlineQuestion = function(formId, questionType, pageNum, isPersonalInfo = false) {
            const content = document.getElementById(`${formId}-content`).value.trim();
            if (!content) {
                alert('Vui lòng nhập nội dung câu hỏi!');
                return;
            }

            const data = {
                noidung_cauhoi: content,
                loai_cauhoi: questionType,
                page: pageNum,
                batbuoc: document.getElementById(`${formId}-required`).checked ? 1 : 0,
                check_duplicate: document.getElementById(`${formId}-checkdup`).checked ? 1 : 0,
                phuong_an: [],
                is_personal_info: isPersonalInfo ? 1 : 0,
                data_source_id: null
            };

            // Collect options if needed
            if (['single_choice', 'multiple_choice'].includes(questionType)) {
                const options = Array.from(document.querySelectorAll(`#${formId} .option-input`))
                    .map(input => input.value.trim())
                    .filter(val => val !== '');
                
                if (options.length < 2) {
                    alert('Vui lòng nhập ít nhất 2 phương án!');
                    return;
                }
                data.phuong_an = options;
            } else if (questionType === 'likert') {
                data.phuong_an = ['Rất không hài lòng', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
            } else if (questionType === 'custom_select') {
                const dsId = document.getElementById(`${formId}-datasource`)?.value;
                if (!dsId) {
                    alert('Vui lòng chọn nguồn dữ liệu!');
                    return;
                }
                data.data_source_id = dsId;
            }

            // Save via AJAX
            $.ajax({
                url: `/admin/mau-khao-sat/{{ $mauKhaoSat->id }}/cau-hoi`,
                method: 'POST',
                data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        cancelInlineForm(formId);
                        loadInitialQuestions();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = Object.values(xhr.responseJSON.errors).flat();
                        alert('Lỗi: \\n' + errors.join('\\n'));
                    } else {
                        alert('Đã xảy ra lỗi khi lưu câu hỏi!');
                    }
                }
            });
        }

        // Auto create empty page logic
        function checkAndAddEmptyPage() {
            const surveyQuestions = allQuestionsData.filter(q => !q.is_personal_info);
            if (surveyQuestions.length === 0) {
                // Always have at least page 1
                return;
            }

            // Find max page number
            const maxPage = Math.max(...surveyQuestions.map(q => q.page || 1));
            
            // Check if max page has questions
            const maxPageHasQuestions = surveyQuestions.some(q => (q.page || 1) === maxPage);
            
            if (maxPageHasQuestions) {
                // Need to add empty page
                const newPageNum = maxPage + 1;
                const surveyContainer = $('#survey-questions-container');
                const emptyPageHtml = createPageContainerHtml(newPageNum, []);
                surveyContainer.append(emptyPageHtml);
                
                // Re-setup drop zones
                setupPageDropZones();
            }
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
            if (isLocked) return; // Don't initialize if locked

            // Initialize sortable for personal questions list
            const personalList = document.querySelector('#personal-questions-list');
            if (personalList) {
                Sortable.create(personalList, {
                    handle: '.handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    chosenClass: 'sortable-chosen',
                    onEnd: function (evt) {
                        const order = Array.from(personalList.children).map(item => parseInt(item.dataset.id));
                        
                        // Update order in data
                        const personalQuestions = allQuestionsData.filter(q => q.is_personal_info);
                        order.forEach((id, index) => {
                            const question = personalQuestions.find(q => q.id === id);
                            if (question) question.thutu = index + 1;
                        });

                        renderAllLists();

                        // Send to server
                        $.ajax({
                            url: "/admin/cau-hoi/update-order",
                            method: 'POST',
                            data: { order: order },
                            headers: { 'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content') },
                            error: () => alert('error', 'Lỗi', 'Lỗi khi lưu thứ tự, vui lòng tải lại trang.'),
                        });
                    },
                });
            }

            // Initialize sortable for each page container
            document.querySelectorAll('.sortable-page').forEach(pageEl => {
                Sortable.create(pageEl, {
                    group: 'survey-pages', // Same group allows dragging between pages
                    handle: '.handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    chosenClass: 'sortable-chosen',
                    onEnd: function (evt) {
                        const newPageNum = parseInt($(evt.to).data('page'));
                        const questionId = parseInt(evt.item.dataset.id);
                        
                        // Find and update the question's page
                        const question = allQuestionsData.find(q => q.id === questionId);
                        if (question && question.page !== newPageNum) {
                            question.page = newPageNum;
                            
                            // Send page update to server
                            $.ajax({
                                url: `/admin/cau-hoi/${questionId}`,
                                method: 'PUT',
                                data: {
                                    noidung_cauhoi: question.noidung_cauhoi,
                                    loai_cauhoi: question.loai_cauhoi,
                                    page: newPageNum,
                                    batbuoc: question.batbuoc ? 1 : 0,
                                    check_duplicate: question.check_duplicate ? 1 : 0,
                                    phuong_an: question.phuong_an_tra_loi?.map(pa => pa.noidung) || [],
                                    is_personal_info: question.is_personal_info ? 1 : 0,
                                    data_source_id: question.data_source_id || null,
                                    cau_dieukien_id: question.cau_dieukien_id || null,
                                    dieukien_hienthi: question.dieukien_hienthi || null
                                },
                                headers: { 'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content') },
                                error: () => console.error('Failed to update page for question', questionId)
                            });
                        }

                        // Update order within the page
                        const order = Array.from(evt.to.children).map(item => parseInt(item.dataset.id));
                        order.forEach((id, index) => {
                            const q = allQuestionsData.find(q => q.id === id);
                            if (q) q.thutu = index + 1;
                        });

                        renderAllLists();

                        // Send order update to server
                        $.ajax({
                            url: "/admin/cau-hoi/update-order",
                            method: 'POST',
                            data: { order: order },
                            headers: { 'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content') },
                            error: () => alert('error', 'Lỗi', 'Lỗi khi lưu thứ tự, vui lòng tải lại trang.'),
                        });
                    },
                });
            });
        }

        // === CONDITIONAL LOGIC ===
        window.showConditionalModal = function(questionId) {
            const modal = new bootstrap.Modal(document.getElementById('conditionalModal'));
            const question = allQuestionsData.find(q => q.id === questionId);
            if (!question) return;

            // Set question ID
            document.getElementById('conditionalQuestionId').value = questionId;

            // Setup enable/disable toggle
            $('#enableConditionalLogic').off('change').on('change', function() {
                if (this.checked) {
                    $('#conditional-rules').slideDown();
                } else {
                    $('#conditional-rules').slideUp();
                    $('#parentQuestion').val('');
                    $('#parentAnswer').html('');
                }
            });

            // Setup parent question change handler
            $('#parentQuestion').off('change').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const options = selectedOption.data('options');
                const answerSelect = $('#parentAnswer');
                answerSelect.html('<option value="">-- Chọn phương án --</option>');
                
                if (options && Array.isArray(options)) {
                    options.forEach(opt => {
                        answerSelect.append(`
                            <option value="${escapeHtml(opt.noidung)}">${escapeHtml(opt.noidung)}</option>
                        `);
                    });
                }
            });

            // Load existing conditional data
            if (question.cau_dieukien_id && question.dieukien_hienthi) {
                $('#enableConditionalLogic').prop('checked', true).trigger('change');
                $('#parentQuestion').val(question.cau_dieukien_id).trigger('change');
                setTimeout(() => {
                    const condition = typeof question.dieukien_hienthi === 'string' 
                        ? JSON.parse(question.dieukien_hienthi) 
                        : question.dieukien_hienthi;
                    $('#parentAnswer').val(condition.value);
                }, 200);
            } else {
                $('#enableConditionalLogic').prop('checked', false).trigger('change');
            }

            modal.show();
        };

        window.saveConditionalLogic = function() {
            const questionId = document.getElementById('conditionalQuestionId').value;
            const enabled = $('#enableConditionalLogic').is(':checked');
            
            let data = {};
            
            if (enabled) {
                const parentId = $('#parentQuestion').val();
                const answerValue = $('#parentAnswer').val();
                
                if (!parentId || !answerValue) {
                    alert('error', 'Lỗi', 'Vui lòng chọn đầy đủ câu hỏi điều kiện và phương án trả lời');
                    return;
                }
                
                data = {
                    cau_dieukien_id: parentId,
                    dieukien_hienthi: JSON.stringify({ 
                        operator: 'equals',
                        value: answerValue 
                    })
                };
            } else {
                data = {
                    cau_dieukien_id: null,
                    dieukien_hienthi: null
                };
            }

            $.ajax({
                url: `/admin/cau-hoi/${questionId}`,
                method: 'PUT',
                data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    alert('success', 'Thành công', 'Đã lưu logic điều kiện');
                    bootstrap.Modal.getInstance(document.getElementById('conditionalModal')).hide();
                    
                    // Reload questions to reflect changes
                    loadInitialQuestions();
                },
                error: function() {
                    alert('error', 'Lỗi', 'Không thể lưu logic điều kiện');
                }
            });
        };

        // === Điểm khởi chạy chính ===
        $(document).ready(function () {
            loadInitialQuestions();
            initializeSortable();
        });
    </script>
@endpush