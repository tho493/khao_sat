@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Chào mừng {{ Auth::user()->hoten }} đến với trang quản trị hệ thống khảo sát</h1>

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
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Phản hồi hoàn thành trong 7 ngày qua</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($responseChart) && collect($responseChart['values'])->sum() > 0)
                            <div class="chart-area" style="height: 320px;">
                                <canvas id="responseChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-graph-up-arrow fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Không có phản hồi nào trong 7 ngày qua.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thống kê phiếu theo đối tượng -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê phiếu theo đợt khảo sát</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($objectStats) && $objectStats->sum('total_responses') > 0)
                            <div class="chart-pie pt-4 pb-2" style="height: 250px;">
                                <canvas id="objectChart"></canvas>
                            </div>

                            <div class="table-responsive mt-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Đợt khảo sát</th>
                                            <th class="text-end">Số phiếu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($objectStats as $item)
                                            <tr>
                                                <td>{{ $item->ten_mau }}</td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($item->total_responses) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Chưa có dữ liệu phiếu hoàn thành để thống kê.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Hoạt động gần đây -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Hoạt động gần đây</h6>
                    </div>
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Các tài khoản đăng nhập gần đây</h6>
                    </div>
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
        // Biểu đồ phản hồi
        const responseCtx = document.getElementById('responseChart').getContext('2d');
        new Chart(responseCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($responseChart['labels']) !!},
                datasets: [{
                    label: 'Số phản hồi',
                    data: {!! json_encode($responseChart['values']) !!},
                    borderColor: 'rgb(78, 115, 223)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Biểu đồ đối tượng
        const objectCtx = document.getElementById('objectChart').getContext('2d');
        new Chart(objectCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($objectStats->pluck('ten_mau')) !!},
                datasets: [{
                    data: {!! json_encode($objectStats->pluck('total_responses')) !!},
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            label += new Intl.NumberFormat('vi-VN').format(context.parsed) + ' phiếu';
                        }
                        return label;
                    }
                }
            }
        });
    </script>
@endpush