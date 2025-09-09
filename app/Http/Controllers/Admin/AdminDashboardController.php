<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\MauKhaoSat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Thống kê tổng quan
        $stats = [
            'total_users' => User::count(),
            'active_surveys' => DotKhaoSat::where('trangthai', 'active')->count(),
            'total_responses' => PhieuKhaoSat::whereMonth('created_at', date('m'))
                ->where('trangthai', 'completed')
                ->count(),
            'total_templates' => MauKhaoSat::count(),
        ];

        // Biểu đồ phản hồi 7 ngày gần nhất
        $responseChart = $this->getResponseChartData();

        // Biểu đồ thống kê theo 5 đợt KS có nhiều phản hồi nhất
        $surveyStatsChart = $this->getTopSurveyStats();

        // Hoạt động gần đây
        // $recentActivities = LichSuThayDoi::with('nguoiThucHien')
        //     ->orderBy('thoigian', 'desc')
        //     ->take(5)
        //     ->get();

        $recentActivities = $this->getRecentActivities();

        // Người dùng hoạt động
        $activeUsers = User::whereNotNull('last_login')
            ->orderBy('last_login', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'responseChart',
            'surveyStatsChart',
            'recentActivities',
            'activeUsers'
        ));
    }

    private function getResponseChartData()
    {
        $data = PhieuKhaoSat::where('trangthai', 'completed')
            ->where('thoigian_hoanthanh', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(thoigian_hoanthanh) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->pluck('count', 'date');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            $values[] = $data->get($date->toDateString(), 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getTopSurveyStats()
    {
        return DotKhaoSat::join('phieu_khaosat', 'dot_khaosat.id', '=', 'phieu_khaosat.dot_khaosat_id')
            ->where('phieu_khaosat.trangthai', 'completed')
            ->select(
                'dot_khaosat.ten_dot',
                DB::raw('COUNT(phieu_khaosat.id) as total_responses')
            )
            ->groupBy('dot_khaosat.id', 'dot_khaosat.ten_dot')
            ->orderBy('total_responses', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentActivities()
    {
        return DB::table('lichsu_thaydoi')
            ->join('taikhoan', 'lichsu_thaydoi.nguoi_thuchien_id', '=', 'taikhoan.id')
            ->select(
                'lichsu_thaydoi.*',
                'taikhoan.hoten as nguoi_thuchien'
            )
            ->orderBy('lichsu_thaydoi.thoigian', 'desc')
            ->take(10)
            ->get();
    }

    private function getObjectStats()
    {
        return DB::table('dot_khaosat as dk')
            ->join('mau_khaosat as mk', 'dk.mau_khaosat_id', '=', 'mk.id')
            ->leftJoin('phieu_khaosat as pk', 'dk.id', '=', 'pk.dot_khaosat_id')
            ->select(
                'mk.ten_mau',
                DB::raw('COUNT(DISTINCT dk.id) as total_surveys'),
                DB::raw('COUNT(pk.id) as total_responses')
            )
            ->groupBy('mk.id', 'mk.ten_mau')
            ->get();
    }
}