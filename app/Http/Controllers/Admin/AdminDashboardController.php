<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\MauKhaoSat;
use App\Models\LichSuThayDoi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Dữ liệu cho 4 Card đầu trang
        $kpis = [
            'active_surveys' => DotKhaoSat::where('trangthai', 'active')->count(),
            'responses_last_7_days' => PhieuKhaoSat::where('trangthai', 'completed')
                ->where('thoigian_hoanthanh', '>=', now()->subDays(7))
                ->count(),
            'total_responses_month' => PhieuKhaoSat::where('trangthai', 'completed')
                ->whereMonth('created_at', date('m'))
                ->count(),
            'avg_completion_time' => $this->getAverageCompletionTime(),
        ];

        $todayStats = $this->getTodayStats();

        // Dữ liệu cho Biểu đồ Xu hướng Phản hồi (30 ngày qua)
        $responseTrendChart = $this->getResponseTrendChartData(30);

        // Dữ liệu cho Biểu đồ Top 5 Mẫu Khảo sát
        $topTemplatesChart = $this->getTopTemplatesChartData();

        // Dữ liệu cho các Bảng danh sách
        $endingSoonSurveys = DotKhaoSat::where('trangthai', 'active')
            ->whereBetween('denngay', [now(), now()->addDays(3)])
            ->orderBy('denngay', 'asc')
            ->take(5)
            ->get();

        $recentActivities = LichSuThayDoi::with('nguoiThucHien')
            ->orderBy('thoigian', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'kpis',
            'responseTrendChart',
            'topTemplatesChart',
            'endingSoonSurveys',
            'recentActivities',
            'todayStats'
        ));
    }


    // --- CÁC HÀM HELPER ---

    private function getTodayStats(): array
    {
        // Số phiếu hoàn thành trong hôm nay
        $todayCompletedResponses = PhieuKhaoSat::where('trangthai', 'completed')
            ->whereDate('thoigian_hoanthanh', Carbon::today())
            ->count();

        // Số phiếu được bắt đầu trong hôm nay (chưa hoàn thành)
        $todayStartedResponses = PhieuKhaoSat::where('trangthai', 'draft')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Đợt khảo sát mới được tạo trong hôm nay
        $todayNewSurveys = DotKhaoSat::whereDate('created_at', Carbon::today())->count();

        // Đợt khảo sát kết thúc vào hôm nay
        $todayEndingSurveys = DotKhaoSat::where('trangthai', 'active')
            ->whereDate('denngay', Carbon::today())
            ->count();

        return [
            'completed' => $todayCompletedResponses,
            'started' => $todayStartedResponses,
            'new' => $todayNewSurveys,
            'ending' => $todayEndingSurveys,
        ];
    }

    private function getAverageCompletionTime(): string
    {
        $avgSeconds = DB::table('phieu_khaosat')
            ->where('trangthai', 'completed')
            ->whereNotNull('thoigian_hoanthanh')
            ->whereNotNull('thoigian_batdau')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, thoigian_batdau, thoigian_hoanthanh)) as avg_time')
            ->value('avg_time');

        if ($avgSeconds === null || $avgSeconds <= 0) {
            return 'N/A';
        }

        $minutes = floor($avgSeconds / 60);
        $seconds = round($avgSeconds % 60);

        if ($minutes < 1) {
            return "{$seconds} giây";
        }

        return "{$minutes} phút {$seconds} giây";
    }

    private function getResponseTrendChartData(int $days): array
    {
        $data = PhieuKhaoSat::where('trangthai', 'completed')
            ->where('thoigian_hoanthanh', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(thoigian_hoanthanh) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->pluck('count', 'date');

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            $values[] = $data->get($date->toDateString(), 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getTopTemplatesChartData()
    {
        return MauKhaoSat::withCount('dotKhaoSat')
            ->orderBy('dot_khao_sat_count', 'desc')
            ->limit(5)
            ->get();
    }
}