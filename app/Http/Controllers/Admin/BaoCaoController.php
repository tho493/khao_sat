<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\PhieuKhaoSatChiTiet;
use App\Models\CauHoiKhaoSat;
use App\Services\ChatbotAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KhaoSatExport;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function dotKhaoSat(DotKhaoSat $dotKhaoSat, Request $request)
    {
        $dotKhaoSat->load([
            'mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'mauKhaoSat.cauHoi.phuongAnTraLoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'mauKhaoSat.cauHoi.dataSource.values',
            'phieuKhaoSat' => function ($query) {
                $query->where('trangthai', 'completed');
            },
            'hiddenQuestions'
        ]);

        // Lấy các câu hỏi thông tin cá nhân (is_personal_info)
        $personalInfoQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->where('is_personal_info', true)
            ->values();

        // Lấy danh sách phiếu đã hoàn thành với bộ lọc
        $baseCompletedQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed');

        // Tách thành 2 query: 1 cho phiếu hợp lệ, 1 cho phiếu trùng lặp
        $nonDuplicateQuery = (clone $baseCompletedQuery)->where('is_duplicate', '!=', 1);
        $duplicateQuery = (clone $baseCompletedQuery)->where('is_duplicate', 1);

        $personalInfoFilters = $request->input('personal_info_filters', []);

        // Áp dụng bộ lọc cho từng câu hỏi thông tin cá nhân
        if (!empty($personalInfoFilters)) {
            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue))
                    continue;

                $question = $personalInfoQuestions->firstWhere('id', $questionId);
                if (!$question)
                    continue;

                // Lấy các phiếu khảo sát có câu trả lời khớp với bộ lọc
                $filterQuery = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $questionId);

                // Xử lý theo loại câu hỏi
                if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
                    $filterQuery->where('phuongan_id', $filterValue);
                } elseif ($question->loai_cauhoi === 'custom_select') {
                    $filterQuery->where('giatri_text', $filterValue);
                } else {
                    // Text, number, date, v.v.
                    $filterQuery->where(function ($q) use ($filterValue) {
                        $q->where('giatri_text', 'LIKE', "%{$filterValue}%")
                            ->orWhere('giatri_number', '=', $filterValue);
                    });
                }

                $filteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');
                $nonDuplicateQuery->whereIn('id', $filteredPhieuIds); // Lọc trên các phiếu hợp lệ
                $duplicateQuery->whereIn('id', $filteredPhieuIds); // Lọc trên các phiếu trùng lặp
            }
        }

        $completedSurveys = $nonDuplicateQuery->get(); // Chỉ lấy phiếu hợp lệ để tính toán
        $completedCount = $completedSurveys->count();
        $duplicateCount = (clone $duplicateQuery)->count();

        $hiddenQuestionIds = $dotKhaoSat->hiddenQuestions->pluck('id')->all();

        $tongQuan = [
            'tong_phieu_hoan_thanh' => $completedCount + $duplicateCount,
            'phieu_hoan_thanh' => $completedCount,
            'tong_cau_hoi' => $dotKhaoSat->mauKhaoSat->cauHoi->count(),
            'thoi_gian_tb' => $this->getAverageCompletionTimeForSurvey($completedSurveys),
            'thoi_gian_nhanh_nhat' => $this->getExtremeCompletionTime($completedSurveys, 'MIN'),
            'thoi_gian_lau_nhat' => $this->getExtremeCompletionTime($completedSurveys, 'MAX'),
        ];

        // --- Dữ liệu Biểu đồ Xu hướng Phản hồi ---
        $responseTrendChart = $this->getResponseTrendForSurvey($dotKhaoSat, $personalInfoFilters, $personalInfoQuestions);

        // Chi tiết từng Câu hỏi
        $thongKeCauHoi = [];
        foreach ($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi) {
            if (in_array($cauHoi->id, $hiddenQuestionIds)) {
                $thongKeCauHoi[$cauHoi->id] = [
                    'type' => 'hidden',
                    'data' => collect(),
                    'total' => 0,
                ];
            } else {
                $thongKeCauHoi[$cauHoi->id] = $this->thongKeCauHoi($dotKhaoSat->id, $cauHoi, $completedSurveys->pluck('id'));
            }
        }

        // Danh sách Phiếu đã nộp (sử dụng cùng query đã lọc)
        $danhSachPhieu = (clone $nonDuplicateQuery)->with(['chiTiet.phuongAn'])
            ->orderBy('thoigian_hoanthanh', 'desc')
            ->paginate(15, ['*'], 'page_completed');

        // Danh sách Phiếu trùng lặp
        $danhSachPhieuTrungLap = (clone $duplicateQuery)->with(['chiTiet.phuongAn'])
            ->orderBy('thoigian_hoanthanh', 'desc')
            ->paginate(10, ['*'], 'page_duplicates');

        // Câu hỏi thông tin cá nhân của mẫu khảo sát này
        $personalInfoQuestions = $dotKhaoSat->mauKhaoSat->cauHoi->where('is_personal_info', true)->values();

        $allSubmittedSurveys = $danhSachPhieu->concat($danhSachPhieuTrungLap);

        // Trích xuất câu trả lời thông tin cá nhân theo từng phiếu, chuẩn hóa dạng chuỗi để hiển thị
        $personalInfoAnswers = [];
        foreach ($allSubmittedSurveys as $phieu) {
            $answersByQuestionId = $phieu->chiTiet->groupBy('cauhoi_id');
            foreach ($personalInfoQuestions as $q) {
                $display = '';
                $answers = $answersByQuestionId->get($q->id);
                if ($answers && $answers->count() > 0) {
                    if ($q->loai_cauhoi === 'multiple_choice') {
                        $display = $answers->map(function ($ans) {
                            return $ans->phuongAn->noidung ?? '';
                        })->filter()->implode('; ');
                    } elseif ($q->loai_cauhoi === 'custom_select') {
                        $first = $answers->first();
                        $value = $first->giatri_text ?? '';
                        if ($q->dataSource && $q->dataSource->values) {
                            $option = $q->dataSource->values->firstWhere('value', $value);
                            $display = $option->label ?? $value;
                        }
                    } else {
                        $first = $answers->first();
                        if ($first->phuongan_id) {
                            $display = $first->phuongAn->noidung ?? '';
                        } elseif (!empty($first->giatri_text)) {
                            $display = $first->giatri_text;
                        } elseif (!is_null($first->giatri_number)) {
                            $display = (string) $first->giatri_number;
                        } elseif (!empty($first->giatri_date)) {
                            $display = (string) $first->giatri_date;
                        }
                    }
                }
                $personalInfoAnswers[$phieu->id][$q->id] = $display !== '' ? $display : 'N/A';
            }
        }

        // Tạo danh sách các options cho từng câu hỏi thông tin cá nhân
        $personalInfoOptions = [];
        foreach ($personalInfoQuestions as $question) {
            $options = [];

            if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
                // Lấy các phương án từ câu hỏi
                $options = $question->phuongAnTraLoi->map(function ($pa) {
                    return (object) ['value' => $pa->id, 'label' => $pa->noidung];
                })->toArray();
            } elseif ($question->loai_cauhoi === 'custom_select') {
                if ($question->dataSource && $question->dataSource->values) {
                    $options = $question->dataSource->values->map(function ($value) {
                        return (object) ['value' => $value->value, 'label' => $value->label];
                    })->toArray();
                }
            } else {
                // Đối với text, number, v.v., lấy tất cả các giá trị riêng biệt
                $distinctValues = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $question->id)
                    ->whereIn('phieu_khaosat_id', $completedSurveys->pluck('id'))
                    ->distinct()
                    ->selectRaw('COALESCE(giatri_text, CAST(giatri_number AS CHAR)) as value')
                    ->pluck('value')
                    ->filter()
                    ->take(100) // Giới hạn 100 giá trị để không quá tải
                    ->map(function ($val) {
                        return (object) ['value' => $val, 'label' => $val];
                    })
                    ->toArray();

                $options = $distinctValues;
            }

            $personalInfoOptions[$question->id] = $options;
        }

        return view('admin.bao-cao.dot-khao-sat', compact(
            'dotKhaoSat',
            'tongQuan',
            'responseTrendChart',
            'thongKeCauHoi',
            'danhSachPhieu',
            'danhSachPhieuTrungLap',
            'personalInfoQuestions',
            'personalInfoAnswers',
            'personalInfoOptions',
            'personalInfoFilters'
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

    public function getAverageCompletionTimeForSurvey($completedSurveys)
    {
        if ($completedSurveys->isEmpty())
            return 'N/A';
        $avgSeconds = $completedSurveys->avg(function ($phieu) {
            return $phieu->thoigian_batdau ? $phieu->thoigian_batdau->diffInSeconds($phieu->thoigian_hoanthanh) : 0;
        });
        return $this->formatSeconds($avgSeconds);
    }

    public function getExtremeCompletionTime($completedSurveys, $type = 'MIN')
    {
        if ($completedSurveys->isEmpty())
            return 'N/A';
        $seconds = $completedSurveys->map(function ($phieu) {
            return $phieu->thoigian_batdau ? $phieu->thoigian_batdau->diffInSeconds($phieu->thoigian_hoanthanh) : null;
        })->filter();

        return $type === 'MIN' ? $this->formatSeconds($seconds->min()) : $this->formatSeconds($seconds->max());
    }

    public function getResponseTrendForSurvey(DotKhaoSat $dotKhaoSat, $personalInfoFilters = [], $personalInfoQuestions = null)
    {
        $query = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed');

        // Áp dụng bộ lọc cho từng câu hỏi thông tin cá nhân
        if (!empty($personalInfoFilters) && $personalInfoQuestions) {
            $allFilteredPhieuIds = collect();
            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue))
                    continue;

                $question = $personalInfoQuestions->firstWhere('id', $questionId);
                if (!$question)
                    continue;

                $filterQuery = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $questionId);

                if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
                    $filterQuery->where('phuongan_id', $filterValue);
                } elseif ($question->loai_cauhoi === 'custom_select') {
                    $filterQuery->where('giatri_text', $filterValue);
                } else {
                    $filterQuery->where(function ($q) use ($filterValue) {
                        $q->where('giatri_text', 'LIKE', "%{$filterValue}%")
                            ->orWhere('giatri_number', '=', $filterValue);
                    });
                }

                $currentFilteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');

                if ($allFilteredPhieuIds->isEmpty()) {
                    $allFilteredPhieuIds = $currentFilteredPhieuIds;
                } else {
                    $allFilteredPhieuIds = $allFilteredPhieuIds->intersect($currentFilteredPhieuIds);
                }
            }
            if ($allFilteredPhieuIds->isNotEmpty()) {
                $query->whereIn('id', $allFilteredPhieuIds);
            }
        }

        $data = $query->select(DB::raw('DATE(thoigian_hoanthanh) as date'), DB::raw('COUNT(*) as count'))
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

    public function getThoiGianTraLoiTrungBinh(DotKhaoSat $dotKhaoSat)
    {
        $avgSeconds = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->whereNotNull('thoigian_hoanthanh')
            ->whereNotNull('thoigian_batdau')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, thoigian_batdau, thoigian_hoanthanh)) as avg_time')
            ->value('avg_time');

        return $this->formatSeconds($avgSeconds);
    }

    /**
     * Thống kê câu hỏi với tuỳ chọn lọc theo danh sách id phiếu đã trả lời.
     *
     * @param  int  $dotKhaoSatId
     * @param  mixed $cauHoi
     * @param  null|array|\Illuminate\Support\Collection $filteredSurveyIds   // danh sách id phiếu trả lời (nếu có lọc theo user), null nếu không lọc
     * @return array
     */
    public function thongKeCauHoi($dotKhaoSatId, $cauHoi, $filteredSurveyIds = null)
    {
        // Lấy id các phiếu khảo sát hoàn thành, có thể có lọc hoặc không
        $completedSurveyIds = is_null($filteredSurveyIds)
            ? DB::table('phieu_khaosat')
                ->where('dot_khaosat_id', $dotKhaoSatId)
                ->where('trangthai', 'completed')
                ->pluck('id')
            : collect($filteredSurveyIds);

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
            ->where('cauhoi_id', $cauHoi->id)
            ->whereIn('phieu_khaosat_id', $completedSurveyIds);

        switch ($cauHoi->loai_cauhoi) {
            case 'single_choice':
            case 'multiple_choice':
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

            case 'likert':
                $answeredCounts = (clone $baseQuery)
                    ->groupBy('phuongan_id')
                    ->select('phuongan_id', DB::raw('COUNT(id) as so_luong'))
                    ->pluck('so_luong', 'phuongan_id');

                $totalResponses = $answeredCounts->sum();
                $weightedSum = 0;

                $data = $cauHoi->phuongAnTraLoi->sortBy('thutu')->values()->map(function ($phuongAn, $index) use ($answeredCounts, $totalResponses, &$weightedSum) {
                    $soLuong = $answeredCounts->get($phuongAn->id, 0);

                    $weight = $index + 1;
                    $weightedSum += $soLuong * $weight;

                    return (object) [
                        'noidung' => $phuongAn->noidung,
                        'so_luong' => $soLuong,
                        'ty_le' => $totalResponses > 0 ? round(($soLuong / $totalResponses) * 100, 2) : 0,
                    ];
                });

                $weightedAverage = $totalResponses > 0 ? round($weightedSum / $totalResponses, 2) : 0;

                return [
                    'type' => 'chart_with_avg',
                    'data' => $data,
                    'total' => $totalResponses,
                    'average' => $weightedAverage,
                    'max_score' => $cauHoi->phuongAnTraLoi->count()
                ];

            case 'text':
                $totalResponses = (clone $baseQuery)
                    ->whereNotNull('giatri_text')->where('giatri_text', '!=', '')->count();
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

            case 'custom_select':
                $rawCounts = (clone $baseQuery)
                    ->select(
                        DB::raw('COALESCE(NULLIF(TRIM(giatri_text), ""), giatri_number) as value'),
                        DB::raw('COUNT(id) as so_luong')
                    )
                    ->where(function ($q) {
                        $q->whereNotNull('giatri_text')->where('giatri_text', '!=', '');
                    })
                    ->groupBy('value')
                    ->pluck('so_luong', 'value');

                $totalResponses = $rawCounts->sum();

                $values = $rawCounts->keys()->filter()->values();
                $valueToLabel = collect();
                if ($cauHoi->dataSource && $cauHoi->dataSource->values) {
                    $valueToLabel = $cauHoi->dataSource->values->whereIn('value', $values)->pluck('label', 'value');
                }

                $data = $rawCounts->map(function ($count, $value) use ($valueToLabel, $totalResponses) {
                    $label = $valueToLabel->get((string) $value);
                    return (object) [
                        'noidung' => $label ?: (string) $value,
                        'so_luong' => $count,
                        'ty_le' => $totalResponses > 0 ? round(($count / $totalResponses) * 100, 2) : 0,
                    ];
                })->values();

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

                $cauTraLoi = (clone $baseQuery)
                    ->whereNotNull('giatri_number')
                    ->select('giatri_number')
                    ->limit(20)
                    ->pluck('giatri_number');
                return [
                    'type' => 'number_stats',
                    'data' => $stats,
                    'total' => $stats->total ?? 0,
                    'cauTraLoi' => $cauTraLoi,
                ];

            default:
                $totalResponses = (clone $baseQuery)->count();
                $rows = (clone $baseQuery)->limit(20)->get();
                // Chuẩn hóa dữ liệu dạng chuỗi để hiển thị trong PDF
                $data = $rows->map(function ($row) use ($cauHoi) {
                    // Trường hợp chọn custom_select: ánh xạ value -> label
                    if ($cauHoi->loai_cauhoi === 'custom_select') {
                        $value = $row->giatri_text ?? null;
                        if ($value !== null && $cauHoi->dataSource && $cauHoi->dataSource->values) {
                            $option = $cauHoi->dataSource->values->firstWhere('value', $value);
                            return $option->label ?? $value;
                        }
                    }

                    // Nếu là phương án lựa chọn, tìm nội dung phương án
                    if (!is_null($row->phuongan_id)) {
                        $noiDung = optional($cauHoi->phuongAnTraLoi->firstWhere('id', $row->phuongan_id))->noidung;
                        if (!empty($noiDung))
                            return $noiDung;
                    }

                    if (!empty($row->giatri_text))
                        return $row->giatri_text;
                    if (!is_null($row->giatri_number))
                        return (string) $row->giatri_number;
                    if (!empty($row->giatri_date))
                        return (string) $row->giatri_date;

                    return '';
                })->filter();

                return ['type' => 'list', 'data' => $data, 'total' => $totalResponses];
        }
    }

    public function getThongKeThang()
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

    public function getThongKeTheoNgay($dotKhaoSat)
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

    // Tổng hợp các câu hỏi likert, dành cho hàm export của pdf
    public function getLikertTableData($completedSurveyIds, $likertQuestions)
    {
        if ($completedSurveyIds->isEmpty() || $likertQuestions->isEmpty()) {
            return collect();
        }
        $allAnswers = DB::table('phieu_khaosat_chitiet')
            ->join('phuongan_traloi', 'phieu_khaosat_chitiet.phuongan_id', '=', 'phuongan_traloi.id')
            ->whereIn('phieu_khaosat_chitiet.cauhoi_id', $likertQuestions->pluck('id'))
            ->whereIn('phieu_khaosat_id', $completedSurveyIds)
            ->select('phieu_khaosat_chitiet.cauhoi_id', 'phuongan_traloi.thutu as option_order')
            ->get();
        return $allAnswers->groupBy('cauhoi_id')->map(fn($answers) => $answers->groupBy('option_order')->map->count());
    }

    protected function prepareSurveyExportData(Request $request, DotKhaoSat $dotKhaoSat): array
    {
        $personalInfoFilters = $request->input('personal_info_filters', []);
        $fileName = 'bao-cao-' . Str::slug($dotKhaoSat->ten_dot);

        $dotKhaoSat->load([
            'mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'mauKhaoSat.cauHoi.phuongAnTraLoi',
            'hiddenQuestions'
        ]);

        $personalInfoQuestions = $dotKhaoSat->mauKhaoSat->cauHoi->where('is_personal_info', true)->values();

        $completedSurveysQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->where('is_duplicate', 0);

        if (!empty($personalInfoFilters)) {
            $allFilteredPhieuIds = collect();
            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue)) {
                    continue;
                }

                $question = $personalInfoQuestions->firstWhere('id', $questionId);
                if (!$question) {
                    continue;
                }

                $filterQuery = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $questionId);

                if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
                    $filterQuery->where('phuongan_id', $filterValue);
                } elseif ($question->loai_cauhoi === 'custom_select') {
                    $dataSourceValues = optional($question->dataSource)->values;
                    $matchedValue = $dataSourceValues
                        ? optional($dataSourceValues->firstWhere('value', $filterValue))->value
                        : null;

                    $filterQuery->where(function ($q) use ($filterValue, $matchedValue) {
                        $q->where('giatri_text', $matchedValue ?? $filterValue)
                            ->orWhere('giatri_number', '=', $matchedValue ?? $filterValue);
                    });
                } else {
                    $filterQuery->where(function ($q) use ($filterValue) {
                        $q->where('giatri_text', 'LIKE', "%{$filterValue}%")
                            ->orWhere('giatri_number', '=', $filterValue);
                    });
                }

                $currentFilteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');

                if ($allFilteredPhieuIds->isEmpty()) {
                    $allFilteredPhieuIds = $currentFilteredPhieuIds;
                } else {
                    $allFilteredPhieuIds = $allFilteredPhieuIds->intersect($currentFilteredPhieuIds);
                }
            }

            if ($allFilteredPhieuIds->isNotEmpty()) {
                $completedSurveysQuery->whereIn('id', $allFilteredPhieuIds);
            } else {
                // Nếu không có bản ghi nào thỏa mãn bộ lọc, đảm bảo trả về tập rỗng
                $completedSurveysQuery->whereRaw('1 = 0');
            }

            $fileName .= '-' . date('Ymd');
        }

        $completedSurveyIds = $completedSurveysQuery->pluck('id');

        return [
            'fileName' => $fileName,
            'completedSurveyIds' => $completedSurveyIds,
            'personalInfoFilters' => $personalInfoFilters,
        ];
    }

    protected function buildPdfViewData(DotKhaoSat $dotKhaoSat, $completedSurveyIds): array
    {
        $tongQuan = [
            'tong_phieu' => $completedSurveyIds->count(),
            'thoi_gian_tb' => $this->getThoiGianTraLoiTrungBinh($dotKhaoSat),
        ];

        $answeredQuestionIdsQuery = PhieuKhaoSat::where('dot_khaosat_id', $dotKhaoSat->id)
            ->where('trangthai', 'completed')
            ->with(['chiTiet.phuongAn']);

        if ($completedSurveyIds->isNotEmpty()) {
            $answeredQuestionIdsQuery->whereIn('id', $completedSurveyIds);
        } else {
            $answeredQuestionIdsQuery->whereRaw('1 = 0');
        }

        $answeredQuestionIds = $answeredQuestionIdsQuery->get();

        $hiddenQuestionIds = $dotKhaoSat->hiddenQuestions->pluck('id')->all();

        $likertQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->where('loai_cauhoi', 'likert')
            ->whereNotIn('id', $hiddenQuestionIds)
            ->values();

        $otherQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->where('loai_cauhoi', '!=', 'likert')
            ->whereNotIn('id', $hiddenQuestionIds)
            ->values();

        $likertOptions = $likertQuestions->isNotEmpty() && $likertQuestions->first()
            ? $likertQuestions->first()->phuongAnTraLoi
            : collect();

        $likertTableData = $this->getLikertTableData($completedSurveyIds, $likertQuestions);

        // Tính điểm trung bình cho từng câu hỏi Likert
        $likertAverages = [];
        foreach ($likertQuestions as $question) {
            $counts = $likertTableData->get($question->id, collect());
            $totalResponses = 0;
            $weightedSum = 0;

            foreach ($likertOptions as $option) {
                $count = $counts->get($option->thutu, 0);
                $weight = $option->thutu;

                $totalResponses += $count;
                $weightedSum += $count * $weight;
            }

            $likertAverages[$question->id] = $totalResponses > 0
                ? round($weightedSum / $totalResponses, 2)
                : 0;
        }

        $thongKeCauHoiKhac = [];
        foreach ($otherQuestions as $cauHoi) {
            $thongKeCauHoiKhac[$cauHoi->id] = $this->thongKeCauHoi($dotKhaoSat->id, $cauHoi, $answeredQuestionIds->pluck('id'));
        }

        return compact(
            'dotKhaoSat',
            'tongQuan',
            'likertQuestions',
            'likertOptions',
            'likertTableData',
            'likertAverages',
            'otherQuestions',
            'thongKeCauHoiKhac'
        );
    }

    public function export(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $format = $request->input('format', 'excel');
        $exportContext = $this->prepareSurveyExportData($request, $dotKhaoSat);
        $fileName = $exportContext['fileName'];
        $completedSurveyIds = $exportContext['completedSurveyIds'];

        if ($format == 'excel') {
            return Excel::download(new KhaoSatExport($dotKhaoSat, $completedSurveyIds), $fileName . '.xlsx');
        }

        if ($format == 'pdf') {
            $pdfData = $this->buildPdfViewData($dotKhaoSat, $completedSurveyIds);
            $pdf = Pdf::loadView('admin.bao-cao.pdf', $pdfData);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download($fileName . '.pdf');
        }

        return back()->with('error', 'Định dạng xuất không hợp lệ.');
    }

    public function previewPdf(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $exportContext = $this->prepareSurveyExportData($request, $dotKhaoSat);
        $completedSurveyIds = $exportContext['completedSurveyIds'];

        $queryParams = array_merge($request->query(), ['format' => 'pdf']);
        $downloadUrl = route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat]) . '?' . http_build_query($queryParams);

        $viewData = $this->buildPdfViewData($dotKhaoSat, $completedSurveyIds);
        $viewData['previewMode'] = true;
        $viewData['downloadUrl'] = $downloadUrl;

        return view('admin.bao-cao.pdf', $viewData);
    }

    public function toggleQuestionVisibility(Request $request, DotKhaoSat $dotKhaoSat)
    {
        try {
            $validated = $request->validate([
                'cauhoi_id' => 'required|integer|exists:cauhoi_khaosat,id',
                'hidden' => 'required'
            ]);

            $cauHoiId = (int) $validated['cauhoi_id'];
            $hiddenRaw = $validated['hidden'];
            $hidden = filter_var($hiddenRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hidden === null) {
                $hidden = in_array((string) $hiddenRaw, ['1', 1], true);
            }

            if ($hidden) {
                if (!$dotKhaoSat->hiddenQuestions()->where('cauhoi_id', $cauHoiId)->exists()) {
                    $dotKhaoSat->hiddenQuestions()->attach($cauHoiId);
                }
            } else {
                $dotKhaoSat->hiddenQuestions()->detach($cauHoiId);
            }

            return response()->json(['success' => true, 'hidden' => (bool) $hidden]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => $ve->errors()
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('toggleQuestionVisibility failed', [
                'error' => $e->getMessage(),
                'dot_khaosat_id' => $dotKhaoSat->id ?? null,
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa một câu trả lời cụ thể
     * @param Request $request
     * @param PhieuKhaoSatChiTiet $phieuKhaoSatChiTiet
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteResponse(Request $request, PhieuKhaoSatChiTiet $phieuKhaoSatChiTiet)
    {
        try {
            // Lấy thông tin phiếu khảo sát và đợt khảo sát
            $phieuKhaoSat = $phieuKhaoSatChiTiet->phieuKhaoSat;
            $dotKhaoSat = $phieuKhaoSat->dotKhaoSat;

            // Kiểm tra xem đợt khảo sát có tồn tại không
            if (!$dotKhaoSat) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đợt khảo sát.'], 404);
            }

            // Kiểm tra xem phiếu khảo sát đã hoàn thành chưa
            if ($phieuKhaoSat->trangthai !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa câu trả lời từ phiếu đã hoàn thành.'], 400);
            }

            // Lưu thông tin để ghi log
            $responseInfo = [
                'phieu_id' => $phieuKhaoSat->id,
                'cauhoi_id' => $phieuKhaoSatChiTiet->cauhoi_id,
                'dot_khaosat_id' => $dotKhaoSat->id,
                'dot_khaosat_ten' => $dotKhaoSat->ten_dot,
                'response_value' => $phieuKhaoSatChiTiet->gia_tri,
                'deleted_by' => auth()->user()->tendangnhap ?? 'unknown',
                'deleted_at' => now()->toDateTimeString()
            ];

            // Xóa câu trả lời
            $phieuKhaoSatChiTiet->delete();

            // Ghi log hoạt động
            \Log::info('Admin deleted survey response', $responseInfo);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa câu trả lời thành công.',
                'data' => $responseInfo
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting survey response', [
                'error' => $e->getMessage(),
                'response_id' => $phieuKhaoSatChiTiet->id ?? 'unknown',
                'user' => auth()->user()->tendangnhap ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa câu trả lời: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ phiếu khảo sát
     * @param Request $request
     * @param PhieuKhaoSat $phieuKhaoSat
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSurvey(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {
            // Kiểm tra quyền truy cập
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'], 401);
            }

            // Lấy thông tin đợt khảo sát
            $dotKhaoSat = $phieuKhaoSat->dotKhaoSat;

            // Kiểm tra xem đợt khảo sát có tồn tại không
            if (!$dotKhaoSat) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đợt khảo sát.'], 404);
            }

            // Kiểm tra xem phiếu khảo sát đã hoàn thành chưa
            if ($phieuKhaoSat->trangthai !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa phiếu khảo sát đã hoàn thành.'], 400);
            }

            // Đếm số câu trả lời sẽ bị xóa
            $responseCount = $phieuKhaoSat->chiTiet()->count();

            // Lưu thông tin để ghi log
            $surveyInfo = [
                'phieu_id' => $phieuKhaoSat->id,
                'dot_khaosat_id' => $dotKhaoSat->id,
                'dot_khaosat_ten' => $dotKhaoSat->ten_dot,
                'response_count' => $responseCount,
                'thoigian_hoanthanh' => $phieuKhaoSat->thoigian_hoanthanh ? $phieuKhaoSat->thoigian_hoanthanh->toDateTimeString() : null,
                'deleted_by' => auth()->user()->tendangnhap ?? 'unknown',
                'deleted_at' => now()->toDateTimeString()
            ];

            // Xóa phiếu khảo sát (cascade sẽ tự động xóa các chi tiết)
            $phieuKhaoSat->delete();

            // Ghi log hoạt động
            \Log::info('Admin deleted entire survey', $surveyInfo);

            return response()->json([
                'success' => true,
                'message' => "Đã xóa phiếu khảo sát thành công. Đã xóa {$responseCount} câu trả lời.",
                'data' => $surveyInfo
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting survey', [
                'error' => $e->getMessage(),
                'survey_id' => $phieuKhaoSat->id ?? 'unknown',
                'user' => auth()->user()->tendangnhap ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phiếu khảo sát: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đổi trạng thái is_duplicate của phiếu khảo sát.
     * @param Request $request
     * @param PhieuKhaoSat $phieuKhaoSat
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleDuplicateStatus(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {

            // Chuyển is_duplicate thành 0 (hợp lệ)
            $phieuKhaoSat->update(['is_duplicate' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu phiếu khảo sát là hợp lệ.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling duplicate status', ['error' => $e->getMessage(), 'phieu_id' => $phieuKhaoSat->id]);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Đánh dấu phiếu khảo sát là trùng lặp.
     * @param Request $request
     * @param PhieuKhaoSat $phieuKhaoSat
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsDuplicate(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {

            // Chuyển is_duplicate thành 1 (trùng lặp)
            $phieuKhaoSat->update(['is_duplicate' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu phiếu khảo sát là trùng lặp.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking as duplicate', ['error' => $e->getMessage(), 'phieu_id' => $phieuKhaoSat->id]);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}