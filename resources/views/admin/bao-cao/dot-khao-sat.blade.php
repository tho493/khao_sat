@extends('layouts.admin')

@section('title', 'Báo cáo: ' . $dotKhaoSat->ten_dot)

@push('styles')
    <style>
        /* CSS cho bảng có nhiều cột */
        .table-nowrap th,
        .table-nowrap td {
            white-space: nowrap !important;
        }

        @media (min-width: 768px) {
            .sticky-col-right {
                position: sticky;
                right: 0;
                background-color: #fff !important;
                z-index: 2;
                box-shadow: -4px 0 8px rgba(0, 0, 0, 0.05);
            }

            .table-hover tbody tr:hover .sticky-col-right {
                background-color: #f8f9fa !important;
            }

            /* Hỗ trợ chế độ tối (dark mode) */
            html.dark .table tbody tr td.sticky-col-right,
            html.dark .table thead tr th.sticky-col-right {
                background-color: #1e293b !important;
                box-shadow: -4px 0 8px rgba(0, 0, 0, 0.2);
            }

            html.dark .table-hover tbody tr:hover td.sticky-col-right {
                background-color: #242f41 !important;
            }
        }

        @media (max-width: 767.98px) {
            .sticky-col-right {
                position: static !important;
                box-shadow: none !important;
            }

            .table-nowrap {
                font-size: 0.8rem;
            }

            .table-nowrap .btn-group .btn {
                padding: 0.2rem 0.35rem;
                font-size: 0.75rem;
            }
        }

        /* Custom thanh cuộn ngang mỏng cho bảng */
        .table-responsive::-webkit-scrollbar {
            height: 5px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

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
                                        $question = $filterQuestions->firstWhere('id', $qId);
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

        <!-- Biểu đồ Thống kê Thiết bị, Hệ điều hành & Nguồn truy cập -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê Loại thiết bị</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;"><canvas id="deviceTypeChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê Hệ điều hành</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;"><canvas id="deviceOsChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê Nguồn truy cập</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;"><canvas id="accessSourceChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-funnel"></i> Bộ lọc báo cáo khảo sát
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}" id="filterForm">
                    <div class="row g-3">
                        @if(isset($filterQuestions) && $filterQuestions->count() > 0)
                            @foreach($filterQuestions as $q)
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
                        @endif

                        {{-- Lọc theo thiết bị & hệ điều hành --}}
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small text-muted mb-1">Loại thiết bị</label>
                            <select class="form-select form-select-sm" name="device_filter">
                                <option value="">-- Tất cả thiết bị --</option>
                                <option value="Desktop" {{ ($deviceFilter ?? '') === 'Desktop' ? 'selected' : '' }}>Máy tính (Desktop)</option>
                                <option value="Mobile" {{ ($deviceFilter ?? '') === 'Mobile' ? 'selected' : '' }}>Điện thoại (Mobile)</option>
                                <option value="Tablet" {{ ($deviceFilter ?? '') === 'Tablet' ? 'selected' : '' }}>Máy tính bảng (Tablet)</option>
                                <option value="Bot" {{ ($deviceFilter ?? '') === 'Bot' ? 'selected' : '' }}>Robot / Crawler</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small text-muted mb-1">Hệ điều hành</label>
                            <select class="form-select form-select-sm" name="os_filter">
                                <option value="">-- Tất cả hệ điều hành --</option>
                                <option value="Windows" {{ ($osFilter ?? '') === 'Windows' ? 'selected' : '' }}>Windows</option>
                                <option value="Android" {{ ($osFilter ?? '') === 'Android' ? 'selected' : '' }}>Android</option>
                                <option value="iOS" {{ ($osFilter ?? '') === 'iOS' ? 'selected' : '' }}>iOS (iPhone/iPad)</option>
                                <option value="macOS" {{ ($osFilter ?? '') === 'macOS' ? 'selected' : '' }}>macOS</option>
                                <option value="Linux" {{ ($osFilter ?? '') === 'Linux' ? 'selected' : '' }}>Linux</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small text-muted mb-1">Nguồn truy cập</label>
                            <select class="form-select form-select-sm" name="source_filter">
                                <option value="">-- Tất cả nguồn --</option>
                                <option value="Trực tiếp" {{ ($sourceFilter ?? '') === 'Trực tiếp' ? 'selected' : '' }}>Trực tiếp (Trình duyệt gốc)</option>
                                <option value="Zalo App" {{ ($sourceFilter ?? '') === 'Zalo App' ? 'selected' : '' }}>Zalo App</option>
                                <option value="Facebook App" {{ ($sourceFilter ?? '') === 'Facebook App' ? 'selected' : '' }}>Facebook App</option>
                                <option value="Messenger App" {{ ($sourceFilter ?? '') === 'Messenger App' ? 'selected' : '' }}>Messenger App</option>
                                <option value="Instagram App" {{ ($sourceFilter ?? '') === 'Instagram App' ? 'selected' : '' }}>Instagram App</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm" type="submit">
                                    <i class="bi bi-filter"></i> Áp dụng bộ lọc
                                </button>
                                @if(!empty($personalInfoFilters) || !empty($deviceFilter) || !empty($osFilter))
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
                                <div class="ajax-answers-container"
                                    data-url="{{ route('admin.bao-cao.question-answers', ['dotKhaoSat' => $dotKhaoSat->id, 'cauHoi' => $cauHoi->id]) }}"
                                    data-personal-filters="{{ json_encode($personalInfoFilters) }}" 
                                    data-device-filter="{{ $deviceFilter ?? '' }}"
                                    data-os-filter="{{ $osFilter ?? '' }}"
                                    data-source-filter="{{ $sourceFilter ?? '' }}"
                                    data-current-page="1"
                                    data-total="{{ $stats['total'] }}" data-per-page="20" data-last-page="{{ ceil($stats['total'] / 20) }}">
                                    <div class="answers-list-wrapper">
                                        <ul class="list-group list-group-flush mb-0">
                                            @foreach($stats['data'] as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>{{ $item->giatri_text }}</span>
                                                    <button class="btn btn-sm btn-link text-info p-0"
                                                        onclick="showResponseDetail({{ $item->phieu_khaosat_id }})" title="Xem người trả lời">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'number_stats')
                                @if(!empty($stats['cauTraLoi']) && is_iterable($stats['cauTraLoi']))
                                    <div class="ajax-answers-container"
                                        data-url="{{ route('admin.bao-cao.question-answers', ['dotKhaoSat' => $dotKhaoSat->id, 'cauHoi' => $cauHoi->id]) }}"
                                        data-personal-filters="{{ json_encode($personalInfoFilters) }}" 
                                        data-device-filter="{{ $deviceFilter ?? '' }}"
                                        data-os-filter="{{ $osFilter ?? '' }}"
                                        data-source-filter="{{ $sourceFilter ?? '' }}"
                                        data-current-page="1"
                                        data-total="{{ $stats['total'] }}" data-per-page="20" data-last-page="{{ ceil($stats['total'] / 20) }}">
                                        <div class="answers-list-wrapper">
                                            <ul class="list-group list-group-flush mb-2">
                                                @foreach($stats['cauTraLoi'] as $item)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span>{{ intval($item->giatri_number) }}</span>
                                                        <button class="btn btn-sm btn-link text-info p-0"
                                                            onclick="showResponseDetail({{ $item->phieu_khaosat_id }})" title="Xem người trả lời">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
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
                                <div class="ajax-answers-container"
                                    data-url="{{ route('admin.bao-cao.question-answers', ['dotKhaoSat' => $dotKhaoSat->id, 'cauHoi' => $cauHoi->id]) }}"
                                    data-personal-filters="{{ json_encode($personalInfoFilters) }}" 
                                    data-device-filter="{{ $deviceFilter ?? '' }}"
                                    data-os-filter="{{ $osFilter ?? '' }}"
                                    data-source-filter="{{ $sourceFilter ?? '' }}"
                                    data-current-page="1"
                                    data-total="{{ $stats['total'] }}" data-per-page="20" data-last-page="{{ ceil($stats['total'] / 20) }}">
                                    <div class="answers-list-wrapper">
                                        <ul class="list-group list-group-flush mb-0">
                                            @foreach($stats['data'] as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>{{ $item->giatri_text }}</span>
                                                    <button class="btn btn-sm btn-link text-info p-0"
                                                        onclick="showResponseDetail({{ $item->phieu_khaosat_id }})" title="Xem người trả lời">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @elseif($stats['type'] == 'number_stats')
                                @if(!empty($cauHoi->is_personal_info))
                                    @if(!empty($stats['cauTraLoi']) && is_iterable($stats['cauTraLoi']))
                                        <div class="ajax-answers-container"
                                            data-url="{{ route('admin.bao-cao.question-answers', ['dotKhaoSat' => $dotKhaoSat->id, 'cauHoi' => $cauHoi->id]) }}"
                                            data-personal-filters="{{ json_encode($personalInfoFilters) }}" 
                                            data-device-filter="{{ $deviceFilter ?? '' }}"
                                            data-os-filter="{{ $osFilter ?? '' }}"
                                            data-source-filter="{{ $sourceFilter ?? '' }}"
                                            data-current-page="1"
                                            data-total="{{ $stats['total'] }}" data-per-page="20" data-last-page="{{ ceil($stats['total'] / 20) }}">
                                            <div class="answers-list-wrapper">
                                                <ul class="list-group list-group-flush mb-2">
                                                    @foreach($stats['cauTraLoi'] as $item)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span>{{ intval($item->giatri_number) }}</span>
                                                            <button class="btn btn-sm btn-link text-info p-0"
                                                                onclick="showResponseDetail({{ $item->phieu_khaosat_id }})" title="Xem người trả lời">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
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
                    <table class="table table-hover align-middle mb-0 table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 70px;">STT</th>
                                @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                    @foreach($personalInfoQuestions as $q)
                                        <th scope="col">{{ $q->noidung_cauhoi }}</th>
                                    @endforeach
                                @endif
                                <th scope="col">IP Người gửi</th>
                                <th scope="col">Thiết bị</th>
                                <th scope="col">Thời gian làm bài</th>
                                <th scope="col" class="text-end sticky-col-right border-start">Thao tác</th>
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
                                        <code class="text-muted">{{ $surveyMetadata[$phieu->id]['ip'] ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        @php
                                            $devType = $surveyMetadata[$phieu->id]['device_type'] ?? 'Desktop';
                                            $devSummary = $surveyMetadata[$phieu->id]['device_summary'] ?? 'N/A';
                                            $icon = 'bi-laptop';
                                            if ($devType === 'Mobile') $icon = 'bi-phone';
                                            elseif ($devType === 'Tablet') $icon = 'bi-tablet';
                                            elseif ($devType === 'Bot') $icon = 'bi-robot';
                                        @endphp
                                        <span class="text-secondary" data-bs-toggle="tooltip" title="{{ $devSummary }}">
                                            <i class="bi {{ $icon }} me-1"></i>
                                            <span style="font-size: 0.85rem;">{{ Str::limit($devSummary, 25) }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        {{ $phieu->thoigian_batdau ? $phieu->thoigian_batdau->format('d/m/Y H:i') : 'N/A' }} -
                                        {{ $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-end sticky-col-right border-start">
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
            <div class="card shadow mb-4 border-warning" id="duplicateSurvey">
                <div class="card-header bg-amber-100 bg-opacity-25">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Danh sách phiếu bị đánh dấu trùng lặp ({{ $danhSachPhieuTrungLap->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-nowrap">
                            <thead class="table-light">
                                <tr>
                                    @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                        @foreach($personalInfoQuestions as $q)
                                            <th scope="col">{{ $q->noidung_cauhoi }}</th>
                                        @endforeach
                                    @endif
                                    <th scope="col">IP Người gửi</th>
                                    <th scope="col">Thiết bị</th>
                                    <th scope="col">Thời gian làm bài</th>
                                    <th scope="col" class="text-end sticky-col-right border-start">Thao tác</th>
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
                                            <code class="text-muted">{{ $surveyMetadata[$phieu->id]['ip'] ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            @php
                                                $devType = $surveyMetadata[$phieu->id]['device_type'] ?? 'Desktop';
                                                $devSummary = $surveyMetadata[$phieu->id]['device_summary'] ?? 'N/A';
                                                $icon = 'bi-laptop';
                                                if ($devType === 'Mobile') $icon = 'bi-phone';
                                                elseif ($devType === 'Tablet') $icon = 'bi-tablet';
                                                elseif ($devType === 'Bot') $icon = 'bi-robot';
                                            @endphp
                                            <span class="text-secondary" data-bs-toggle="tooltip" title="{{ $devSummary }}">
                                                <i class="bi {{ $icon }} me-1"></i>
                                                <span style="font-size: 0.85rem;">{{ Str::limit($devSummary, 25) }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            {{ $phieu->thoigian_batdau ? $phieu->thoigian_batdau->format('d/m/Y H:i') : 'N/A' }} -
                                            {{ $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                        <td class="text-end sticky-col-right border-start">
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

            // Biểu đồ Thống kê Loại thiết bị
            const deviceTypeCtx = document.getElementById('deviceTypeChart')?.getContext('2d');
            if (deviceTypeCtx) {
                const deviceTypeData = @json($thongKeThietBi['devices'] ?? ['labels'=>[], 'values'=>[]]);
                new Chart(deviceTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: deviceTypeData.labels,
                        datasets: [{
                            data: deviceTypeData.values,
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 15, padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const dataset = context.dataset;
                                        const total = dataset.data.reduce((sum, val) => sum + val, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${label}: ${value} phiếu (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Biểu đồ Thống kê Hệ điều hành
            const deviceOsCtx = document.getElementById('deviceOsChart')?.getContext('2d');
            if (deviceOsCtx) {
                const deviceOsData = @json($thongKeThietBi['os'] ?? ['labels'=>[], 'values'=>[]]);
                new Chart(deviceOsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: deviceOsData.labels,
                        datasets: [{
                            data: deviceOsData.values,
                            backgroundColor: ['#1cc88a', '#36b9cc', '#4e73df', '#f6c23e', '#e74a3b', '#858796'],
                            hoverBackgroundColor: ['#17a673', '#2c9faf', '#2e59d9', '#f4b619', '#e02d1b', '#717384'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 15, padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const dataset = context.dataset;
                                        const total = dataset.data.reduce((sum, val) => sum + val, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${label}: ${value} phiếu (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Biểu đồ Thống kê Nguồn truy cập
            const accessSourceCtx = document.getElementById('accessSourceChart')?.getContext('2d');
            if (accessSourceCtx) {
                const accessSourceData = @json($thongKeThietBi['sources'] ?? ['labels'=>[], 'values'=>[]]);
                new Chart(accessSourceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: accessSourceData.labels,
                        datasets: [{
                            data: accessSourceData.values,
                            backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b', '#36b9cc', '#f6c23e'],
                            hoverBackgroundColor: ['#2e59d9', '#17a673', '#e02d1b', '#2c9faf', '#f4b619'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 15, padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const dataset = context.dataset;
                                        const total = dataset.data.reduce((sum, val) => sum + val, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${label}: ${value} phiếu (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Khởi tạo Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

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
                    cauhoi_id: questionId,
                    personal_info_filters: @json($personalInfoFilters),
                    device_filter: '{{ $deviceFilter ?? "" }}',
                    os_filter: '{{ $osFilter ?? "" }}',
                    source_filter: '{{ $sourceFilter ?? "" }}'
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
                    
                    // Thêm thông tin truy cập IP và thiết bị vào popup
                    let appInfo = phieuData.device_app ? ` (${phieuData.device_app} App)` : '';
                    let sourceBadgeClass = 'bg-secondary';
                    if (phieuData.device_source === 'Zalo App') sourceBadgeClass = 'bg-primary bg-opacity-75';
                    else if (phieuData.device_source === 'Facebook App') sourceBadgeClass = 'bg-primary';
                    else if (phieuData.device_source === 'Messenger App') sourceBadgeClass = 'bg-info text-dark';
                    else if (phieuData.device_source === 'Instagram App') sourceBadgeClass = 'bg-danger';

                    html += `<h5><i class="bi bi-info-circle text-primary me-2"></i>Thông tin truy cập</h5>
                            <table class="table table-sm table-bordered mb-4">
                                <tbody>
                                    <tr>
                                        <td width="40%"><strong>Địa chỉ IP</strong></td>
                                        <td><code>${phieuData.ip_address || 'N/A'}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nguồn truy cập</strong></td>
                                        <td><span class="badge ${sourceBadgeClass}">${phieuData.device_source || 'N/A'}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hệ điều hành</strong></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                ${phieuData.device_os || 'N/A'}${phieuData.device_os_version ? ' ' + phieuData.device_os_version : ''}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Thiết bị / Trình duyệt</strong></td>
                                        <td>
                                            <span class="text-secondary">
                                                ${phieuData.device_type || 'N/A'} - ${phieuData.device_browser || 'N/A'}${appInfo}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>`;

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

                // Lấy personal info filters, device_filter, os_filter và source_filter
                for (const [key, value] of formData.entries()) {
                    if (value && (key.startsWith('personal_info_filters[') || key === 'device_filter' || key === 'os_filter' || key === 'source_filter')) {
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
            @if(!empty($personalInfoFilters) || !empty($deviceFilter) || !empty($osFilter) || !empty($sourceFilter))
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

            // Phân trang Server-side qua Ajax cho danh sách câu trả lời (.ajax-answers-container)
            document.querySelectorAll('.ajax-answers-container').forEach(function (container) {
                const url = container.getAttribute('data-url');
                const filters = JSON.parse(container.getAttribute('data-personal-filters') || '{}');
                const deviceFilter = container.getAttribute('data-device-filter') || '';
                const osFilter = container.getAttribute('data-os-filter') || '';
                const sourceFilter = container.getAttribute('data-source-filter') || '';
                const total = parseInt(container.getAttribute('data-total')) || 0;
                const perPage = parseInt(container.getAttribute('data-per-page')) || 20;
                const lastPage = parseInt(container.getAttribute('data-last-page')) || 1;

                const listWrapper = container.querySelector('.answers-list-wrapper');
                // Lưu lại HTML gốc của Trang 1 được render bởi Blade
                container.originalPage1Html = listWrapper.innerHTML;

                let currentPage = 1;

                function loadPage(page) {
                    currentPage = page;

                    if (currentPage === 1) {
                        // Giải phóng minHeight và khôi phục nhanh Trang 1 từ bộ nhớ
                        listWrapper.style.minHeight = '';
                        listWrapper.innerHTML = container.originalPage1Html;
                        renderPaginationControls();
                        return;
                    }

                    // Đo chiều cao hiện tại để giữ chiều cao trong lúc tải, tránh sụt layout (giật trang)
                    const currentHeight = listWrapper.offsetHeight;
                    if (currentHeight > 0) {
                        listWrapper.style.minHeight = currentHeight + 'px';
                    }

                    listWrapper.innerHTML = `
                                <div class="d-flex align-items-center justify-content-center text-muted" style="min-height: ${currentHeight || 150}px;">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Đang tải câu trả lời...
                                </div>
                            `;

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            page: currentPage,
                            per_page: perPage,
                            personal_info_filters: filters,
                            device_filter: deviceFilter,
                            os_filter: osFilter,
                            source_filter: sourceFilter
                        },
                        success: function (response) {
                            renderData(response);
                        },
                        error: function (xhr) {
                            listWrapper.style.minHeight = '';
                            listWrapper.innerHTML = `
                                        <div class="alert alert-danger py-2 mb-0" style="font-size:0.85rem;">
                                            Không thể tải dữ liệu câu trả lời. Vui lòng thử lại.
                                        </div>
                                    `;
                        }
                    });
                }

                function renderData(response) {
                    // Giải phóng chiều cao cố định sau khi đã có dữ liệu mới
                    listWrapper.style.minHeight = '';

                    const items = response.data;
                    const totalItems = response.total;

                    if (totalItems === 0) {
                        listWrapper.innerHTML = '<p class="text-muted text-center mb-0">Chưa có câu trả lời.</p>';
                        return;
                    }

                    let html = '<ul class="list-group list-group-flush mb-0">';
                    items.forEach(function (item) {
                        html += `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>${escapeHtml(String(item.value))}</span>
                                        <button class="btn btn-sm btn-link text-info p-0" onclick="showResponseDetail(${item.phieu_khaosat_id})" title="Xem người trả lời">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </li>
                                `;
                    });
                    html += '</ul>';

                    listWrapper.innerHTML = html;
                    renderPaginationControls();
                }

                function renderPaginationControls() {
                    if (lastPage <= 1) return;

                    const start = (currentPage - 1) * perPage + 1;
                    const end = Math.min(currentPage * perPage, total);

                    // Xóa pagination điều hướng cũ nếu có
                    const oldPagination = container.querySelector('.ajax-pagination-nav');
                    if (oldPagination) {
                        oldPagination.remove();
                    }

                    const paginationHtml = `
                                <div class="ajax-pagination-nav d-flex justify-content-between align-items-center mt-2 px-3 py-2 bg-light rounded-bottom border" style="font-size: 0.8rem;">
                                    <span class="text-muted fw-medium">
                                        Hiển thị ${start}-${end} / ${total}
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-prev-ajax" ${currentPage === 1 ? 'disabled' : ''}>
                                            <i class="bi bi-chevron-left" style="font-size: 0.75rem;"></i>
                                        </button>
                                        <div class="d-flex align-items-center gap-1">
                                            <span>Trang</span>
                                            <input type="number" class="form-control form-control-sm text-center input-page-ajax" value="${currentPage}" min="1" max="${lastPage}" style="width: 50px; height: 26px; padding: 2px;">
                                            <span>/ ${lastPage}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-next-ajax" ${currentPage === lastPage ? 'disabled' : ''}>
                                            <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            `;

                    container.insertAdjacentHTML('beforeend', paginationHtml);

                    // Gắn sự kiện
                    const nav = container.querySelector('.ajax-pagination-nav');
                    const prevBtn = nav.querySelector('.btn-prev-ajax');
                    const nextBtn = nav.querySelector('.btn-next-ajax');
                    const pageInput = nav.querySelector('.input-page-ajax');

                    if (prevBtn) {
                        prevBtn.addEventListener('click', function (e) {
                            e.preventDefault();
                            loadPage(currentPage - 1);
                        });
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', function (e) {
                            e.preventDefault();
                            loadPage(currentPage + 1);
                        });
                    }

                    if (pageInput) {
                        pageInput.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                handlePageJump();
                            }
                        });

                        pageInput.addEventListener('blur', function (e) {
                            handlePageJump();
                        });

                        function handlePageJump() {
                            let pageVal = parseInt(pageInput.value);
                            if (isNaN(pageVal) || pageVal < 1) {
                                pageVal = 1;
                            } else if (pageVal > lastPage) {
                                pageVal = lastPage;
                            }
                            if (pageVal !== currentPage) {
                                loadPage(pageVal);
                            }
                        }
                    }
                }

                // Vẽ thanh điều hướng phân trang cho Trang 1 (không cần gọi Ajax)
                renderPaginationControls();
            });

            // Ajax phân trang cho Danh sách phiếu đã hoàn thành (#detailSurvey)
            $(document).on('click', '#detailSurvey .pagination a', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url) return;

                const $container = $('#detailSurvey');
                const currentHeight = $container.outerHeight();
                $container.css({
                    'min-height': currentHeight + 'px',
                    'opacity': '0.6'
                });

                $.get(url, function (html) {
                    const $newHtml = $(html).find('#detailSurvey').html();
                    $container.html($newHtml);
                    $container.css({
                        'min-height': '',
                        'opacity': ''
                    });

                    // Cuộn mượt tới vị trí bảng
                    document.getElementById('detailSurvey').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }).fail(function () {
                    $container.css({
                        'min-height': '',
                        'opacity': ''
                    });
                    alert('Không thể tải danh sách phiếu. Vui lòng thử lại.');
                });
            });

            // Ajax phân trang cho Danh sách phiếu trùng lặp (#duplicateSurvey)
            $(document).on('click', '#duplicateSurvey .pagination a', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url) return;

                const $container = $('#duplicateSurvey');
                const currentHeight = $container.outerHeight();
                $container.css({
                    'min-height': currentHeight + 'px',
                    'opacity': '0.6'
                });

                $.get(url, function (html) {
                    const $newHtml = $(html).find('#duplicateSurvey').html();
                    $container.html($newHtml);
                    $container.css({
                        'min-height': '',
                        'opacity': ''
                    });

                    // Cuộn mượt tới vị trí bảng
                    document.getElementById('duplicateSurvey').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }).fail(function () {
                    $container.css({
                        'min-height': '',
                        'opacity': ''
                    });
                    alert('Không thể tải danh sách phiếu. Vui lòng thử lại.');
                });
            });
        });
    </script>
@endpush