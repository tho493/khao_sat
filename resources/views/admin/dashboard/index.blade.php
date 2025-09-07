@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/splash-screen.css') }}">
@endpush

@section('splash-screen')
    @include('layouts.splash-screen')
@endsection

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Chào mừng {{ Auth::user()->hoten }} đến với trang quản trị</h1>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Người dùng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_users']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Đợt đang hoạt động
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['active_surveys'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Phản hồi tháng này
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_responses']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-text fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Mẫu khảo sát
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_templates'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clipboard-data fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Biểu đồ phản hồi -->
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <a href="{{ route('admin.bao-cao.index') }}" class="card-header py-3" style="text-decoration: none;">
                        <h6 class="m-0 font-weight-bold text-primary">Lượt phản hồi hoàn thành (7 ngày qua)</h6>
                    </a>
                    <div class="card-body">
                        @if(isset($responseChart) && collect($responseChart['values'])->sum() > 0)
                            <div class="chart-area" style="height: 320px;">
                                <canvas id="responseChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center"
                                style="height: 320px;">
                                <i class="bi bi-graph-up-arrow fs-1 text-gray-300"></i>
                                <p class="text-muted mt-2">Không có phản hồi nào trong 7 ngày qua.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Thống kê phiếu theo đợt khảo sát -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <a href="{{ route('admin.bao-cao.index') }}" class="card-header py-3" style="text-decoration: none;">
                        <h6 class="m-0 font-weight-bold text-primary">Top 5 Đợt KS có nhiều phản hồi nhất</h6>
                    </a>
                    <div class="card-body">
                        @if(isset($surveyStatsChart) && $surveyStatsChart->sum('total_responses') > 0)
                            <div class="chart-pie" style="height: 320px;">
                                <canvas id="surveyStatsChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center"
                                style="height: 320px;">
                                <i class="bi bi-bar-chart-line fs-1 text-gray-300"></i>
                                <p class="text-muted mt-2">Chưa có dữ liệu phiếu để thống kê.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Hoạt động gần đây -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <a href="{{ route('admin.logs.index') }}" class="card-header py-3" style="text-decoration: none;">
                    <h6 class="m-0 font-weight-bold text-primary">Hoạt động gần đây</h6>
                </a>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Người thực hiện</th>
                                    <th>Hành động</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                    <tr>
                                        <td>{{ $activity->nguoi_thuchien }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $activity->hanhdong == 'create' ? 'success' : ($activity->hanhdong == 'delete' ? 'danger' : 'info') }}">
                                                {{ $activity->hanhdong }}
                                            </span>
                                            {{ $activity->bang_thaydoi }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($activity->thoigian)->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Người dùng hoạt động -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <a href="{{ route('admin.users.index') }}" class="card-header py-3" style="text-decoration: none;">
                    <h6 class="m-0 font-weight-bold text-primary">Các tài khoản đăng nhập gần đây</h6>
                </a>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Họ tên</th>
                                    <th>Đăng nhập lần cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeUsers as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->tendangnhap }}</td>
                                        <td>{{ $user->hoten }}</td>
                                        <td>{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Chưa từng đăng nhập' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
            // --- Biểu đồ Phản hồi (Line Chart) ---
            const responseCtx = document.getElementById('responseChart')?.getContext('2d');
            if (responseCtx) {
                const responseData = @json($responseChart);
                new Chart(responseCtx, {
                    type: 'line',
                    data: {
                        labels: responseData.labels,
                        datasets: [{
                            label: 'Số phản hồi',
                            data: responseData.values,
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4e73df',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // --- Biểu đồ Thống kê theo Đợt KS (Doughnut Chart) ---
            const surveyStatsCtx = document.getElementById('surveyStatsChart')?.getContext('2d');
            if (surveyStatsCtx) {
                const surveyStatsData = @json($surveyStatsChart);
                new Chart(surveyStatsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: surveyStatsData.map(item => item.ten_dot),
                        datasets: [{
                            data: surveyStatsData.map(item => item.total_responses),
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, boxWidth: 12 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        if (label.length > 30) {
                                            label = label.substring(0, 30) + '...';
                                        }
                                        if (label) label += ': ';
                                        if (context.parsed !== null) {
                                            label += new Intl.NumberFormat('vi-VN').format(context.parsed) + ' phiếu';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>

    <script src="{{ asset('js/splash-screen.js') }}"></script>
@endpush