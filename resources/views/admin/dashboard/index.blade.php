@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="/css/splash-screen.css">
@endpush


@section('splash-screen')
    @include('layouts.splash-screen')
    <script src="/js/splash-screen.js"></script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Chào mừng trở lại, {{ Auth::user()->hoten }}</h1>
                <a href="{{ route('admin.dot-khao-sat.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-circle"></i> Tạo đợt khảo sát mới
                </a>
            </div>
            <span class="text-muted mb-0">Đây là trang tổng quan về hệ thống quản lý khảo sát</span>
        </div>

        <!-- Stats 4 Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đang hoạt động</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($kpis['active_surveys']) }}
                                </div>
                            </div>
                            <div class="col-auto"><i class="bi bi-play-circle-fill fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Phản hồi (7 ngày)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($kpis['responses_last_7_days']) }}
                                </div>
                            </div>
                            <div class="col-auto"><i class="bi bi-check2-circle fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Phản hồi (tháng này)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($kpis['total_responses_month']) }}
                                </div>
                            </div>
                            <div class="col-auto"><i class="bi bi-calendar3 fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Thời gian làm bài TB
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpis['avg_completion_time'] }}</div>
                            </div>
                            <div class="col-auto"><i class="bi bi-clock-history fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h5 class="mb-3 text-gray-800">Hoạt động Hôm nay ({{ now()->format('d/m/Y') }})</h5>
            </div>

            <!-- Card Phiếu Hoàn thành Hôm nay -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Phiếu hoàn thành
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($todayStats['completed']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check2-all fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Phiếu Mới Bắt đầu Hôm nay -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Phiếu mới bắt đầu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($todayStats['started']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-pencil-square fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Đợt KS Mới Hôm nay -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đợt KS mới tạo</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($todayStats['new']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-plus-circle-dotted fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Đợt KS Hết hạn Hôm nay -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đợt KS hết hạn H.nay
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($todayStats['ending']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-x fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Xu hướng và phản hồi -->
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3 text-gray-800">Biểu đồ và thông tin</h5>
            </div>
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Xu hướng phản hồi (30 ngày qua)</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;"><canvas id="responseTrendChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Top 5 Mẫu KS được dùng nhiều nhất</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;"><canvas id="topTemplatesChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách và hoạt động -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-warning">Khảo sát sắp kết thúc (3 ngày tới)</h6>
                    </div>
                    <div class="card-body">
                        @if($endingSoonSurveys->isNotEmpty())
                            <div class="list-group list-group-flush">
                                @foreach($endingSoonSurveys as $dot)
                                    <a href="{{ route('admin.dot-khao-sat.show', $dot) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ Str::limit($dot->ten_dot, 45) }}</div>
                                            <small class="text-muted">Hạn cuối: {{ $dot->denngay }}</small>
                                        </div>
                                        <span class="badge bg-warning text-dark rounded-pill">
                                            {{ $dot->denngay->diffForHumans(now(), null, true, 2) }} sẽ kết thúc</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted p-3">Không có khảo sát nào sắp kết thúc.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Hoạt động gần đây</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($recentActivities as $activity)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $activity->nguoiThucHien->hoten ?? 'N/A' }}</strong> đã
                                        {{ $activity->hanhdong == 'create' ? 'tạo' : ($activity->hanhdong == 'update' ? 'cập nhật' : 'xóa') }}
                                        <span class="text-primary">{{ $activity->bang_thaydoi }}
                                            #{{ $activity->id_banghi }}</span>
                                    </div>
                                    <small class="text-muted">{{ $activity->thoigian->diffForHumans() }}</small>
                                </div>
                            @empty
                                <div class="text-center text-muted p-3">Chưa có hoạt động nào.</div>
                            @endforelse
                        </div>
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
                                label: 'Số phản hồi',
                                data: trendData.values,
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                fill: true, tension: 0.4
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
                    });
                }

                // Biểu đồ Top Mẫu Khảo sát
                const templatesCtx = document.getElementById('topTemplatesChart')?.getContext('2d');
                if (templatesCtx) {
                    const templatesData = @json($topTemplatesChart);
                    new Chart(templatesCtx, {
                        type: 'bar',
                        data: {
                            labels: templatesData.map(item => item.ten_mau.substring(0, 20) + (item.ten_mau.length > 20 ? '...' : '')),
                            datasets: [{
                                label: 'Số lần sử dụng',
                                data: templatesData.map(item => item.dot_khao_sat_count),
                                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true, maintainAspectRatio: false,
                            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            });
        </script>
    @endpush