<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\MauKhaoSat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Thống kê tổng quan
        $stats = [
            'total_users' => User::count(),
            'active_surveys' => DotKhaoSat::where('trangthai', 'active')->count(),
            'total_responses' => PhieuKhaoSat::whereMonth('thoigian_batdau', date('m'))->count(),
            'total_templates' => MauKhaoSat::where('trangthai', 'active')->count(),
        ];

        // Biểu đồ phản hồi 7 ngày gần nhất
        $responseChart = $this->getResponseChart();

        // Hoạt động gần đây
        $recentActivities = $this->getRecentActivities();

        // Thống kê theo đối tượng
        $objectStats = $this->getObjectStats();

        // Người dùng hoạt động
        $activeUsers = User::where('last_login', '>=', Carbon::now()->subDays(7))
            ->orderBy('last_login', 'desc')
            ->take(5)
            ->get();

        // dd($objectStats);
        return view('admin.dashboard.index', compact(
            'stats',
            'responseChart',
            'recentActivities',
            'objectStats',
            'activeUsers'
        ));
    }

    private function getResponseChart()
    {
        $data = PhieuKhaoSat::selectRaw('DATE(thoigian_batdau) as date, COUNT(*) as count')
            ->whereDate('thoigian_batdau', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $valuesCollection = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->toDateString();

            $labels[] = $date->format('d/m');

            $valuesCollection->push($data->get($dateString, 0));
        }

        return [
            'labels' => $labels,
            'values' => $valuesCollection->all()
        ];
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