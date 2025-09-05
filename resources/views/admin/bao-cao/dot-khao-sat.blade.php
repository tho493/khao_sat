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
                        <span class="fw-bold">
                            {{ \Carbon\Carbon::parse($dotKhaoSat->tungay)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('d/m/Y') }}
                        </span>
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
            <div class="row mb-4">
                {{-- Cards thống kê như trong dashboard --}}
            </div>

            {{-- Biểu đồ phản hồi theo ngày --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold text-primary">Phản hồi theo ngày</h6>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="dailyResponseChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            {{-- Thống kê chi tiết từng câu hỏi --}}
            <h3 class="h4 mb-3">Phân tích câu trả lời</h3>
            @forelse($dotKhaoSat->mauKhaoSat->cauHoi->sortBy('thutu') as $index => $cauHoi)
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
                            @if($stats['type'] == 'chart' && !empty($stats['data']) && $stats['data']->isNotEmpty())
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
                                <p class="text-muted text-center mb-0">Không có dữ liệu phù hợp để hiển thị cho loại câu hỏi này.</p>
                            @endif
                        @else
                            <p class="text-muted text-center mb-0">Chưa có dữ liệu cho câu hỏi này.</p>
                        @endif {{-- <--- @endif BỊ THIẾU LÀ Ở ĐÂY --}}
                    </div>
                </div>
            @empty
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <p class="text-muted text-center mb-0">Mẫu khảo sát này chưa có câu hỏi nào.</p>
                    </div>
                </div>
            @endforelse

            {{-- Danh sách chi tiết phiếu trả lời --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold text-primary">Danh sách phiếu đã hoàn thành</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Mã người trả lời</th>
                                    <th scope="col">Họ tên</th>
                                    <th scope="col">Đơn vị</th>
                                    <th scope="col">Thời gian hoàn thành</th>
                                    <!-- <th scope="col">Thao tác</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($danhSachPhieu as $phieu)
                                    <tr>
                                        <td>{{ $phieu->ma_nguoi_traloi }}</td>
                                        <td>{{ $phieu->metadata['hoten'] ?? 'N/A' }}</td>
                                        <td>{{ $phieu->metadata['donvi'] ?? 'N/A' }}</td>
                                        <td>
                                            {{ $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                        <!-- <td>
                                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết phiếu" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td> -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Chưa có phiếu nào được hoàn thành.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{-- $danhSachPhieu->links() --}}
                        @if ($danhSachPhieu->hasPages())
                            {{ $danhSachPhieu->withQueryString()->links() }}
                        @endif
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
                        {{-- Nội dung tóm tắt --}}
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
@endsection

@push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Biểu đồ phản hồi theo ngày
                const dailyCtx = document.getElementById('dailyResponseChart')?.getContext('2d');
                if (dailyCtx) {
                    const dailyData = @json($thongKeTheoNgay);
                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: dailyData.map(item => new Date(item.ngay).toLocaleDateString('vi-VN')),
                            datasets: [{
                                label: 'Số phiếu hoàn thành',
                                data: dailyData.map(item => item.so_luong),
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: true }
                    });
                }

                // Biểu đồ cho từng câu hỏi
                @foreach($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi)
                    @php $stats = $thongKeCauHoi[$cauHoi->id]; @endphp
                    @if($stats['type'] == 'chart' && !$stats['data']->isEmpty())
                        const ctx{{ $cauHoi->id }} = document.getElementById('chart-cauhoi-{{ $cauHoi->id }}')?.getContext('2d');
                        if (ctx{{ $cauHoi->id }}) {
                            new Chart(ctx{{ $cauHoi->id }}, {
                                type: 'pie', // hoặc 'bar'
                                data: {
                                    labels: {!! json_encode($stats['data']->pluck('noidung')) !!},
                                    datasets: [{
                                        data: {!! json_encode($stats['data']->pluck('so_luong')) !!},
                                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } }
                                }
                            });
                        }
                    @endif
                @endforeach
            });
        </script>

        <script>
        const summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));

        function requestSummary(questionId, questionContext) {
            // Reset modal content
            $('#summaryQuestionContext').text('Câu hỏi: ' + questionContext + '.');
            $('#summaryContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">AI đang phân tích và tóm tắt... Vui lòng chờ trong giây lát.</p>
                </div>
            `);
            summaryModal.show();

            // Gửi request AJAX
            $.ajax({
                url: "{{ route('admin.bao-cao.summarize', $dotKhaoSat) }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cauhoi_id: questionId
                },
                 success: function(response) {
            $('#summaryContent').html(response.summary);
            },
            error: function(xhr) {
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
    </script>
@endpush