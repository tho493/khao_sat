<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách đợt khảo sát để lọc
        $dotKhaoSats = DotKhaoSat::with(['mauKhaoSat'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Thống kê tổng quan
        $tongQuan = $this->getTongQuan($request);

        // Biểu đồ theo tháng
        $bieuDoThang = $this->getBieuDoThang($request);

        return view('admin.reports.index', compact(
            'dotKhaoSats',
            'tongQuan',
            'bieuDoThang',
        ));
    }

    public function analytics(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Phân tích xu hướng
        $trendData = $this->getTrendAnalysis($year);

        // So sánh giữa các đối tượng
        $comparisonData = $this->getObjectComparison($year);

        // Thời gian trả lời trung bình
        $responseTimeData = $this->getResponseTimeAnalysis($year);

        return view('admin.reports.analytics', compact(
            'year',
            'trendData',
            'comparisonData',
            'responseTimeData'
        ));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'dot_khaosat_id' => 'nullable|exists:dot_khaosat,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'format' => 'required|in:excel,pdf'
        ]);

        // Logic xuất báo cáo
        if ($validated['format'] == 'excel') {
            return $this->exportExcel($validated);
        } else {
            return $this->exportPdf($validated);
        }
    }

    private function getTongQuan($request)
    {
        $query = PhieuKhaoSat::query();

        if ($request->filled('dot_khaosat_id')) {
            $query->where('dot_khaosat_id', $request->dot_khaosat_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return [
            'tong_phieu' => $query->count(),
            'phieu_hoan_thanh' => (clone $query)->where('trangthai', 'completed')->count(),
            'ty_le_hoan_thanh' => $query->count() > 0
                ? round((clone $query)->where('trangthai', 'completed')->count() / $query->count() * 100, 2)
                : 0,
            'thoi_gian_tb' => (clone $query)->where('trangthai', 'completed')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, thoigian_batdau, thoigian_hoanthanh)) as avg_time')
                ->value('avg_time') ?? 0
        ];
    }

    private function getBieuDoThang($request)
    {
        $year = $request->get('year', date('Y'));

        $data = PhieuKhaoSat::selectRaw('MONTH(thoigian_batdau) as month, COUNT(*) as total')
            ->whereYear('thoigian_batdau', $year)
            ->where('trangthai', 'completed')
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $data->get($i, 0);
        }

        return $result;
    }

    private function getTrendAnalysis($year)
    {
        // Phân tích xu hướng theo quý
        return DB::table('phieu_khaosat')
            ->selectRaw('QUARTER(thoigian_batdau) as quarter, COUNT(*) as total')
            ->whereYear('thoigian_batdau', $year)
            ->where('trangthai', 'completed')
            ->groupBy('quarter')
            ->orderBy('quarter')
            ->get();
    }

    private function getObjectComparison($year)
    {
        // So sánh tỷ lệ hoàn thành giữa các đối tượng
        return DB::table('phieu_khaosat as pk')
            ->join('dot_khaosat as dk', 'pk.dot_khaosat_id', '=', 'dk.id')
            ->join('mau_khaosat as mk', 'dk.mau_khaosat_id', '=', 'mk.id')
            ->selectRaw('
                mk.ten_mau,
                COUNT(*) as total,
                SUM(CASE WHEN pk.trangthai = "completed" THEN 1 ELSE 0 END) as completed,
                ROUND(SUM(CASE WHEN pk.trangthai = "completed" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as completion_rate
            ')
            ->whereYear('pk.thoigian_batdau', $year)
            ->groupBy('mk.id')
            ->orderBy('completion_rate', 'desc')
            ->get();
    }

    private function getResponseTimeAnalysis($year)
    {
        // Phân tích thời gian trả lời theo tháng
        return DB::table('phieu_khaosat')
            ->selectRaw('
                MONTH(thoigian_batdau) as month,
                AVG(TIMESTAMPDIFF(MINUTE, thoigian_batdau, thoigian_hoanthanh)) as avg_time,
                MIN(TIMESTAMPDIFF(MINUTE, thoigian_batdau, thoigian_hoanthanh)) as min_time,
                MAX(TIMESTAMPDIFF(MINUTE, thoigian_batdau, thoigian_hoanthanh)) as max_time
            ')
            ->whereYear('thoigian_batdau', $year)
            ->where('trangthai', 'completed')
            ->whereNotNull('thoigian_hoanthanh')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function exportExcel($params)
    {
        // Implement Excel export
        // Sử dụng Maatwebsite\Excel
        return back()->with('info', 'Chức năng xuất Excel đang được phát triển');
    }

    private function exportPdf($params)
    {
        // Implement PDF export
        // Sử dụng barryvdh/laravel-dompdf
        return back()->with('info', 'Chức năng xuất PDF đang được phát triển');
    }
}