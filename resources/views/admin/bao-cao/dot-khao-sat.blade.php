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

        {{-- Header Báo cáo --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-8 col-12">
                <h1 class="h3 mb-1">{{ $dotKhaoSat->ten_dot }}</h1>
                <p class="text-muted mb-0">
                    <span class="fw-semibold">Tên đợt khảo sát:</span>
                    <span class="fw-bold">{{ $dotKhaoSat->ten_dot ?? 'N/A' }}</span>
                    <span class="mx-2">|</span>
                    <span class="fw-semibold">Thời gian:</span>
                    <span class="fw-bold">{{ $dotKhaoSat->tungay }} - {{ $dotKhaoSat->denngay }}</span>
                </p>
            </div>
            <div class="col-md-4 col-12 text-md-end mt-3 mt-md-0">
                <div class="btn-group" role="group" aria-label="Export group">
                    <a href="{{ route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'excel']) }}"
                        class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                    </a>
                    <a href="{{ route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat, 'format' => 'pdf']) }}"
                        class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Thống kê tổng quan --}}
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tổng quan Kết quả</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <i class="bi bi-check2-all fs-1 text-success"></i>
                        <div class="h4 mt-2 font-weight-bold text-gray-800">
                            {{ number_format($tongQuan['phieu_hoan_thanh']) }}
                        </div>
                        <div class="text-xs font-weight-bold text-success text-uppercase">Phiếu hoàn thành</div>
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
                <h6 class="m-0 font-weight-bold text-primary">Xu hướng phản hồi theo ngày</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;"><canvas id="responseTrendChart"></canvas></div>
            </div>
        </div>

        {{-- Thống kê chi tiết từng câu hỏi --}}
        <h3 class="h4 mb-3">Phân tích câu trả lời</h3>
        @forelse($dotKhaoSat->mauKhaoSat->cauHoi as $index => $cauHoi)
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold text-primary">
                            Câu {{ $index + 1 }}: {{ $cauHoi->noidung_cauhoi }}
                        </h6>
                        <small class="text-muted">({{ $thongKeCauHoi[$cauHoi->id]['total'] ?? 0 }} lượt trả lời)</small>
                    </div>
                    @if($cauHoi->loai_cauhoi === 'text' && ($thongKeCauHoi[$cauHoi->id]['total'] ?? 0) > 0)
                        <button class="btn btn-sm btn-outline-info"
                            onclick="requestSummary({{ $cauHoi->id }}, '{{ e($cauHoi->noidung_cauhoi) }}')">
                            <i class="bi bi-robot"></i> Tóm tắt bằng AI
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @php
                        $stats = $thongKeCauHoi[$cauHoi->id] ?? null;
                    @endphp

                    @if($stats && $stats['total'] > 0)
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
                            <div class="row text-center">
                                <div class="col">
                                    <div class="h5">{{ number_format($stats['data']->avg, 2) }}</div>
                                    <div class="text-muted small">Trung bình</div>
                                </div>
                                <div class="col">
                                    <div class="h5">{{ number_format($stats['data']->min) }}</div>
                                    <div class="text-muted small">Nhỏ nhất</div>
                                </div>
                                <div class="col">
                                    <div class="h5">{{ number_format($stats['data']->max) }}</div>
                                    <div class="text-muted small">Lớn nhất</div>
                                </div>
                            </div>
                        @else
                            @if($cauHoi->loai_cauhoi === 'select_ctdt')
                                <p class="text-muted text-center mb-0">Không có dữ liệu CTĐT để hiển thị.</p>
                            @else
                                <p class="text-muted text-center mb-0">Không có dữ liệu phù hợp để hiển thị cho loại câu hỏi này.</p>
                            @endif
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">Chưa có dữ liệu cho câu hỏi này.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="card shadow mb-4">

                <div class="card-body">
                    <p class="text-muted text-center mb-0">Mẫu khảo sát này chưa có câu hỏi nào.</p>
                </div>
            </div>
        @endforelse

        {{-- Danh sách chi tiết phiếu trả lời với form lọc --}}
        <div class="card shadow mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu đã hoàn thành</h6>
                    </div>
                    @if(isset($availableCtdts) && $availableCtdts->isNotEmpty())
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}" id="filterForm">
                                <div class="input-group">
                                    <select class="form-select" name="ctdt"
                                        onchange="document.getElementById('filterForm').submit()">
                                        <option value="">-- Lọc theo Chương trình đào tạo --</option>
                                        @foreach($availableCtdts as $ctdt)
                                            <option value="{{ $ctdt->mactdt }}" {{ $selectedCtdt == $ctdt->mactdt ? 'selected' : '' }}>
                                                {{ $ctdt->tenctdt }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {{-- Nút xóa lọc --}}
                                    @if($selectedCtdt)
                                        <a href="{{ route('admin.bao-cao.dot-khao-sat', $dotKhaoSat) }}"
                                            class="btn btn-outline-secondary" title="Bỏ lọc">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        {{-- ... thead và tbody của bảng giữ nguyên ... --}}
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
                            @forelse($danhSachPhieu as $phieu)
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
                                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết phiếu"
                                            onclick="showResponseDetail({{ $phieu->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ (isset($personalInfoQuestions) ? $personalInfoQuestions->count() : 0) + 2 }}"
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
                            html += `<div class="mb-3">
                                            <p class="mb-1"><strong>Câu ${index + 1}:</strong> ${escapeHtml(question.noidung_cauhoi)}</p>
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
    </script>
@endpush