@extends('layouts.admin')

@section('title', 'Báo cáo thống kê')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Báo cáo thống kê</h1>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Đợt khảo sát</label>
                        <select class="form-select" name="dot_khaosat_id">
                            <option value="">-- Tất cả --</option>
                            @foreach($dotKhaoSats as $dot)
                                <option value="{{ $dot->id }}" {{ request('dot_khaosat_id') == $dot->id ? 'selected' : '' }}>
                                    {{ $dot->ten_dot }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tổng phiếu
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ number_format($tongQuan['tong_phieu']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Hoàn thành
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ number_format($tongQuan['phieu_hoan_thanh']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tỷ lệ hoàn thành
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $tongQuan['ty_le_hoan_thanh'] }}%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Thời gian TB
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ round($tongQuan['thoi_gian_tb']) }} phút
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Biểu đồ theo tháng -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê theo tháng</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Thống kê theo đối tượng -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Theo đối tượng</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="objectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export buttons -->
        <div class="text-end mt-4">
            <button class="btn btn-success" onclick="exportReport('excel')">
                <i class="bi bi-file-earmark-excel"></i> Xuất Excel
            </button>
            <button class="btn btn-danger" onclick="exportReport('pdf')">
                <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
            </button>
            <a href="{{ route('admin.reports.analytics') }}" class="btn btn-info">
                <i class="bi bi-graph-up"></i> Phân tích chi tiết
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ theo tháng
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Số phiếu khảo sát',
                    data: {!! json_encode($bieuDoThang) !!},
                    backgroundColor: 'rgba(78, 115, 223, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Biểu đồ đối tượng
        const objectCtx = document.getElementById('objectChart').getContext('2d');
        new Chart(objectCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($thongKeDoiTuong->pluck('ten_doituong')) !!},
                datasets: [{
                    data: {!! json_encode($thongKeDoiTuong->pluck('phieu_hoan_thanh')) !!},
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
                maintainAspectRatio: false
            }
        });

        function exportReport(format) {
            const params = new URLSearchParams(window.location.search);
            params.append('format', format);

            window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
        }
    </script>
@endpush