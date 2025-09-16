<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\CauHoiKhaoSat;
use App\Services\ChatbotAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KhaoSatExport;
use Illuminate\Support\Str;
use PDF;

class BaoCaoController extends Controller
{
    public function index(Request $request)
    {
        $query = DotKhaoSat::with(['mauKhaoSat'])
            ->withCount([
                'phieuKhaoSat as phieu_hoan_thanh' => function ($q) {
                    $q->where('trangthai', 'completed');
                }
            ])
            ->where('trangthai', '!=', 'draft');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');

            // Tìm kiếm trong tên đợt khảo sát HOẶC tên mẫu khảo sát liên quan
            $query->where(function ($q) use ($searchTerm) {
                $q->where('ten_dot', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('mauKhaoSat', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('ten_mau', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Sắp xếp và phân trang
        $dotKhaoSats = $query->orderBy('created_at', 'desc')->paginate(10);

        // Thống kê tổng quan
        $tongQuan = [
            'tong_dot' => DotKhaoSat::count(),
            'dot_active' => DotKhaoSat::where('trangthai', 'active')->count(),
            'tong_phieu' => PhieuKhaoSat::count(),
            'phieu_hoanthanh' => PhieuKhaoSat::where('trangthai', 'completed')->count(),
        ];

        // Thống kê theo tháng (12 tháng gần nhất)
        $thongKeThang = $this->getThongKeThang();

        // Thống kê theo mẫu khảo sát 
        $thongKeMauKhaoSat = DB::table('dot_khaosat as dk')
            ->join('mau_khaosat as mk', 'dk.mau_khaosat_id', '=', 'mk.id')
            ->leftJoin('phieu_khaosat as pk', function ($join) {
                $join->on('dk.id', '=', 'pk.dot_khaosat_id')
                    ->where('pk.trangthai', '=', 'completed');
            })
            ->where('dk.trangthai', '!=', 'draft')
            ->groupBy('dk.mau_khaosat_id', 'mk.ten_mau')
            ->select(
                'mk.ten_mau',
                DB::raw('COUNT(DISTINCT dk.id) as so_dot'),
                DB::raw('COUNT(pk.id) as phieu_hoanthanh')
            )
            ->get()
            ->map(function ($item) {
                return [
                    'ten_mau' => $item->ten_mau ?? 'N/A',
                    'phieu_hoanthanh' => $item->phieu_hoanthanh,
                ];
            });

        return view('admin.bao-cao.index', compact(
            'dotKhaoSats',
            'tongQuan',
            'thongKeThang',
            'thongKeMauKhaoSat'
        ));
    }

    public function dotKhaoSat(DotKhaoSat $dotKhaoSat)
    {
        $dotKhaoSat->load([
            'mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'mauKhaoSat.cauHoi.phuongAnTraLoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'phieuKhaoSat' => function ($query) {
                $query->where('trangthai', 'completed');
            }
        ]);

        // Thống kê Tổng quan
        $completedSurveys = $dotKhaoSat->phieuKhaoSat;
        $completedCount = $completedSurveys->count();

        $tongQuan = [
            'phieu_hoan_thanh' => $completedCount,
            'tong_cau_hoi' => $dotKhaoSat->mauKhaoSat->cauHoi->count(),
            'thoi_gian_tb' => $this->getAverageCompletionTimeForSurvey($completedSurveys),
            'thoi_gian_nhanh_nhat' => $this->getExtremeCompletionTime($completedSurveys, 'MIN'),
            'thoi_gian_lau_nhat' => $this->getExtremeCompletionTime($completedSurveys, 'MAX'),
        ];

        // --- 2. Dữ liệu Biểu đồ Xu hướng Phản hồi ---
        $responseTrendChart = $this->getResponseTrendForSurvey($dotKhaoSat);

        // Chi tiết từng Câu hỏi
        $thongKeCauHoi = [];
        foreach ($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi) {
            $thongKeCauHoi[$cauHoi->id] = $this->thongKeCauHoi($dotKhaoSat->id, $cauHoi);
        }

        // Danh sách Phiếu đã nộp
        $danhSachPhieu = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->orderBy('thoigian_hoanthanh', 'desc')
            ->paginate(15);

        return view('admin.bao-cao.dot-khao-sat', compact(
            'dotKhaoSat',
            'tongQuan',
            'responseTrendChart',
            'thongKeCauHoi',
            'danhSachPhieu'
        ));
    }

    // FUNCTION
    protected function formatSeconds(?int $avgSeconds): string
    {
        if ($avgSeconds === null || $avgSeconds <= 0) {
            return 'N/A';
        }

        $minutes = floor($avgSeconds / 60);
        $seconds = round($avgSeconds % 60);

        if ($minutes == 0) {
            return "{$seconds} giây";
        }

        return "{$minutes} phút {$seconds} giây";
    }

    private function getAverageCompletionTimeForSurvey($completedSurveys)
    {
        if ($completedSurveys->isEmpty())
            return 'N/A';
        $avgSeconds = $completedSurveys->avg(function ($phieu) {
            return $phieu->thoigian_batdau ? $phieu->thoigian_batdau->diffInSeconds($phieu->thoigian_hoanthanh) : 0;
        });
        return $this->formatSeconds($avgSeconds);
    }

    private function getExtremeCompletionTime($completedSurveys, $type = 'MIN')
    {
        if ($completedSurveys->isEmpty())
            return 'N/A';
        $seconds = $completedSurveys->map(function ($phieu) {
            return $phieu->thoigian_batdau ? $phieu->thoigian_batdau->diffInSeconds($phieu->thoigian_hoanthanh) : null;
        })->filter();

        return $type === 'MIN' ? $this->formatSeconds($seconds->min()) : $this->formatSeconds($seconds->max());
    }

    private function getResponseTrendForSurvey(DotKhaoSat $dotKhaoSat)
    {
        $data = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->select(DB::raw('DATE(thoigian_hoanthanh) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date');

        $labels = [];
        $values = [];
        $period = \Carbon\CarbonPeriod::create($dotKhaoSat->tungay, $dotKhaoSat->denngay);
        foreach ($period as $date) {
            $labels[] = $date->format('d/m');
            $values[] = $data->get($date->toDateString(), 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getThoiGianTraLoiTrungBinh(DotKhaoSat $dotKhaoSat)
    {
        $avgSeconds = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->whereNotNull('thoigian_hoanthanh')
            ->whereNotNull('thoigian_batdau')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, thoigian_batdau, thoigian_hoanthanh)) as avg_time')
            ->value('avg_time');

        return $this->formatSeconds($avgSeconds);
    }

    private function thongKeCauHoi($dotKhaoSatId, $cauHoi)
    {
        $completedSurveyIds = DB::table('phieu_khaosat')
            ->where('dot_khaosat_id', $dotKhaoSatId)
            ->where('trangthai', 'completed')
            ->pluck('id');

        if ($completedSurveyIds->isEmpty() && in_array($cauHoi->loai_cauhoi, ['single_choice', 'multiple_choice', 'likert', 'rating'])) {
            $data = $cauHoi->phuongAnTraLoi->map(function ($item) {
                return (object) [
                    'noidung' => $item->noidung,
                    'so_luong' => 0,
                    'ty_le' => 0,
                ];
            });
            return ['type' => 'chart', 'data' => $data, 'total' => 0];
        }

        $baseQuery = DB::table('phieu_khaosat_chitiet')
            ->where('phieu_khaosat_chitiet.cauhoi_id', $cauHoi->id)
            ->whereIn('phieu_khaosat_id', $completedSurveyIds);

        switch ($cauHoi->loai_cauhoi) {

            case 'single_choice':
            case 'multiple_choice':
            case 'likert':
                $answeredCounts = (clone $baseQuery)
                    ->groupBy('phuongan_id')
                    ->select(
                        'phuongan_id',
                        DB::raw('COUNT(id) as so_luong')
                    )
                    ->pluck('so_luong', 'phuongan_id');

                $totalResponses = $answeredCounts->sum();

                $data = $cauHoi->phuongAnTraLoi->map(function ($phuongAn) use ($answeredCounts, $totalResponses) {
                    $soLuong = $answeredCounts->get($phuongAn->id, 0);
                    return (object) [
                        'noidung' => $phuongAn->noidung,
                        'so_luong' => $soLuong,
                        'ty_le' => $totalResponses > 0 ? round(($soLuong / $totalResponses) * 100, 2) : 0,
                    ];
                });

                return [
                    'type' => 'chart',
                    'data' => $data,
                    'total' => $totalResponses,
                ];

            case 'text':
                $totalResponses = (clone $baseQuery)->whereNotNull('giatri_text')->where('giatri_text', '!=', '')->count();
                $data = (clone $baseQuery)
                    ->whereNotNull('giatri_text')
                    ->where('giatri_text', '!=', '')
                    ->select('giatri_text')
                    ->limit(20)
                    ->pluck('giatri_text');

                return ['type' => 'text', 'data' => $data, 'total' => $totalResponses];

            case 'rating':
                $answeredCounts = (clone $baseQuery)
                    ->whereNotNull('giatri_number')
                    ->groupBy('giatri_number')
                    ->select(
                        'giatri_number',
                        DB::raw('COUNT(id) as so_luong')
                    )
                    ->pluck('so_luong', 'giatri_number');

                $totalResponses = $answeredCounts->sum();
                $normalizedAnsweredCounts = collect();
                foreach ($answeredCounts as $key => $value) {
                    $intKey = (int) $key;
                    $normalizedAnsweredCounts->put($intKey, $value);
                }
                $data = collect([1, 2, 3, 4, 5])->map(function ($rating) use ($normalizedAnsweredCounts, $totalResponses) {
                    $soLuong = $normalizedAnsweredCounts->get($rating, 0);
                    return (object) [
                        'noidung' => "{$rating} sao",
                        'so_luong' => $soLuong,
                        'ty_le' => $totalResponses > 0 ? round(($soLuong / $totalResponses) * 100, 2) : 0,
                    ];
                });

                return [
                    'type' => 'chart',
                    'data' => $data,
                    'total' => $totalResponses,
                ];

            case 'number':
                $stats = (clone $baseQuery)
                    ->whereNotNull('giatri_number')
                    ->selectRaw('
                        COUNT(id) as total,
                        MIN(giatri_number) as min,
                        MAX(giatri_number) as max,
                        AVG(giatri_number) as avg,
                        STDDEV(giatri_number) as stddev
                    ')->first();

                return [
                    'type' => 'number_stats',
                    'data' => $stats,
                    'total' => $stats->total ?? 0,
                ];

            default:
                $totalResponses = (clone $baseQuery)->count();
                $data = (clone $baseQuery)->limit(20)->get();
                return ['type' => 'list', 'data' => $data, 'total' => $totalResponses];
        }
    }

    private function getThongKeThang()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = PhieuKhaoSat::whereYear('thoigian_batdau', $date->year)
                ->whereMonth('thoigian_batdau', $date->month)
                ->where('trangthai', 'completed')
                ->count();

            $data[] = [
                'thang' => $date->format('m/Y'),
                'so_luong' => $count
            ];
        }

        return $data;
    }

    private function getThongKeTheoNgay($dotKhaoSat)
    {
        return DB::table('phieu_khaosat')
            ->where('dot_khaosat_id', $dotKhaoSat->id)
            ->where('trangthai', 'completed')
            ->selectRaw('DATE(thoigian_hoanthanh) as ngay, COUNT(*) as so_luong')
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get();
    }

    /**
     * Tóm tắt câu trả lời bằng AI.
     * @param Request $request
     * @param DotKhaoSat $dotKhaoSat
     * @param ChatbotAIService $aiService
     * @return \Illuminate\Http\JsonResponse
     */
    public function summarizeWithAi(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $validated = $request->validate([
            'cauhoi_id' => 'required|exists:cauhoi_khaosat,id',
        ]);

        $cauHoi = CauHoiKhaoSat::find($validated['cauhoi_id']);

        if ($cauHoi->loai_cauhoi !== 'text') {
            return response()->json(['summary' => 'Chức năng này chỉ áp dụng cho câu hỏi tự luận.'], 400);
        }

        $completedSurveyIds = $dotKhaoSat->phieuKhaoSat()->where('trangthai', 'completed')->pluck('id');

        $answers = DB::table('phieu_khaosat_chitiet')
            ->where('cauhoi_id', $cauHoi->id)
            ->whereIn('phieu_khaosat_id', $completedSurveyIds)
            ->whereNotNull('giatri_text')
            ->where('giatri_text', '!=', '')
            ->pluck('giatri_text');

        if ($answers->count() < 3) { // Cần ít nhất vài câu trả lời để tóm tắt có ý nghĩa
            return response()->json(['summary' => 'Không đủ dữ liệu để tạo tóm tắt (cần ít nhất 3 câu trả lời).'], 400);
        }

        $fullText = $answers->implode("\n- ");
        $aiService = app(ChatbotAIService::class);
        $summary = $aiService->summarizeText($fullText, $cauHoi->noidung_cauhoi);

        if ($summary['success']) {
            return response()->json([
                'summary' => $summary['text']
            ]);
        } else {
            return response()->json([
                'summary' => "<div class='alert alert-warning'><strong>Lỗi từ dịch vụ AI:</strong><br>" . e($summary['error']) . "</div>"
            ], 503);
        }
    }

    public function export(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $format = $request->input('format', 'excel');
        $fileName = 'bao-cao-' . Str::slug($dotKhaoSat->ten_dot) . '-' . date('Ymd');

        if ($format == 'excel') {
            return Excel::download(new KhaoSatExport($dotKhaoSat), $fileName . '.xlsx');
        }

        if ($format == 'pdf') {
            $tongQuan = [
                'tong_phieu' => $dotKhaoSat->phieuKhaoSat()->where('trangthai', 'completed')->count(),
                'thoi_gian_tb' => $this->getThoiGianTraLoiTrungBinh($dotKhaoSat)
            ];
            $thongKeCauHoi = [];
            foreach ($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi) {
                $thongKeCauHoi[$cauHoi->id] = $this->thongKeCauHoi($dotKhaoSat->id, $cauHoi);
            }

            $pdf = PDF::loadView('admin.bao-cao.pdf', compact('dotKhaoSat', 'tongQuan', 'thongKeCauHoi'));

            $pdf->setPaper('a4', 'portrait');

            return $pdf->download($fileName . '.pdf');
        }

        return back()->with('error', 'Định dạng xuất không hợp lệ.');
    }
}