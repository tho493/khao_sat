@extends('layouts.admin')

@section('title', 'Tổng quan Báo cáo')

@push('styles')
<style>
    .bao-cao-card-header {
        background: linear-gradient(90deg, #f8fafc 60%, #e3f0ff 100%);
        border-bottom: 1px solid #e3e6f0;
    }
    html.dark .bao-cao-card-header {
        background: linear-gradient(90deg, #1e293b 60%, #0f172a 100%) !important;
        border-bottom-color: rgba(255, 255, 255, 0.05) !important;
    }
    .search-input-custom {
        background-color: #f8fafc !important;
    }
    html.dark .search-input-custom {
        background-color: rgba(15, 23, 42, 0.6) !important;
        color: #f8fafc !important;
    }
    html.dark thead.table-light th {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: rgba(255, 255, 255, 0.05) !important;
    }
    .text-truncate-2-lines {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Làm đẹp thanh cuộn cho legend */
    #objectChartLegend::-webkit-scrollbar {
        width: 4px;
    }
    #objectChartLegend::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    #objectChartLegend::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    #objectChartLegend::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Báo cáo khảo sát</h1>
    </div>

    <!-- Hàng 1: Các thẻ thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng đợt KS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($tongQuan['tong_dot'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đang hoạt động</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($tongQuan['dot_active'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-play-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng phiếu KS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($tongQuan['tong_phieu'] ?? 0) }}</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Phiếu hoàn thành</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($tongQuan['phieu_hoanthanh'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng 2: Biểu đồ -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Số phiếu hoàn thành trong 12 tháng qua</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tỷ lệ phiếu theo mẫu khảo sát</h6>
                </div>
                <div class="card-body d-flex flex-column" style="height: 350px;">
                    <div class="chart-pie flex-grow-1" style="height: 180px;">
                        <canvas id="objectChart"></canvas>
                    </div>
                    <div id="objectChartLegend" class="mt-3 overflow-auto pe-2" style="max-height: 120px; font-size: 0.8rem; line-height: 1.4;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 bao-cao-card-header">
            <h6 class="m-0 font-weight-bold text-primary" style="font-size: 1.15rem;">
                <i class="bi bi-list-task me-2 text-info"></i>Danh sách đợt khảo sát
            </h6>
            <form method="GET" action="{{ route('admin.bao-cao.index') }}" class="w-100 w-md-auto" style="max-width: 420px;">
                <div class="input-group shadow-sm">
                    <input 
                        type="text" 
                        class="form-control border-primary search-input-custom" 
                        name="search" 
                        placeholder="Tìm theo tên đợt hoặc tên mẫu..." 
                        value="{{ request('search') }}"
                        autocomplete="off"
                    >
                    <button class="btn btn-primary" type="submit" title="Tìm kiếm">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.bao-cao.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tên đợt</th>
                            <th>Tên mẫu khảo sát</th>
                            <th>Thời gian</th>
                            <th class="text-center">Số phiếu HT</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dotKhaoSats as $dot)
                            <tr>
                                <td>
                                    <strong>{{ $dot->ten_dot }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info" style="max-width: 180px; white-space: normal; word-break: break-word; display: inline-block;">
                                        {{ $dot->mauKhaoSat->ten_mau ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        {{ \Carbon\Carbon::parse($dot->tungay)->format('d/m/Y') }} - 
                                        {{ \Carbon\Carbon::parse($dot->denngay)->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill">{{ $dot->phieu_hoan_thanh }}</span>
                                </td>
                                <td class="text-center">
                                    @switch($dot->trangthai)
                                        @case('active')
                                            <span class="badge bg-success">Đang hoạt động</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-secondary">Đã đóng</span>
                                            @break
                                        @default
                                            <span class="badge bg-warning">{{ ucfirst($dot->trangthai) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if($dot->phieu_hoan_thanh > 0)
                                        <a href="{{ route('admin.bao-cao.dot-khao-sat', $dot) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Xem báo cáo
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>Chưa có dữ liệu</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Không có đợt khảo sát nào để báo cáo.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Phân trang --}}
            <div class="mt-3 d-flex justify-content-end">
                {{-- $dotKhaoSats->links() --}}
                @if ($dotKhaoSats->hasPages())
                    {{ $dotKhaoSats->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ theo tháng
    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($thongKeThang)->pluck('thang')) !!},
                datasets: [{
                    label: 'Số phiếu hoàn thành',
                    data: {!! json_encode(collect($thongKeThang)->pluck('so_luong')) !!},
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0 // Hiển thị số nguyên
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Biểu đồ đối tượng
    const objectCtx = document.getElementById('objectChart')?.getContext('2d');
    if (objectCtx) {
        const objectChart = new Chart(objectCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($thongKeMauKhaoSat->pluck('ten_mau')) !!},
                datasets: [{
                    data: {!! json_encode($thongKeMauKhaoSat->pluck('phieu_hoanthanh')) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Vẽ Custom Legend
        const legendContainer = document.getElementById('objectChartLegend');
        if (legendContainer) {
            const data = objectChart.data;
            const dataset = data.datasets[0];
            let legendHtml = '<div class="row g-2">';
            
            data.labels.forEach((label, index) => {
                const color = dataset.backgroundColor[index % dataset.backgroundColor.length];
                const value = dataset.data[index] || 0;
                
                legendHtml += `
                    <div class="col-12 d-flex align-items-start gap-2">
                        <span class="d-inline-block rounded-circle flex-shrink-0" style="width: 10px; height: 10px; background-color: ${color}; margin-top: 4px;"></span>
                        <div class="text-truncate-2-lines flex-grow-1 text-muted" title="${label}">
                            <strong>${value} phiếu</strong> - ${label}
                        </div>
                    </div>
                `;
            });
            legendHtml += '</div>';
            legendContainer.innerHTML = legendHtml;
        }
    }
});
</script>
@endpush