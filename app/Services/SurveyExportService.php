<?php

namespace App\Services;

use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\SurveyStatisticsService;

class SurveyExportService
{
    protected $statisticsService;

    public function __construct(SurveyStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Chuẩn bị dữ liệu cho việc xuất báo cáo (Excel/PDF).
     */
    public function prepareSurveyExportData($request, DotKhaoSat $dotKhaoSat): array
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

        $filterQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->filter(function ($q) {
                return $q->is_personal_info || $q->allow_filter;
            })
            ->values();

        $completedSurveysQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->where('is_duplicate', 0);

        $deviceFilter = $request->input('device_filter');
        $osFilter = $request->input('os_filter');
        $sourceFilter = $request->input('source_filter');

        if ($deviceFilter) {
            if ($deviceFilter === 'Mobile') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%mobile%')
                        ->orWhere('user_agent', 'LIKE', '%phone%')
                        ->orWhere('user_agent', 'LIKE', '%ipod%');
                });
            } elseif ($deviceFilter === 'Tablet') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%ipad%')
                        ->orWhere('user_agent', 'LIKE', '%tablet%')
                        ->orWhere(function ($sub) {
                            $sub->where('user_agent', 'LIKE', '%android%')
                                ->where('user_agent', 'NOT LIKE', '%mobile%');
                        });
                });
            } elseif ($deviceFilter === 'Desktop') {
                $completedSurveysQuery->where('user_agent', 'NOT LIKE', '%mobile%')
                    ->where('user_agent', 'NOT LIKE', '%phone%')
                    ->where('user_agent', 'NOT LIKE', '%ipod%')
                    ->where('user_agent', 'NOT LIKE', '%ipad%')
                    ->where('user_agent', 'NOT LIKE', '%tablet%')
                    ->where('user_agent', 'NOT LIKE', '%bot%')
                    ->where('user_agent', 'NOT LIKE', '%crawler%')
                    ->where('user_agent', 'NOT LIKE', '%spider%');
            } elseif ($deviceFilter === 'Bot') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%bot%')
                        ->orWhere('user_agent', 'LIKE', '%crawler%')
                        ->orWhere('user_agent', 'LIKE', '%spider%');
                });
            }
        }

        if ($osFilter) {
            if ($osFilter === 'iOS') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%iphone%')
                        ->orWhere('user_agent', 'LIKE', '%ipad%')
                        ->orWhere('user_agent', 'LIKE', '%ipod%');
                });
            } else {
                $completedSurveysQuery->where('user_agent', 'LIKE', "%{$osFilter}%");
            }
        }

        if ($sourceFilter) {
            if ($sourceFilter === 'Trực tiếp') {
                $completedSurveysQuery->where(function ($q) {
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
                $completedSurveysQuery->where('user_agent', 'LIKE', '%zalo%');
            } elseif ($sourceFilter === 'Facebook App') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%fbav%')
                        ->orWhere('user_agent', 'LIKE', '%fb_iab%');
                });
            } elseif ($sourceFilter === 'Messenger App') {
                $completedSurveysQuery->where(function ($q) {
                    $q->where('user_agent', 'LIKE', '%messenger%')
                        ->orWhere('user_agent', 'LIKE', '%fbms%');
                });
            } elseif ($sourceFilter === 'Instagram App') {
                $completedSurveysQuery->where('user_agent', 'LIKE', '%instagram%');
            }
        }

        if (!empty($personalInfoFilters)) {
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
                    $dataSourceValues = optional($question->dataSource)->values;
                    $matchedValue = $dataSourceValues
                        ? optional($dataSourceValues->firstWhere('value', $filterValue))->value
                        : null;

                    $filterQuery->where(function ($q) use ($filterValue, $matchedValue) {
                        $q->where('giatri_text', $matchedValue ?? $filterValue)
                            ->orWhere('giatri_number', '=', $matchedValue ?? $filterValue);
                    });
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
                $completedSurveysQuery->whereIn('id', $allFilteredPhieuIds);
            } else {
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

    /**
     * Chuẩn bị dữ liệu để render PDF.
     */
    public function buildPdfViewData(DotKhaoSat $dotKhaoSat, $completedSurveyIds): array
    {
        $tongQuan = [
            'tong_phieu' => $completedSurveyIds->count(),
            'thoi_gian_tb' => $this->statisticsService->getThoiGianTraLoiTrungBinh($dotKhaoSat),
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

        $likertTableData = $this->statisticsService->getLikertTableData($completedSurveyIds, $likertQuestions);

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
            $thongKeCauHoiKhac[$cauHoi->id] = $this->statisticsService->thongKeCauHoi($dotKhaoSat->id, $cauHoi, $answeredQuestionIds->pluck('id'));
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
}
