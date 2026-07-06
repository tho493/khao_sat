<?php

namespace App\Services;

use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SurveyStatisticsService
{
    /**
     * Định dạng giây thành chuỗi phút giây.
     */
    public function formatSeconds(?int $avgSeconds): string
    {
        if ($avgSeconds === null || $avgSeconds < 0) {
            return 'N/A';
        }

        if ($avgSeconds === 0) {
            return '0 giây';
        }

        $parts = [];
        
        $days = floor($avgSeconds / 86400);
        $avgSeconds %= 86400;
        
        $hours = floor($avgSeconds / 3600);
        $avgSeconds %= 3600;
        
        $minutes = floor($avgSeconds / 60);
        $seconds = round($avgSeconds % 60);

        if ($days > 0) {
            $parts[] = "{$days} ngày";
        }
        if ($hours > 0) {
            $parts[] = "{$hours} giờ";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes} phút";
        }
        if ($seconds > 0 || empty($parts)) {
            $parts[] = "{$seconds} giây";
        }

        return implode(' ', $parts);
    }

    /**
     * Lấy thời gian làm bài trung bình.
     */
    public function getAverageCompletionTimeForSurvey($completedSurveys)
    {
        if ($completedSurveys->isEmpty()) {
            return 'N/A';
        }
        $seconds = $completedSurveys->map(function ($phieu) {
            if (!$phieu->thoigian_batdau || !$phieu->thoigian_hoanthanh) {
                return null;
            }
            $start = Carbon::parse($phieu->thoigian_batdau);
            $end = Carbon::parse($phieu->thoigian_hoanthanh);
            return $end->timestamp - $start->timestamp;
        })->filter(fn($value) => !is_null($value) && $value > 0);

        if ($seconds->isEmpty()) {
            return 'N/A';
        }

        return $this->formatSeconds((int) round($seconds->average()));
    }

    /**
     * Lấy thời gian làm bài nhanh nhất hoặc lâu nhất.
     */
    public function getExtremeCompletionTime($completedSurveys, $type = 'MIN')
    {
        if ($completedSurveys->isEmpty()) {
            return 'N/A';
        }
        $seconds = $completedSurveys->map(function ($phieu) {
            if (!$phieu->thoigian_batdau || !$phieu->thoigian_hoanthanh) {
                return null;
            }
            $start = Carbon::parse($phieu->thoigian_batdau);
            $end = Carbon::parse($phieu->thoigian_hoanthanh);
            return $end->timestamp - $start->timestamp;
        })->filter(fn($value) => !is_null($value) && $value > 0);

        if ($seconds->isEmpty()) {
            return 'N/A';
        }

        return $type === 'MIN' ? $this->formatSeconds($seconds->min()) : $this->formatSeconds($seconds->max());
    }

    /**
     * Lấy xu hướng phản hồi theo ngày của đợt khảo sát có kèm bộ lọc.
     */
    public function getResponseTrendForSurvey(DotKhaoSat $dotKhaoSat, $personalInfoFilters = [], $filterQuestions = null, $deviceFilter = null, $osFilter = null, $sourceFilter = null)
    {
        $query = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed');

        if ($deviceFilter) {
            if ($deviceFilter === 'Mobile') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%mobile%')
                        ->orWhere('user_agent', 'LIKE', '%phone%')
                        ->orWhere('user_agent', 'LIKE', '%ipod%');
                });
            } elseif ($deviceFilter === 'Tablet') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%ipad%')
                        ->orWhere('user_agent', 'LIKE', '%tablet%')
                        ->orWhere(function ($sub) {
                            $sub->where('user_agent', 'LIKE', '%android%')
                                ->where('user_agent', 'NOT LIKE', '%mobile%');
                        });
                });
            } elseif ($deviceFilter === 'Desktop') {
                $query->where('user_agent', 'NOT LIKE', '%mobile%')
                    ->where('user_agent', 'NOT LIKE', '%phone%')
                    ->where('user_agent', 'NOT LIKE', '%ipod%')
                    ->where('user_agent', 'NOT LIKE', '%ipad%')
                    ->where('user_agent', 'NOT LIKE', '%tablet%')
                    ->where('user_agent', 'NOT LIKE', '%bot%')
                    ->where('user_agent', 'NOT LIKE', '%crawler%')
                    ->where('user_agent', 'NOT LIKE', '%spider%');
            } elseif ($deviceFilter === 'Bot') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%bot%')
                        ->orWhere('user_agent', 'LIKE', '%crawler%')
                        ->orWhere('user_agent', 'LIKE', '%spider%');
                });
            }
        }

        if ($osFilter) {
            if ($osFilter === 'iOS') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%iphone%')
                        ->orWhere('user_agent', 'LIKE', '%ipad%')
                        ->orWhere('user_agent', 'LIKE', '%ipod%');
                });
            } else {
                $query->where('user_agent', 'LIKE', "%{$osFilter}%");
            }
        }

        if ($sourceFilter) {
            if ($sourceFilter === 'Trực tiếp') {
                $query->where(function ($q) {
                    $q->whereNull('user_agent')
                        ->orWhere(function ($sub) {
                            $sub->where('user_agent', 'NOT LIKE', '%zalo%')
                                ->where('user_agent', 'NOT LIKE', '%fbav%')
                                ->where('user_agent', 'NOT LIKE', '%fb_iab%')
                                ->where('user_agent', 'NOT LIKE', '%instagram%')
                                ->where('user_agent', 'NOT LIKE', '%messenger%')
                                ->where('user_agent', 'NOT LIKE', '%fbms%');
                        });
                });
            } elseif ($sourceFilter === 'Zalo App') {
                $query->where('user_agent', 'LIKE', '%zalo%');
            } elseif ($sourceFilter === 'Facebook App') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%fbav%')
                        ->orWhere('user_agent', 'LIKE', '%fb_iab%');
                });
            } elseif ($sourceFilter === 'Messenger App') {
                $query->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%messenger%')
                        ->orWhere('user_agent', 'LIKE', '%fbms%');
                });
            } elseif ($sourceFilter === 'Instagram App') {
                $query->where('user_agent', 'LIKE', '%instagram%');
            }
        }

        // Áp dụng bộ lọc cho từng câu hỏi thông tin cá nhân/câu hỏi lọc
        if (!empty($personalInfoFilters) && $filterQuestions) {
            $allFilteredPhieuIds = collect();
            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue)) {
                    continue;
                }

                $question = $filterQuestions->firstWhere('id', $questionId);
                if (!$question) {
                    continue;
                }

                $filterQuery = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $questionId);

                if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
                    $filterQuery->where('phuongan_id', $filterValue);
                } elseif ($question->loai_cauhoi === 'custom_select') {
                    $filterQuery->where('giatri_text', $filterValue);
                } elseif ($question->loai_cauhoi === 'date') {
                    $dateStr = null;
                    try {
                        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $filterValue)) {
                            $dateStr = \Carbon\Carbon::createFromFormat('d/m/Y', $filterValue)->toDateString();
                        } else {
                            $dateStr = \Carbon\Carbon::parse($filterValue)->toDateString();
                        }
                    } catch (\Exception $e) {
                        $dateStr = $filterValue;
                    }
                    $filterQuery->whereDate('giatri_date', '=', $dateStr);
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
        $period = CarbonPeriod::create($dotKhaoSat->tungay, $dotKhaoSat->denngay);
        foreach ($period as $date) {
            $labels[] = $date->format('d/m');
            $values[] = $data->get($date->toDateString(), 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Lấy thời gian trả lời trung bình của đợt khảo sát.
     */
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
     * Thống kê chi tiết câu hỏi theo các id phiếu trả lời đã lọc.
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
                    ->select('giatri_text', 'phieu_khaosat_id')
                    ->limit(20)
                    ->get();

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

            case 'date':
                $totalResponses = (clone $baseQuery)
                    ->whereNotNull('giatri_date')->count();
                $data = (clone $baseQuery)
                    ->whereNotNull('giatri_date')
                    ->select('giatri_date', 'phieu_khaosat_id')
                    ->limit(20)
                    ->get()
                    ->map(function ($row) {
                        $row->giatri_text = $row->giatri_date ? \Carbon\Carbon::parse($row->giatri_date)->format('d/m/Y') : '';
                        return $row;
                    });

                return ['type' => 'text', 'data' => $data, 'total' => $totalResponses];

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
                    ->select('giatri_number', 'phieu_khaosat_id')
                    ->limit(20)
                    ->get();
                return [
                    'type' => 'number_stats',
                    'data' => $stats,
                    'total' => $stats->total ?? 0,
                    'cauTraLoi' => $cauTraLoi,
                ];

            default:
                $totalResponses = (clone $baseQuery)->count();
                $rows = (clone $baseQuery)->limit(20)->get();
                // Chuẩn hóa dữ liệu dạng chuỗi để hiển thị
                $data = $rows->map(function ($row) use ($cauHoi) {
                    if ($cauHoi->loai_cauhoi === 'custom_select') {
                        $value = $row->giatri_text ?? null;
                        if ($value !== null && $cauHoi->dataSource && $cauHoi->dataSource->values) {
                            $option = $cauHoi->dataSource->values->firstWhere('value', $value);
                            return $option->label ?? $value;
                        }
                    }

                    if (!is_null($row->phuongan_id)) {
                        $noiDung = optional($cauHoi->phuongAnTraLoi->firstWhere('id', $row->phuongan_id))->noidung;
                        if (!empty($noiDung)) {
                            return $noiDung;
                        }
                    }

                    if (!empty($row->giatri_text)) {
                        return $row->giatri_text;
                    }
                    if (!is_null($row->giatri_number)) {
                        return (string) $row->giatri_number;
                    }
                    if (!empty($row->giatri_date)) {
                        return (string) $row->giatri_date;
                    }

                    return '';
                })->filter();

                return ['type' => 'list', 'data' => $data, 'total' => $totalResponses];
        }
    }

    /**
     * Thống kê theo tháng.
     */
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

    /**
     * Thống kê theo ngày.
     */
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
     * Lấy dữ liệu bảng Likert.
     */
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

    /**
     * Thống kê thiết bị và hệ điều hành của các phiếu khảo sát đã hoàn thành.
     */
    public function getThongKeThietBi($completedSurveys): array
    {
        $devices = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0, 'Bot' => 0, 'Unknown' => 0];
        $osList = [];
        $browsers = [];
        $sources = ['Trực tiếp' => 0, 'Zalo App' => 0, 'Facebook App' => 0, 'Messenger App' => 0, 'Instagram App' => 0];

        foreach ($completedSurveys as $phieu) {
            $parsed = \App\Helpers\UserAgentParser::parse($phieu->user_agent);

            // 1. Đếm thiết bị
            $devType = $parsed['device'];
            if (isset($devices[$devType])) {
                $devices[$devType]++;
            } else {
                $devices['Unknown']++;
            }

            // 2. Đếm hệ điều hành
            $os = $parsed['os'];
            $osList[$os] = ($osList[$os] ?? 0) + 1;

            // 3. Đếm trình duyệt
            $browserName = $parsed['browser'];
            if ($parsed['app']) {
                $browserName .= ' (' . $parsed['app'] . ' App)';
            }
            $browsers[$browserName] = ($browsers[$browserName] ?? 0) + 1;

            // 4. Đếm nguồn truy cập
            $src = $parsed['app'] ? $parsed['app'] . ' App' : 'Trực tiếp';
            if (isset($sources[$src])) {
                $sources[$src]++;
            } else {
                $sources['Trực tiếp']++;
            }
        }

        // Loại bỏ các thiết bị có số lượng = 0 để biểu đồ hiển thị đẹp hơn
        $filteredDevices = array_filter($devices, fn($count) => $count > 0);
        $filteredSources = array_filter($sources, fn($count) => $count > 0);

        // Sắp xếp giảm dần theo số lượng
        arsort($osList);
        arsort($browsers);
        arsort($filteredSources);

        return [
            'devices' => [
                'labels' => array_keys($filteredDevices),
                'values' => array_values($filteredDevices)
            ],
            'os' => [
                'labels' => array_keys($osList),
                'values' => array_values($osList)
            ],
            'browsers' => [
                'labels' => array_keys($browsers),
                'values' => array_values($browsers)
            ],
            'sources' => [
                'labels' => array_keys($filteredSources),
                'values' => array_values($filteredSources)
            ]
        ];
    }
}
