@extends('layouts.admin')

@section('title', 'Báo cáo: ' . $dotKhaoSat->ten_dot)

@push('styles')
    <style>
        .modal-backdrop.fade {
            opacity: 0;
            transition: opacity 0.3s ease-out !important;
        }

        .modal-backdrop.show {
            opacity: 1;
            background-color: rgba(241, 245, 249, 0.5);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
        }

        .modal.fade .modal-dialog {
            transform: translateY(30px) scale(0.98);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.3s ease-out !important;
        }

        .modal.show .modal-dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.bao-cao.index') }}">Báo cáo</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $dotKhaoSat->ten_dot }}</li>
            </ol>
        </nav>

        <!-- Header Báo cáo -->
        <div class="row align-items-center mb-4">
            <div class="col-lg-8 col-md-7">
                <h1 class="h3 mb-1">{{ $dotKhaoSat->ten_dot }}</h1>
                <p class="text-muted mb-0">
                    <span class="fw-semibold">Tên mẫu khảo sát:</span>
                    <span class="fw-bold">
                        {{ $dotKhaoSat->mauKhaoSat->ten_mau ?? 'N/A' }}
                    </span>
                    <span class="mx-2">|</span>
                    <span class="fw-semibold">Thời gian:</span>
                    <span class="fw-bold">{{ $dotKhaoSat->tungay }} - {{ $dotKhaoSat->denngay }}</span>
                    @if(!empty($personalInfoFilters))
                        <span class="mx-2">|</span>
                        <span class="fw-semibold">Đang lọc:</span>
                        <span class="fw-bold text-primary">
                            @php
                                $activeFilters = [];
                                if (!empty($personalInfoFilters)) {
                                    foreach ($personalInfoFilters as $qId => $filterValue) {
                                        $question = $personalInfoQuestions->firstWhere('id', $qId);
                                        if ($question && $filterValue) {
                                            $filterLabel = $filterValue;
                                            if ($question->phuongAnTraLoi->count() > 0) {
                                                $option = $question->phuongAnTraLoi->firstWhere('id', $filterValue);
                                                if ($option)
                                                    $filterLabel = $option->noidung;
                                            }
                                            $activeFilters[] = $filterLabel;
                                        }
                                    }
                                }
                            @endphp
                            {{ implode(', ', $activeFilters) }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
                @php
                    $currentQuery = request()->query();
                @endphp
                <div class="btn-group" role="group">
                    {{-- Nút xuất mặc định --}}
                    <a href="{{ route('admin.bao-cao.export', array_merge(['dotKhaoSat' => $dotKhaoSat], $currentQuery, ['format' => 'excel'])) }}"
                        class="btn btn-outline-success" id="exportExcelBtn">
                        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                    </a>
                    <!-- <a href="{{ route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'pdf']) }}"
                                                                                            class="btn btn-danger" id="exportPdfBtn">
                                                                                            <i class="bi bi-file-earmark-pdf"></i> Tải PDF
                                                                                        </a> -->
                    <a href="{{ route('admin.bao-cao.pdf-preview', array_merge(['dotKhaoSat' => $dotKhaoSat], $currentQuery, ['format' => 'pdf'])) }}"
                        class="btn btn-outline-danger" id="previewPdfBtn" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                    </a>
                </div>
            </div>
        </div>

        @if(!empty($hiddenIds))
            <div class="card shadow mb-4 border-warning">
                <div class="card-header bg-warning bg-opacity-25">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-eye-slash me-1"></i> Câu hỏi đang bị ẩn khi tổng hợp
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($dotKhaoSat->mauKhaoSat->cauHoi->whereIn('id', $hiddenIds) as $hiddenQ)
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center border rounded p-2">
                                    <div class="me-3 text-truncate" title="{{ $hiddenQ->noidung_cauhoi }}">
                                        {{ $hiddenQ->noidung_cauhoi }}
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="toggleQuestionVisibility(this, {{ $hiddenQ->id }}, false)">
                                        <i class="bi bi-eye"></i> Hiện trong tổng hợp
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Thống kê tổng quan --}}
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    Tổng quan Kết quả
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <i class="bi bi-check2-circle fs-1 text-success"></i>
                        <div class="h4 mt-2 font-weight-bold text-gray-800">
                            {{ $tongQuan['phieu_hoan_thanh'] }}
                            <span class="fs-6 text-muted">/ {{ $tongQuan['tong_phieu_hoan_thanh'] }}</span>
                        </div>
                        <div class="text-xs font-weight-bold text-success text-uppercase">Phiếu hoàn thành (Hợp lệ / Tổng)
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <i class="bi bi-card-checklist fs-1 text-info"></i>
                        <div class="h4 mt-2 font-weight-bold text-gray-800">{{ $tongQuan['tong_cau_hoi'] }}</div>
                        <div class="text-xs font-weight-bold text-info text-uppercase">Tổng số câu hỏi</div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                        <i class="bi bi-clock-history fs-1 text-warning"></i>
                        <div class="h4 mt-2 font-weight-bold text-gray-800">{{ $tongQuan['thoi_gian_tb'] }}</div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase">Thời gian làm bài (TB)</div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <i class="bi bi-stopwatch fs-1 text-secondary"></i>
                        <div class="mt-2 font-weight-bold text-gray-800">
                            <div><small>Nhanh nhất: {{ $tongQuan['thoi_gian_nhanh_nhat'] }}</small></div>
                            <div><small>Lâu nhất: {{ $tongQuan['thoi_gian_lau_nhat'] }}</small></div>
                        </div>
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mt-1">Biên độ thời gian</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ Xu hướng Phản hồi -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    Xu hướng phản hồi theo ngày
                </h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;"><canvas id="responseTrendChart"></canvas></div>
            </div>
        </div>

        <!-- Bộ lọc -->
        @if(isset($personalInfoQuestions) && $personalInfoQuestions->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-funnel"></i> Bộ lọc theo câu hỏi thông tin cá nhân
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}" id="filterForm">
                        <div class="row g-3">
                            @foreach($personalInfoQuestions as $q)
                                @php
                                    $currentValue = $personalInfoFilters[$q->id] ?? null;
                                    $isSelectType = in_array($q->loai_cauhoi, ['single_choice', 'multiple_choice', 'custom_select']);
                                    $options = $personalInfoOptions[$q->id] ?? [];
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label small text-muted mb-1">{{ $q->noidung_cauhoi }}</label>
                                    @if($isSelectType && !empty($options))
                                        {{-- Hiển thị dropdown cho câu hỏi select --}}
                                        <select class="form-select form-select-sm" name="personal_info_filters[{{ $q->id }}]">
                                            <option value="">-- Tất cả --</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option->value }}" {{ $currentValue == $option->value ? 'selected' : '' }}>
                                                    {{ $option->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{-- Hiển thị input text cho câu hỏi text/number --}}
                                        <input type="text" class="form-control form-control-sm"
                                            name="personal_info_filters[{{ $q->id }}]" value="{{ $currentValue }}"
                                            placeholder="Nhập giá trị để lọc...">
                                    @endif
                                </div>
                            @endforeach
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="bi bi-filter"></i> Áp dụng bộ lọc
                                    </button>
                                    @if(!empty($personalInfoFilters))
                                        <a href="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-x-lg"></i> Bỏ tất cả bộ lọc
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Thống kê chi tiết tất cả câu trả lời --}}
        <h3 class="h4 mb-3">
            Chi tiết tất cả câu trả lời
        </h3>
        @php
            $hiddenIds = $dotKhaoSat->hiddenQuestions->pluck('id')->toArray();
            $personalInfoQuestionsList = $dotKhaoSat->mauKhaoSat->cauHoi->where('is_personal_info', true);
            $surveyQuestionsList = $dotKhaoSat->mauKhaoSat->cauHoi->where('is_personal_info', false);
        @endphp

        @php $personalCount = 1; @endphp
        @if($personalInfoQuestionsList->isNotEmpty())
            <div class="d-flex align-items-center mt-4 mb-3">
                <div class="flex-grow-1 border-bottom border-2 border-primary"></div>
                <div class="px-3 text-uppercase text-primary fw-bold small">Thông tin người trả lời</div>
                <div class="flex-grow-1 border-bottom border-2 border-primary"></div>
            </div>
            @foreach($personalInfoQuestionsList as $cauHoi)
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold text-primary">
                                Câu {{ $personalCount++ }}: {{ $cauHoi->noidung_cauhoi }}
                            </h6>
                            <small class="text-muted">({{ $thongKeCauHoi[$cauHoi->id]['total'] ?? 0 }} lượt trả lời)</small>
                            @if(in_array($cauHoi->id, $hiddenIds))
                                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-eye-slash me-1"></i>Đang ẩn khi tổng
                                    hợp</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @php $isHidden = in_array($cauHoi->id, $hiddenIds); @endphp
                            @if($isHidden)
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="toggleQuestionVisibility(this, {{ $cauHoi->id }}, false)"
                                    title="Hiển thị lại câu hỏi này trong báo cáo tổng hợp">
                                    <i class="bi bi-eye"></i> Hiện khi tổng hợp
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="toggleQuestionVisibility(this, {{ $cauHoi->id }}, true)"
                                    title="Ẩn câu hỏi này khỏi báo cáo tổng hợp">
                                    <i class="bi bi-eye-slash"></i> Ẩn khi tổng hợp
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $stats = $thongKeCauHoi[$cauHoi->id] ?? null;
                        @endphp
                        @if($stats && ($stats['type'] ?? '') === 'hidden')
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Câu hỏi này đang <strong>được ẩn khi tổng hợp</strong>, nên không hiển thị biểu đồ/thống kê.
                            </div>
                        @elseif($stats && $stats['total'] > 0)
                            @if($stats['type'] == 'chart_with_avg')
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-4 mb-md-0">
                                        <div class="display-4 font-weight-bold text-primary">{{ number_format($stats['average'], 2) }}</div>
                                        <div class="font-weight-bold text-gray-600">/ {{ $stats['max_score'] }}</div>
                                        <div class="text-xs text-uppercase text-primary mt-1">Điểm trung bình</div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div style="height: 180px;"><canvas id="chart-cauhoi-{{ $cauHoi->id }}"></canvas></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Phương án</th>
                                                                <th class="text-center">Số lượng</th>
                                                                <th class="text-center">Tỷ lệ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($stats['data'] as $item)
                                                                <tr>
                                                                    <td>{{ $item->noidung ?? 'Không xác định' }}</td>
                                                                    <td class="text-center">{{ $item->so_luong }}</td>
                                                                    <td class="text-center">{{ $item->ty_le }}%</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'chart' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div style="height: 250px;"><canvas id="chart-cauhoi-{{ $cauHoi->id }}"></canvas></div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Phương án</th>
                                                        <th class="text-center">Số lượng</th>
                                                        <th class="text-center">Tỷ lệ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($stats['data'] as $item)
                                                        <tr>
                                                            <td>{{ $item->noidung ?? 'Không xác định' }}</td>
                                                            <td class="text-center">{{ $item->so_luong }}</td>
                                                            <td class="text-center">{{ $item->ty_le }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'text' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach($stats['data'] as $item)
                                        <li class="list-group-item">{{ $item }}</li>
                                    @endforeach
                                </ul>
                                @if($stats['total'] > 20)
                                    <p class="small text-muted mt-2 text-center">... và {{ $stats['total'] - 20 }} câu trả lời khác.</p>
                                @endif
                            @elseif($stats['type'] == 'number_stats')
                                @if(!empty($stats['cauTraLoi']) && is_iterable($stats['cauTraLoi']))
                                    <ul class="list-group list-group-flush mb-2">
                                        @foreach($stats['cauTraLoi'] as $item)
                                            <li class="list-group-item">{{ intval($item) }}</li>
                                        @endforeach
                                    </ul>
                                    @if(isset($stats['total']) && $stats['total'] > 20)
                                        <p class="small text-muted mt-2 text-center">... và {{ $stats['total'] - 20 }} câu trả lời khác.</p>
                                    @endif
                                @else
                                    <p class="text-muted text-center mb-0">Không có dữ liệu.</p>
                                @endif
                            @endif
                        @else
                            <p class="text-muted text-center mb-0">Chưa có dữ liệu cho câu hỏi này.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        @php $count = 1; @endphp
        @if($surveyQuestionsList->isNotEmpty())
            <div class="d-flex align-items-center mt-5 mb-3">
                <div class="flex-grow-1 border-bottom border-2 border-success"></div>
                <div class="px-3 text-uppercase text-success fw-bold small">Câu hỏi khảo sát</div>
                <div class="flex-grow-1 border-bottom border-2 border-success"></div>
            </div>
            @foreach($surveyQuestionsList as $index => $cauHoi)
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold text-primary">
                                Câu {{ $count++ }}: {{ $cauHoi->noidung_cauhoi }}
                            </h6>
                            <small class="text-muted">({{ $thongKeCauHoi[$cauHoi->id]['total'] ?? 0 }} lượt trả lời)</small>
                            @if(in_array($cauHoi->id, $hiddenIds))
                                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-eye-slash me-1"></i>Đang ẩn khi tổng
                                    hợp</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if($cauHoi->loai_cauhoi === 'text' && ($thongKeCauHoi[$cauHoi->id]['total'] ?? 0) > 0 && !$cauHoi->is_personal_info)
                                <button class="btn btn-sm btn-outline-info"
                                    onclick="requestSummary({{ $cauHoi->id }}, '{{ e($cauHoi->noidung_cauhoi) }}')">
                                    <i class="bi bi-robot"></i> Tóm tắt bằng AI
                                </button>
                            @endif
                            @php $isHidden = in_array($cauHoi->id, $hiddenIds); @endphp
                            @if($isHidden)
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="toggleQuestionVisibility(this, {{ $cauHoi->id }}, false)"
                                    title="Hiển thị lại câu hỏi này trong báo cáo tổng hợp">
                                    <i class="bi bi-eye"></i> Hiện khi tổng hợp
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="toggleQuestionVisibility(this, {{ $cauHoi->id }}, true)"
                                    title="Ẩn câu hỏi này khỏi báo cáo tổng hợp">
                                    <i class="bi bi-eye-slash"></i> Ẩn khi tổng hợp
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $stats = $thongKeCauHoi[$cauHoi->id] ?? null;
                        @endphp
                        @if($stats && ($stats['type'] ?? '') === 'hidden')
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Câu hỏi này đang <strong>được ẩn khi tổng hợp</strong>, nên không hiển thị biểu đồ/thống kê.
                            </div>
                        @elseif($stats && $stats['total'] > 0)
                            @if($stats['type'] == 'chart_with_avg')
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-4 mb-md-0">
                                        <div class="display-4 font-weight-bold text-primary">{{ number_format($stats['average'], 2) }}</div>
                                        <div class="font-weight-bold text-gray-600">/ {{ $stats['max_score'] }}</div>
                                        <div class="text-xs text-uppercase text-primary mt-1">Điểm trung bình</div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div style="height: 180px;"><canvas id="chart-cauhoi-{{ $cauHoi->id }}"></canvas></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Phương án</th>
                                                                <th class="text-center">Số lượng</th>
                                                                <th class="text-center">Tỷ lệ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($stats['data'] as $item)
                                                                <tr>
                                                                    <td>{{ $item->noidung ?? 'Không xác định' }}</td>
                                                                    <td class="text-center">{{ $item->so_luong }}</td>
                                                                    <td class="text-center">{{ $item->ty_le }}%</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'chart' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div style="height: 250px;"><canvas id="chart-cauhoi-{{ $cauHoi->id }}"></canvas></div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Phương án</th>
                                                        <th class="text-center">Số lượng</th>
                                                        <th class="text-center">Tỷ lệ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($stats['data'] as $item)
                                                        <tr>
                                                            <td>{{ $item->noidung ?? 'Không xác định' }}</td>
                                                            <td class="text-center">{{ $item->so_luong }}</td>
                                                            <td class="text-center">{{ $item->ty_le }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'text' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach($stats['data'] as $item)
                                        <li class="list-group-item">{{ $item }}</li>
                                    @endforeach
                                </ul>
                                @if($stats['total'] > 20)
                                    <p class="small text-muted mt-2 text-center">... và {{ $stats['total'] - 20 }} câu trả lời khác.</p>
                                @endif
                            @elseif($stats['type'] == 'number_stats')
                                @if(!empty($cauHoi->is_personal_info))
                                    @if(!empty($stats['cauTraLoi']) && is_iterable($stats['cauTraLoi']))
                                        <ul class="list-group list-group-flush mb-2">
                                            @foreach($stats['cauTraLoi'] as $item)
                                                <li class="list-group-item">{{ intval($item) }}</li>
                                            @endforeach
                                        </ul>
                                        @if(isset($stats['total']) && $stats['total'] > 20)
                                            <p class="small text-muted mt-2 text-center">... và {{ $stats['total'] - 20 }} câu trả lời khác.</p>
                                        @endif
                                    @else
                                        <p class="text-muted text-center mb-0">Không có dữ liệu.</p>
                                    @endif
                                @else
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="h5">{{ number_format($stats['data']->avg, 2) }}</div>
                                            <div class="text-muted small">Trung bình</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5">{{ number_format($stats['data']->min, 2) }}</div>
                                            <div class="text-muted small">Nhỏ nhất</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5">{{ number_format($stats['data']->max, 2) }}</div>
                                            <div class="text-muted small">Lớn nhất</div>
                                        </div>
                                        <div class="col">
                                            <div class="h5">{{ number_format($stats['data']->stddev, 2) }}</div>
                                            <div class="text-muted small">Độ lệch chuẩn</div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @else
                            <p class="text-muted text-center mb-0">Chưa có dữ liệu cho câu hỏi này.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @elseif($personalInfoQuestionsList->isEmpty())
            <div class="card shadow mb-4">
                <div class="card-body">
                    <p class="text-muted text-center mb-0">Mẫu khảo sát này chưa có câu hỏi nào.</p>
                </div>
            </div>
        @endif

        <!-- Danh sách chi tiết phiếu trả lời -->
        <div class="card shadow mb-4" id="detailSurvey">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu đã hoàn thành</h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 70px;">STT</th>
                                @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                    @foreach($personalInfoQuestions as $q)
                                        <th scope="col">{{ $q->noidung_cauhoi }}</th>
                                    @endforeach
                                @endif
                                <th scope="col">Thời gian làm bài</th>
                                <th scope="col" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($danhSachPhieu as $phieu)
                                <tr>
                                    <td class="text-center">
                                        {{ ($danhSachPhieu->firstItem() ?? 0) + $loop->index }}
                                    </td>
                                    @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                        @foreach($personalInfoQuestions as $q)
                                            <td>{{ $personalInfoAnswers[$phieu->id][$q->id] ?? 'N/A' }}</td>
                                        @endforeach
                                    @endif
                                    <td>
                                        {{ $phieu->thoigian_batdau ? $phieu->thoigian_batdau->format('d/m/Y H:i') : 'N/A' }} -
                                        {{ $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết phiếu"
                                                onclick="showResponseDetail({{ $phieu->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" title="Đánh dấu là trùng lặp"
                                                onclick="markAsDuplicate({{ $phieu->id }})">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Xóa toàn bộ phiếu khảo sát"
                                                onclick="deleteEntireSurvey({{ $phieu->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ (isset($personalInfoQuestions) ? $personalInfoQuestions->count() : 0) + 3 }}"
                                        class="text-center">Chưa có phiếu nào được hoàn thành.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    {{-- Thêm withQueryString() để giữ bộ lọc khi phân trang --}}
                    {{ $danhSachPhieu->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <!-- Danh sách phiếu bị đánh dấu trùng lặp -->
        @if($danhSachPhieuTrungLap->isNotEmpty())
            <div class="card shadow mb-4 border-warning">
                <div class="card-header bg-amber-100 bg-opacity-25">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Danh sách phiếu bị đánh dấu trùng lặp ({{ $danhSachPhieuTrungLap->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                        @foreach($personalInfoQuestions as $q)
                                            <th scope="col">{{ $q->noidung_cauhoi }}</th>
                                        @endforeach
                                    @endif
                                    <th scope="col">Thời gian làm bài</th>
                                    <th scope="col" class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($danhSachPhieuTrungLap as $phieu)
                                    <tr>
                                        @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                            @foreach($personalInfoQuestions as $q)
                                                <td>{{ $personalInfoAnswers[$phieu->id][$q->id] ?? 'N/A' }}</td>
                                            @endforeach
                                        @endif
                                        <td>
                                            {{ $phieu->thoigian_batdau ? $phieu->thoigian_batdau->format('d/m/Y H:i') : 'N/A' }} -
                                            {{ $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info" title="Xem chi tiết phiếu"
                                                    onclick="showResponseDetail({{ $phieu->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" title="Đánh dấu là không trùng lặp"
                                                    onclick="toggleDuplicate({{ $phieu->id }})">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Xóa vĩnh viễn phiếu này"
                                                    onclick="deleteEntireSurvey({{ $phieu->id }}, true)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $danhSachPhieuTrungLap->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="summaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="summaryModalLabel">
                        <i class="bi bi-robot"></i> Tóm tắt AI cho câu hỏi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" id="summaryQuestionContext"></p>
                    <hr>
                    <div id="summaryContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3">AI đang phân tích và tóm tắt... Vui lòng chờ trong giây lát.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="responseDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Chi tiết Phiếu khảo sát</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="responseDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa câu trả lời -->
    <div class="modal fade" id="deleteResponseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa câu trả lời
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Cảnh báo:</strong> Thao tác này sẽ xóa câu trả lời và không thể hoàn tác!
                    </div>
                    <p>Bạn có chắc chắn muốn xóa câu trả lời này không?</p>
                    <div class="bg-light p-3 rounded">
                        <strong>Câu hỏi:</strong><br>
                        <span id="deleteQuestionText">Đang tải...</span><br><br>
                        <strong>Câu trả lời:</strong><br>
                        <span id="deleteAnswerText">Đang tải...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Hủy
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Xóa câu trả lời
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa toàn bộ phiếu khảo sát -->
    <div class="modal fade" id="deleteSurveyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa toàn bộ phiếu khảo sát
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Cảnh báo:</strong> Thao tác này sẽ xóa phiếu khảo sát này!
                    </div>
                    <p class="mb-3">Bạn có chắc chắn muốn xóa phiếu khảo sát này không?</p>
                    <div class="bg-light p-3 rounded">
                        <strong>Thông tin phiếu:</strong><br>
                        <span id="deleteSurveyInfo">Đang tải...</span>
                    </div>
                    <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning rounded">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Hành động này không thể hoàn tác và sẽ ảnh hưởng đến thống kê báo cáo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Hủy
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSurveyBtn">
                        <i class="bi bi-trash me-1"></i>Xóa toàn bộ phiếu
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Biểu đồ Xu hướng Phản hồi
            const trendCtx = document.getElementById('responseTrendChart')?.getContext('2d');
            if (trendCtx) {
                const trendData = @json($responseTrendChart);
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels,
                        datasets: [{
                            label: 'Số phiếu hoàn thành',
                            data: trendData.values,
                            borderColor: '#4e73df', backgroundColor: 'rgba(78, 115, 223, 0.1)',
                            fill: true, tension: 0.3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
                });
            }

            // Biểu đồ cho từng câu hỏi
            @foreach($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi)
                @php $stats = $thongKeCauHoi[$cauHoi->id] ?? null; @endphp
                @if($stats && ($stats['type'] ?? '') === 'hidden')
                    @continue
                @endif
                @if($stats && $stats['type'] == 'chart_with_avg' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                    {
                        const ctxAvg{{ $cauHoi->id }} = document.getElementById('chart-cauhoi-{{ $cauHoi->id }}')?.getContext('2d');
                        if (ctxAvg{{ $cauHoi->id }}) {
                            new Chart(ctxAvg{{ $cauHoi->id }}, {
                                type: 'bar',
                                data: {
                                    labels: {!! json_encode($stats['data']->pluck('noidung')) !!},
                                    datasets: [{
                                        label: 'Số lượt chọn',
                                        data: {!! json_encode($stats['data']->pluck('so_luong')) !!},
                                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                                        borderRadius: 4
                                    }]
                                },
                                options: {
                                    indexAxis: 'y',
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                                    plugins: { legend: { display: false } }
                                }
                            });
                        }
                    }
                @elseif($stats && $stats['type'] == 'chart' && !empty($stats['data']) && $stats['data']->isNotEmpty())
                    {
                        const ctx{{ $cauHoi->id }} = document.getElementById('chart-cauhoi-{{ $cauHoi->id }}')?.getContext('2d');
                        if (ctx{{ $cauHoi->id }}) {
                            new Chart(ctx{{ $cauHoi->id }}, {
                                type: 'pie',
                                data: {
                                    labels: {!! json_encode($stats['data']->pluck('noidung')) !!},
                                    datasets: [{
                                        label: 'Số lượt chọn',
                                        data: {!! json_encode($stats['data']->pluck('so_luong')) !!},
                                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                                        borderRadius: 4
                                    }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false } }
                                }
                            });
                        }
                    }
                @endif
            @endforeach
                        });

        const summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));

        function requestSummary(questionId, questionContext) {
            $('#summaryQuestionContext').text('Câu hỏi: ' + questionContext + '.');
            $('#summaryContent').html(`
                                                            <div class="text-center py-5">
                                                                <div class="spinner-border text-primary" role="status"></div>
                                                                <p class="mt-3">AI đang phân tích và tóm tắt... Vui lòng chờ trong giây lát.</p>
                                                            </div>
                                                        `);
            summaryModal.show();

            $.ajax({
                url: "/admin/bao-cao/{{ $dotKhaoSat->id }}/summarize",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cauhoi_id: questionId
                },
                success: function (response) {
                    $('#summaryContent').html(response.summary);
                },
                error: function (xhr) {
                    let errorMessage = "Có lỗi không xác định xảy ra.";
                    if (xhr.status === 503 && xhr.responseJSON && xhr.responseJSON.summary) {
                        $('#summaryContent').html(xhr.responseJSON.summary);
                    } else {
                        if (xhr.responseJSON && xhr.responseJSON.summary) {
                            errorMessage = xhr.responseJSON.summary;
                        }
                        $('#summaryContent').html(`<div class="alert alert-danger">${errorMessage}</div>`);
                    }
                }
            });
        }

        const responseDetailModal = new bootstrap.Modal(document.getElementById('responseDetailModal'));

        function showResponseDetail(phieuId) {
            const modalContent = $('#responseDetailContent');
            const modalLabel = $('#responseModalLabel');
            const modalInstance = new bootstrap.Modal(document.getElementById('responseDetailModal'));

            modalLabel.text('Chi tiết Phiếu khảo sát #' + phieuId);
            modalContent.html(`
                                                        <div class="text-center py-5">
                                                            <div class="spinner-border text-primary" role="status"></div>
                                                            <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
                                                        </div>
                                                    `);
            modalInstance.show();

            $.get(`/admin/phieu-khao-sat/${phieuId}`)
                .done(function (phieuData) {
                    const answersByQuestionId = {};
                    phieuData.chi_tiet.forEach(answer => {
                        const qId = answer.cauhoi_id;
                        if (!answersByQuestionId[qId]) {
                            answersByQuestionId[qId] = [];
                        }
                        const value = answer.phuong_an ? answer.phuong_an.noidung
                            : (answer.giatri_text || answer.giatri_number || (answer.giatri_date ? new Date(answer.giatri_date).toLocaleDateString('vi-VN') : ''));
                        answersByQuestionId[qId].push(value);
                    });

                    const allQuestions = phieuData.dot_khao_sat.mau_khao_sat.cau_hoi || [];
                    const personalInfoQuestions = allQuestions.filter(q => q.is_personal_info);
                    const surveyQuestions = allQuestions.filter(q => !q.is_personal_info);

                    let html = '';
                    if (personalInfoQuestions.length > 0) {
                        html += `<h5><i class="bi bi-person-circle text-primary me-2"></i>Thông tin người trả lời</h5>
                                                            <table class="table table-sm table-bordered mb-4"><tbody>`;
                        personalInfoQuestions.forEach(question => {
                            const answerArray = answersByQuestionId[question.id] || [];
                            const answerText = answerArray.length > 0 ? answerArray.join('; ') : '<em class="text-muted">(Không trả lời)</em>';
                            html += `<tr>
                                                                        <td width="40%"><strong>${escapeHtml(question.noidung_cauhoi)}</strong></td>
                                                                        <td>${answerText}</td>
                                                                        </tr>`;
                        });
                        html += `</tbody></table>`;
                    }

                    if (surveyQuestions.length > 0) {
                        html += `<hr><h5 class="mt-4"><i class="bi bi-card-checklist text-success me-2"></i>Nội dung khảo sát</h5>`;
                        surveyQuestions.forEach((question, index) => {
                            const answerArray = answersByQuestionId[question.id] || [];
                            const answerText = answerArray.length > 0 ? answerArray.join('; ') : '<em class="text-muted">(Không trả lời)</em>';

                            // Tìm chi tiết câu trả lời để lấy ID
                            const responseDetails = phieuData.chi_tiet.filter(detail => detail.cauhoi_id === question.id);

                            html += `<div class="mb-3 border rounded p-3">
                                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                                <p class="mb-1"><strong>Câu ${index + 1}:</strong> ${escapeHtml(question.noidung_cauhoi)}</p>
                                                                                ${responseDetails.length > 0 ? `
                                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                                        ${responseDetails.map(detail => `
                                                                                            <!-- <button class="btn btn-outline-danger btn-sm" 
                                                                                                    title="Xóa câu trả lời này"
                                                                                                    onclick="deleteSpecificResponse(${detail.id}, '${escapeHtml(question.noidung_cauhoi)}', '${escapeHtml(answerText)}')">
                                                                                                <i class="bi bi-trash"></i>
                                                                                            </button> -->
                                                                                        `).join('')}
                                                                                    </div>
                                                                                ` : ''}
                                                                            </div>
                                                                            <p class="ps-3 text-primary fst-italic mb-0">${answerText}</p>
                                                                            </div>`;
                        });
                    }
                    modalContent.html(html);
                })
                .fail(function () {
                    modalContent.html('<div class="alert alert-danger">Không thể tải dữ liệu chi tiết. Vui lòng thử lại.</div>');
                });
        }

        function escapeHtml(text) {
            if (typeof text !== 'string') return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function toggleQuestionVisibility(btn, questionId, shouldHide) {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang lưu...';

            $.post(
                `/admin/bao-cao/dot-khao-sat/{{ $dotKhaoSat->id }}/toggle-question`, {
                _token: '{{ csrf_token() }}',
                cauhoi_id: questionId,
                hidden: shouldHide
            }
            ).done(function () {
                const message = shouldHide ? 'Câu hỏi sẽ được ẩn khi tổng hợp báo cáo.' : 'Câu hỏi sẽ được hiển thị lại trong tổng hợp.';
                alert('success', 'Đã cập nhật', message);
                setTimeout(() => { window.location.reload(); }, 1000);
            }).fail(function (xhr) {
                let msg = 'Không thể cập nhật trạng thái ẩn.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert('error', 'Lỗi', msg);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }
        // Xử lý xóa toàn bộ phiếu khảo sát
        let currentSurveyId = null;
        const deleteSurveyModal = new bootstrap.Modal(document.getElementById('deleteSurveyModal'));

        function deleteEntireSurvey(surveyId, isDuplicate = false) {
            currentSurveyId = surveyId;

            // Hiển thị thông tin phiếu trong modal
            $('#deleteSurveyInfo').html(`
                                                                                                                                <div class="text-center py-3">
                                                                                                                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                                                                                                    <p class="mt-2 mb-0">Đang tải thông tin phiếu...</p>
                                                                                                                                </div>
                                                                                                                            `);

            deleteSurveyModal.show();

            // Tải thông tin phiếu để hiển thị trong modal xác nhận
            $.get(`/admin/phieu-khao-sat/${surveyId}`)
                .done(function (phieuData) {
                    const personalInfoQuestions = phieuData.dot_khao_sat.mau_khao_sat.cau_hoi.filter(q => q.is_personal_info);
                    let infoText = `Phiếu #${surveyId}<br>`;

                    if (personalInfoQuestions.length > 0) {
                        const answersByQuestionId = {};
                        phieuData.chi_tiet.forEach(answer => {
                            const qId = answer.cauhoi_id;
                            if (!answersByQuestionId[qId]) {
                                answersByQuestionId[qId] = [];
                            }
                            const value = answer.phuong_an ? answer.phuong_an.noidung
                                : (answer.giatri_text || answer.giatri_number || (answer.giatri_date ? new Date(answer.giatri_date).toLocaleDateString('vi-VN') : ''));
                            answersByQuestionId[qId].push(value);
                        });

                        personalInfoQuestions.forEach(question => {
                            const answerArray = answersByQuestionId[question.id] || [];
                            const answerText = answerArray.length > 0 ? answerArray.join('; ') : '(Không trả lời)';
                            infoText += `<strong>${escapeHtml(question.noidung_cauhoi)}:</strong> ${escapeHtml(answerText)}<br>`;
                        });
                    }

                    infoText += `<strong>Thời gian hoàn thành:</strong> ${phieuData.thoigian_hoanthanh ? new Date(phieuData.thoigian_hoanthanh).toLocaleString('vi-VN') : 'N/A'}<br>`;
                    infoText += `<strong>Số câu trả lời:</strong> ${phieuData.chi_tiet.length} câu`;

                    $('#deleteSurveyInfo').html(infoText);
                })
                .fail(function () {
                    $('#deleteSurveyInfo').html(`<span class="text-danger">Không thể tải thông tin phiếu #${surveyId}</span>`);
                });
        }

        // Xử lý xác nhận xóa toàn bộ phiếu
        $('#confirmDeleteSurveyBtn').on('click', function () {
            if (!currentSurveyId) return;

            const btn = $(this);
            const originalText = btn.html();

            // Disable button và hiển thị loading
            btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Đang xóa...');

            // Gọi API xóa toàn bộ phiếu khảo sát
            $.ajax({ // admin/bao-cao/phieu-khao-sat/{phieuKhaoSat}
                url: `/admin/bao-cao/survey/${currentSurveyId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        // Hiển thị thông báo thành công
                        alert('success', 'Xóa thành công', response.message);

                        // Đóng modal
                        deleteSurveyModal.hide();

                        // Reload trang để cập nhật dữ liệu
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        alert('error', 'Lỗi', response.message);
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function (xhr) {
                    let errorMessage = 'Có lỗi xảy ra khi xóa phiếu khảo sát.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert('error', 'Lỗi', errorMessage);
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        function toggleDuplicate(phieuId) {
            if (!confirm(`Bạn có chắc chắn muốn đánh dấu phiếu #${phieuId} là hợp lệ? Phiếu này sẽ được chuyển sang tab "Phiếu hợp lệ" và được tính vào báo cáo.`)) {
                return;
            }

            $.ajax({
                url: `/admin/bao-cao/phieu-khao-sat/${phieuId}/toggle-duplicate`,
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        alert('success', 'Thành công', response.message);
                        // Reload trang để cập nhật lại toàn bộ dữ liệu
                        location.reload();
                    }
                },
                error: function (xhr) { alert('error', 'Lỗi', xhr.responseJSON?.message || 'Đã có lỗi xảy ra.'); }
            });
        }

        function markAsDuplicate(phieuId) {
            if (!confirm(`Bạn có chắc chắn muốn đánh dấu phiếu #${phieuId} là trùng lặp? Phiếu này sẽ được chuyển sang tab "Phiếu trùng lặp" và không được tính vào báo cáo.`)) {
                return;
            }

            $.ajax({
                url: `/admin/bao-cao/phieu-khao-sat/${phieuId}/mark-as-duplicate`,
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        alert('success', 'Thành công', response.message);
                        // Reload trang để cập nhật lại toàn bộ dữ liệu
                        location.reload();
                    }
                },
                error: function (xhr) { alert('error', 'Lỗi', xhr.responseJSON?.message || 'Đã có lỗi xảy ra.'); }
            });
        }

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const exportExcelBtn = document.getElementById('exportExcelBtn');
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            const previewPdfBtn = document.getElementById('previewPdfBtn');

            function updateExportLinks() {
                if (!exportExcelBtn || !exportPdfBtn || !previewPdfBtn) return;

                // Lấy URL gốc của nút export
                const excelBaseUrl = "{{ route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'excel']) }}";
                const pdfBaseUrl = "{{ route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'pdf']) }}";
                const previewPdfBaseUrl = "{{ route('admin.bao-cao.pdf-preview', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'pdf']) }}";

                // Lấy tất cả các bộ lọc từ form
                const formData = new FormData(filterForm);
                const filters = [];

                // Lấy personal info filters
                for (const [key, value] of formData.entries()) {
                    if (key.startsWith('personal_info_filters[') && value) {
                        filters.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                    }
                }

                // Thêm filters vào URL
                if (filters.length > 0) {
                    const queryString = '&' + filters.join('&');
                    exportExcelBtn.href = excelBaseUrl + queryString;
                    exportPdfBtn.href = pdfBaseUrl + queryString;
                    previewPdfBtn.href = previewPdfBaseUrl + queryString;
                    exportExcelBtn.innerHTML = '<i class="bi bi-file-earmark-excel"></i> Xuất Excel (Đã lọc)';
                    exportPdfBtn.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Tải PDF (Đã lọc)';
                    previewPdfBtn.innerHTML = '<i class="bi bi-eye"></i> Xem HTML PDF (Đã lọc)';
                } else {
                    exportExcelBtn.href = excelBaseUrl;
                    exportPdfBtn.href = pdfBaseUrl;
                    previewPdfBtn.href = previewPdfBaseUrl;
                    exportExcelBtn.innerHTML = '<i class="bi bi-file-earmark-excel"></i> Xuất Excel (Tất cả)';
                    exportPdfBtn.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Tải PDF (Tất cả)';
                    previewPdfBtn.innerHTML = '<i class="bi bi-eye"></i> Xem HTML PDF (Tất cả)';
                }
            }

            // Update export links khi form thay đổi
            if (filterForm) {
                filterForm.addEventListener('change', function (e) {
                    // Cập nhật khi thay đổi select hoặc input
                    if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT') {
                        updateExportLinks();
                    }
                });

                // Cập nhật khi người dùng nhập vào input text
                filterForm.addEventListener('input', function (e) {
                    if (e.target.tagName === 'INPUT' && e.target.type === 'text') {
                        updateExportLinks();
                    }
                });
            }

            // Chạy lần đầu khi tải trang để cập nhật link theo bộ lọc hiện tại
            @if(!empty($personalInfoFilters))
                updateExportLinks();
            @endif

                    // Tự động cuộn đến phần detailSurvey nếu có tham số page
                    if (new URLSearchParams(window.location.search).has('page')) {
                const detailSurvey = document.getElementById('detailSurvey');
                if (detailSurvey) {
                    setTimeout(() => {
                        detailSurvey.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            }
        });
    </script>
@endpush