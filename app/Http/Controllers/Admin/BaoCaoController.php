<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\PhieuKhaoSatChiTiet;
use App\Models\CauHoiKhaoSat;
use App\Services\ChatbotAIService;
use App\Services\SurveyStatisticsService;
use App\Services\SurveyExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KhaoSatExport;
use Barryvdh\DomPDF\Facade\Pdf;

class BaoCaoController extends Controller
{
    protected $statisticsService;
    protected $exportService;

    public function __construct(SurveyStatisticsService $statisticsService, SurveyExportService $exportService)
    {
        $this->statisticsService = $statisticsService;
        $this->exportService = $exportService;
    }

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
            $query->where(function ($q) use ($searchTerm) {
                $q->where('ten_dot', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('mauKhaoSat', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('ten_mau', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        $dotKhaoSats = $query->orderBy('created_at', 'desc')->paginate(10);

        $tongQuan = [
            'tong_dot' => DotKhaoSat::count(),
            'dot_active' => DotKhaoSat::where('trangthai', 'active')->count(),
            'tong_phieu' => PhieuKhaoSat::count(),
            'phieu_hoanthanh' => PhieuKhaoSat::where('trangthai', 'completed')->count(),
        ];

        $thongKeThang = $this->statisticsService->getThongKeThang();

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

        $personalInfoQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->where('is_personal_info', true)
            ->values();

        $filterQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
            ->filter(function ($q) {
                return $q->is_personal_info || $q->allow_filter;
            })
            ->values();

        $baseCompletedQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed');

        $nonDuplicateQuery = (clone $baseCompletedQuery)->where('is_duplicate', '!=', 1);
        $duplicateQuery = (clone $baseCompletedQuery)->where('is_duplicate', 1);

        $deviceFilter = $request->input('device_filter');
        $osFilter = $request->input('os_filter');
        $sourceFilter = $request->input('source_filter');

        $applyUserAgentFilters = function ($query) use ($deviceFilter, $osFilter, $sourceFilter) {
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
                                    ->where('user_agent', 'NOT LIKE', '%fban/%')
                                    ->where('user_agent', 'NOT LIKE', '%instagram%')
                                    ->where('user_agent', 'NOT LIKE', '%messenger%')
                                    ->where('user_agent', 'NOT LIKE', '%fbms%')
                                    ->where('user_agent', 'NOT LIKE', '%tiktok%')
                                    ->where('user_agent', 'NOT LIKE', '%musically%')
                                    ->where('user_agent', 'NOT LIKE', '%twitter%')
                                    ->where('user_agent', 'NOT LIKE', '%com.google.android.youtube%');
                            });
                    });
                } elseif ($sourceFilter === 'Zalo App') {
                    $query->where('user_agent', 'LIKE', '%zalo%');
                } elseif ($sourceFilter === 'Facebook App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%fbav%')
                            ->orWhere('user_agent', 'LIKE', '%fb_iab%')
                            ->orWhere('user_agent', 'LIKE', '%fban/%');
                    });
                } elseif ($sourceFilter === 'Messenger App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%messenger%')
                            ->orWhere('user_agent', 'LIKE', '%fbms%');
                    });
                } elseif ($sourceFilter === 'Instagram App') {
                    $query->where('user_agent', 'LIKE', '%instagram%');
                } elseif ($sourceFilter === 'TikTok App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%tiktok%')
                            ->orWhere('user_agent', 'LIKE', '%musically%');
                    });
                } elseif ($sourceFilter === 'Twitter App') {
                    $query->where('user_agent', 'LIKE', '%twitter%');
                } elseif ($sourceFilter === 'YouTube App') {
                    $query->where('user_agent', 'LIKE', '%com.google.android.youtube%');
                }
            }
        };

        $applyUserAgentFilters($nonDuplicateQuery);
        $applyUserAgentFilters($duplicateQuery);
        $applyUserAgentFilters($baseCompletedQuery);

        $personalInfoFilters = $request->input('personal_info_filters', []);

        if (!empty($personalInfoFilters)) {
            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue))
                    continue;

                $question = $filterQuestions->firstWhere('id', $questionId);
                if (!$question)
                    continue;

                $filterQuery = DB::table('phieu_khaosat_chitiet')->where('cauhoi_id', $questionId);

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

                $filteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');
                $nonDuplicateQuery->whereIn('id', $filteredPhieuIds);
                $duplicateQuery->whereIn('id', $filteredPhieuIds);
            }
        }

        $completedSurveys = $nonDuplicateQuery->get();
        $completedCount = $completedSurveys->count();
        $duplicateCount = (clone $duplicateQuery)->count();

        $hiddenQuestionIds = $dotKhaoSat->hiddenQuestions->pluck('id')->all();

        $tongQuan = [
            'tong_phieu_hoan_thanh' => $completedCount + $duplicateCount,
            'phieu_hoan_thanh' => $completedCount,
            'tong_cau_hoi' => $dotKhaoSat->mauKhaoSat->cauHoi->count(),
            'thoi_gian_tb' => $this->statisticsService->getAverageCompletionTimeForSurvey($completedSurveys),
            'thoi_gian_nhanh_nhat' => $this->statisticsService->getExtremeCompletionTime($completedSurveys, 'MIN'),
            'thoi_gian_lau_nhat' => $this->statisticsService->getExtremeCompletionTime($completedSurveys, 'MAX'),
        ];

        $responseTrendChart = $this->statisticsService->getResponseTrendForSurvey($dotKhaoSat, $personalInfoFilters, $filterQuestions, $deviceFilter, $osFilter, $sourceFilter);

        $thongKeCauHoi = [];
        foreach ($dotKhaoSat->mauKhaoSat->cauHoi as $cauHoi) {
            if (in_array($cauHoi->id, $hiddenQuestionIds)) {
                $thongKeCauHoi[$cauHoi->id] = [
                    'type' => 'hidden',
                    'data' => collect(),
                    'total' => 0,
                ];
            } else {
                $thongKeCauHoi[$cauHoi->id] = $this->statisticsService->thongKeCauHoi($dotKhaoSat->id, $cauHoi, $completedSurveys->pluck('id'));
            }
        }

        $danhSachPhieu = (clone $nonDuplicateQuery)->with(['chiTiet.phuongAn'])
            ->orderBy('thoigian_hoanthanh', 'desc')
            ->paginate(15, ['*'], 'page_completed');

        $danhSachPhieuTrungLap = (clone $duplicateQuery)->with(['chiTiet.phuongAn'])
            ->orderBy('thoigian_hoanthanh', 'desc')
            ->paginate(10, ['*'], 'page_duplicates');

        $allSubmittedSurveys = $danhSachPhieu->concat($danhSachPhieuTrungLap);

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

        $personalInfoOptions = [];
        foreach ($filterQuestions as $question) {
            $options = [];

            if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice'])) {
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
                $distinctValues = DB::table('phieu_khaosat_chitiet')
                    ->where('cauhoi_id', $question->id)
                    ->whereIn('phieu_khaosat_id', $completedSurveys->pluck('id'))
                    ->distinct()
                    ->selectRaw('COALESCE(giatri_text, CAST(giatri_number AS CHAR)) as value')
                    ->pluck('value')
                    ->filter()
                    ->take(100)
                    ->map(function ($val) {
                        return (object) ['value' => $val, 'label' => $val];
                    })
                    ->toArray();

                $options = $distinctValues;
            }

            $personalInfoOptions[$question->id] = $options;
        }

        $thongKeThietBi = $this->statisticsService->getThongKeThietBi($completedSurveys);

        $surveyMetadata = [];
        foreach ($allSubmittedSurveys as $phieu) {
            $parsedAgent = \App\Helpers\UserAgentParser::parse($phieu->user_agent);
            $surveyMetadata[$phieu->id] = [
                'ip' => $phieu->ip_address ?: 'N/A',
                'device_summary' => $parsedAgent['summary'],
                'device_type' => $parsedAgent['device']
            ];
        }

        return view('admin.bao-cao.dot-khao-sat', compact(
            'dotKhaoSat',
            'tongQuan',
            'responseTrendChart',
            'thongKeCauHoi',
            'danhSachPhieu',
            'danhSachPhieuTrungLap',
            'personalInfoQuestions',
            'filterQuestions',
            'personalInfoAnswers',
            'personalInfoOptions',
            'personalInfoFilters',
            'thongKeThietBi',
            'surveyMetadata',
            'deviceFilter',
            'osFilter',
            'sourceFilter'
        ));
    }

    public function export(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $format = $request->input('format', 'excel');
        $exportContext = $this->exportService->prepareSurveyExportData($request, $dotKhaoSat);
        $fileName = $exportContext['fileName'];
        $completedSurveyIds = $exportContext['completedSurveyIds'];

        if ($format == 'excel') {
            return Excel::download(new KhaoSatExport($dotKhaoSat, $completedSurveyIds), $fileName . '.xlsx');
        }

        if ($format == 'pdf') {
            $pdfData = $this->exportService->buildPdfViewData($dotKhaoSat, $completedSurveyIds);
            $pdf = Pdf::loadView('admin.bao-cao.pdf', $pdfData);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download($fileName . '.pdf');
        }

        return back()->with('error', 'Định dạng xuất không hợp lệ.');
    }

    public function previewPdf(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $exportContext = $this->exportService->prepareSurveyExportData($request, $dotKhaoSat);
        $completedSurveyIds = $exportContext['completedSurveyIds'];

        $queryParams = array_merge($request->query(), ['format' => 'pdf']);
        $downloadUrl = route('admin.bao-cao.export', ['dotKhaoSat' => $dotKhaoSat]) . '?' . http_build_query($queryParams);

        $viewData = $this->exportService->buildPdfViewData($dotKhaoSat, $completedSurveyIds);
        $viewData['previewMode'] = true;
        $viewData['downloadUrl'] = $downloadUrl;

        return view('admin.bao-cao.pdf', $viewData);
    }

    public function summarizeWithAi(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $validated = $request->validate([
            'cauhoi_id' => 'required|exists:cauhoi_khaosat,id',
        ]);

        $cauHoi = CauHoiKhaoSat::find($validated['cauhoi_id']);

        if ($cauHoi->loai_cauhoi !== 'text') {
            return response()->json(['summary' => 'Chức năng này chỉ áp dụng cho câu hỏi tự luận.'], 400);
        }

        $baseCompletedQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->where('is_duplicate', '!=', 1);

        $deviceFilter = $request->input('device_filter');
        $osFilter = $request->input('os_filter');
        $sourceFilter = $request->input('source_filter');

        $applyUserAgentFilters = function ($query) use ($deviceFilter, $osFilter, $sourceFilter) {
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
                            ->orWhere('user_agent', 'LIKE', '%fb_iab%')
                            ->orWhere('user_agent', 'LIKE', '%fban/%');
                    });
                } elseif ($sourceFilter === 'Messenger App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%messenger%')
                            ->orWhere('user_agent', 'LIKE', '%fbms%');
                    });
                } elseif ($sourceFilter === 'Instagram App') {
                    $query->where('user_agent', 'LIKE', '%instagram%');
                } elseif ($sourceFilter === 'TikTok App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%tiktok%')
                            ->orWhere('user_agent', 'LIKE', '%musically%');
                    });
                } elseif ($sourceFilter === 'Twitter App') {
                    $query->where('user_agent', 'LIKE', '%twitter%');
                } elseif ($sourceFilter === 'YouTube App') {
                    $query->where('user_agent', 'LIKE', '%com.google.android.youtube%');
                }
            }
        };

        $applyUserAgentFilters($baseCompletedQuery);

        $personalInfoFilters = $request->input('personal_info_filters', []);
        if (!empty($personalInfoFilters)) {
            $filterQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
                ->filter(function ($q) {
                    return $q->is_personal_info || $q->allow_filter;
                })
                ->values();

            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue))
                    continue;

                $question = $filterQuestions->firstWhere('id', $questionId);
                if (!$question)
                    continue;

                $filterQuery = DB::table('phieu_khaosat_chitiet')->where('cauhoi_id', $questionId);

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

                $filteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');
                $baseCompletedQuery->whereIn('id', $filteredPhieuIds);
            }
        }

        $completedSurveyIds = $baseCompletedQuery->pluck('id');

        $answers = DB::table('phieu_khaosat_chitiet')
            ->where('cauhoi_id', $cauHoi->id)
            ->whereIn('phieu_khaosat_id', $completedSurveyIds)
            ->whereNotNull('giatri_text')
            ->where('giatri_text', '!=', '')
            ->pluck('giatri_text');

        if ($answers->count() < 3) {
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

    public function deleteResponse(Request $request, PhieuKhaoSatChiTiet $phieuKhaoSatChiTiet)
    {
        try {
            $phieuKhaoSat = $phieuKhaoSatChiTiet->phieuKhaoSat;
            $dotKhaoSat = $phieuKhaoSat->dotKhaoSat;

            if (!$dotKhaoSat) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đợt khảo sát.'], 404);
            }

            if ($phieuKhaoSat->trangthai !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa câu trả lời từ phiếu đã hoàn thành.'], 400);
            }

            $responseInfo = [
                'phieu_id' => $phieuKhaoSat->id,
                'cauhoi_id' => $phieuKhaoSatChiTiet->cauhoi_id,
                'dot_khaosat_id' => $dotKhaoSat->id,
                'dot_khaosat_ten' => $dotKhaoSat->ten_dot,
                'response_value' => $phieuKhaoSatChiTiet->gia_tri,
                'deleted_by' => auth()->user()->tendangnhap ?? 'unknown',
                'deleted_at' => now()->toDateTimeString()
            ];

            $phieuKhaoSatChiTiet->delete();
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

    public function deleteSurvey(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'], 401);
            }

            $dotKhaoSat = $phieuKhaoSat->dotKhaoSat;

            if (!$dotKhaoSat) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đợt khảo sát.'], 404);
            }

            if ($phieuKhaoSat->trangthai !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa phiếu khảo sát đã hoàn thành.'], 400);
            }

            $responseCount = $phieuKhaoSat->chiTiet()->count();

            $surveyInfo = [
                'phieu_id' => $phieuKhaoSat->id,
                'dot_khaosat_id' => $dotKhaoSat->id,
                'dot_khaosat_ten' => $dotKhaoSat->ten_dot,
                'response_count' => $responseCount,
                'thoigian_hoanthanh' => $phieuKhaoSat->thoigian_hoanthanh ? $phieuKhaoSat->thoigian_hoanthanh->toDateTimeString() : null,
                'deleted_by' => auth()->user()->tendangnhap ?? 'unknown',
                'deleted_at' => now()->toDateTimeString()
            ];

            $phieuKhaoSat->delete();
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

    public function toggleDuplicateStatus(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {
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

    public function markAsDuplicate(Request $request, PhieuKhaoSat $phieuKhaoSat)
    {
        try {
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

    public function getQuestionAnswers(Request $request, DotKhaoSat $dotKhaoSat, CauHoiKhaoSat $cauHoi)
    {
        $perPage = $request->input('per_page', 20);
        $personalInfoFilters = $request->input('personal_info_filters', []);
        
        $baseCompletedQuery = $dotKhaoSat->phieuKhaoSat()
            ->where('trangthai', 'completed')
            ->where('is_duplicate', '!=', 1);

        $deviceFilter = $request->input('device_filter');
        $osFilter = $request->input('os_filter');
        $sourceFilter = $request->input('source_filter');

        $applyUserAgentFilters = function ($query) use ($deviceFilter, $osFilter, $sourceFilter) {
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
                            ->orWhere('user_agent', 'LIKE', '%fb_iab%')
                            ->orWhere('user_agent', 'LIKE', '%fban/%');
                    });
                } elseif ($sourceFilter === 'Messenger App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%messenger%')
                            ->orWhere('user_agent', 'LIKE', '%fbms%');
                    });
                } elseif ($sourceFilter === 'Instagram App') {
                    $query->where('user_agent', 'LIKE', '%instagram%');
                } elseif ($sourceFilter === 'TikTok App') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'LIKE', '%tiktok%')
                            ->orWhere('user_agent', 'LIKE', '%musically%');
                    });
                } elseif ($sourceFilter === 'Twitter App') {
                    $query->where('user_agent', 'LIKE', '%twitter%');
                } elseif ($sourceFilter === 'YouTube App') {
                    $query->where('user_agent', 'LIKE', '%com.google.android.youtube%');
                }
            }
        };

        $applyUserAgentFilters($baseCompletedQuery);

        if (!empty($personalInfoFilters)) {
            $filterQuestions = $dotKhaoSat->mauKhaoSat->cauHoi
                ->filter(function ($q) {
                    return $q->is_personal_info || $q->allow_filter;
                })
                ->values();

            foreach ($personalInfoFilters as $questionId => $filterValue) {
                if (empty($filterValue))
                    continue;

                $question = $filterQuestions->firstWhere('id', $questionId);
                if (!$question)
                    continue;

                $filterQuery = DB::table('phieu_khaosat_chitiet')->where('cauhoi_id', $questionId);

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

                $filteredPhieuIds = $filterQuery->pluck('phieu_khaosat_id');
                $baseCompletedQuery->whereIn('id', $filteredPhieuIds);
            }
        }

        $completedSurveyIds = $baseCompletedQuery->pluck('id');

        $query = DB::table('phieu_khaosat_chitiet')
            ->where('cauhoi_id', $cauHoi->id)
            ->whereIn('phieu_khaosat_id', $completedSurveyIds);

        if ($cauHoi->loai_cauhoi === 'text') {
            $query->whereNotNull('giatri_text')
                ->where('giatri_text', '!=', '')
                ->select('giatri_text as value', 'phieu_khaosat_id');
        } elseif ($cauHoi->loai_cauhoi === 'number') {
            $query->whereNotNull('giatri_number')
                ->select('giatri_number as value', 'phieu_khaosat_id');
        } elseif ($cauHoi->loai_cauhoi === 'date') {
            $query->whereNotNull('giatri_date')
                ->select('giatri_date as value', 'phieu_khaosat_id');
        } else {
            $query->select(DB::raw('COALESCE(giatri_text, CAST(giatri_number AS CHAR)) as value'), 'phieu_khaosat_id');
        }

        $paginator = $query->paginate($perPage);

        if ($cauHoi->loai_cauhoi === 'date') {
            $items = collect($paginator->items())->map(function ($item) {
                $item->value = $item->value ? \Carbon\Carbon::parse($item->value)->format('d/m/Y') : '';
                return $item;
            })->all();
        } else {
            $items = $paginator->items();
        }

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage()
        ]);
    }
}