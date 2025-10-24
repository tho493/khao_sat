<?php

namespace App\Http\Controllers;

use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\PhieuKhaoSatChiTiet;
use App\Models\Ctdt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\CauHoiKhaoSat;
use Illuminate\Support\Facades\DB;

class KhaoSatController extends Controller
{
    public function index()
    {
        $dotKhaoSats = DotKhaoSat::with(['mauKhaoSat'])
            ->where('trangthai', 'active')
            ->where('tungay', '<=', now())
            ->where('denngay', '>=', now())
            ->get();

        return view('khao-sat.index', compact('dotKhaoSats'));
    }

    public function show(DotKhaoSat $dotKhaoSat)
    {
        $isAdminMode = Auth::check();
        if (
            !$isAdminMode &&
            (
                $dotKhaoSat->isClosed() ||
                $dotKhaoSat->isDraft() ||
                $dotKhaoSat->isUpcoming() ||
                $dotKhaoSat->isExpired()
            )
        ) {
            $statusMap = [
                'closed' => [
                    'message' => 'Đợt khảo sát này đã được đóng lại.',
                    'reason' => 'closed'
                ],
                'draft' => [
                    'message' => 'Đợt khảo sát này đang trong quá trình chỉnh sửa.',
                    'reason' => 'draft'
                ],
                'upcoming' => [
                    'message' => 'Đợt khảo sát này chưa bắt đầu.',
                    'reason' => 'not_started_yet'
                ],
                'expired' => [
                    'message' => 'Đợt khảo sát này đã kết thúc.',
                    'reason' => 'expired'
                ],
            ];

            if ($dotKhaoSat->isClosed()) {
                $status = 'closed';
            } elseif ($dotKhaoSat->isDraft()) {
                $status = 'draft';
            } elseif ($dotKhaoSat->isUpcoming()) {
                $status = 'upcoming';
            } else {
                $status = 'expired';
            }

            return view('khao-sat.closed', array_merge(
                ['dotKhaoSat' => $dotKhaoSat],
                $statusMap[$status]
            ));
        }

        $mauKhaoSat = $dotKhaoSat->mauKhaoSat()->with([
            'cauHoi' => function ($query) {
                $query->where('trangthai', 1)->orderBy('page', 'asc')->orderBy('thutu', 'asc');
            },
            'cauHoi.phuongAnTraLoi' => function ($query) {
                $query->orderBy('thutu', 'asc');
            }
        ])->first();

        if (!$mauKhaoSat) {
            return redirect()->route('khao-sat.index')
                ->with('error', 'Không tìm thấy mẫu khảo sát cho đợt này.');
        }

        // Phân loại câu hỏi: thông tin cá nhân và câu hỏi thường, rồi gom nhóm theo trang
        $personalInfoQuestions = $mauKhaoSat->cauHoi->where('is_personal_info', true)->values();
        $questionsByPage = $mauKhaoSat->cauHoi
            ->where('is_personal_info', false)
            ->groupBy('page');

        // Danh sách CTDT cho kiểu select_ctdt
        $ctdtList = Ctdt::orderBy('tenctdt', 'asc')->get(['mactdt', 'tenctdt']);

        // Nếu là admin (đăng nhập), hiển thị cảnh báo chế độ admin
        $adminModeWarning = ($isAdminMode) ? 'Bạn đang ở chế độ quản trị viên (Admin) nên có thể xem trước. Khảo sát đang ở chế độ ' . $dotKhaoSat->trangthai : null;
        return view('khao-sat.show', compact('dotKhaoSat', 'mauKhaoSat', 'questionsByPage', 'personalInfoQuestions', 'ctdtList', 'adminModeWarning'));
    }

    public function store(Request $request, DotKhaoSat $dotKhaoSat)
    {
        $isAdmin = Auth::check();
        if (!$dotKhaoSat->isActive()) {
            $message = $isAdmin
                ? 'Quản trị viên đang ở chế độ xem trước và không thể nộp khảo sát.'
                : 'Đợt khảo sát không hoạt động';
            $response = [
                'success' => false,
                'message' => $message
            ];
            if ($isAdmin) {
                $response['redirect'] = route('khao-sat.show', $dotKhaoSat);
            }
            return response()->json($response, 403);
        }

        // Validate reCAPTCHA
        $request->validate([
            'g-recaptcha-response' => ['required', new \App\Rules\Recaptcha]
        ], [
            'g-recaptcha-response.required' => 'Vui lòng xác thực reCAPTCHA.'
        ]);

        DB::beginTransaction();
        try {
            // Tạo phiếu khảo sát
            $phieuKhaoSat = PhieuKhaoSat::create([
                'dot_khaosat_id' => $dotKhaoSat->id,
                'thoigian_batdau' => $request->metadata['thoigian_batdau'] ?? null,
                'trangthai' => 'draft',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Lưu câu trả lời
            foreach ($request->input('cau_tra_loi', []) as $cauHoiId => $traLoi) {
                if (is_null($traLoi) || (is_string($traLoi) && trim($traLoi) === '') || (is_array($traLoi) && empty($traLoi))) {
                    continue;
                }

                $cauHoi = CauHoiKhaoSat::find($cauHoiId);
                if (!$cauHoi)
                    continue;

                $data = [
                    'phieu_khaosat_id' => $phieuKhaoSat->id,
                    'cauhoi_id' => $cauHoiId
                ];

                switch ($cauHoi->loai_cauhoi) {
                    case 'multiple_choice':
                        $dataToInsert = [];
                        foreach ($traLoi as $phuongAnId) {
                            $dataToInsert[] = [
                                'phieu_khaosat_id' => $phieuKhaoSat->id,
                                'cauhoi_id' => $cauHoiId,
                                'phuongan_id' => $phuongAnId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        if (!empty($dataToInsert)) {
                            PhieuKhaoSatChiTiet::insert($dataToInsert);
                        }
                        break;

                    case 'single_choice':
                    case 'likert':
                        $data['phuongan_id'] = $traLoi;
                        PhieuKhaoSatChiTiet::create($data);
                        break;

                    case 'rating':
                    case 'number':
                        $data['giatri_number'] = $traLoi;
                        PhieuKhaoSatChiTiet::create($data);
                        break;

                    case 'date':
                        $data['giatri_date'] = $traLoi;
                        PhieuKhaoSatChiTiet::create($data);
                        break;

                    case 'select_ctdt':
                        $data['giatri_text'] = $traLoi;
                        PhieuKhaoSatChiTiet::create($data);
                        break;

                    case 'text':
                    default:
                        $data['giatri_text'] = $traLoi;
                        PhieuKhaoSatChiTiet::create($data);
                        break;
                }
            }

            $phieuKhaoSat->update([
                'trangthai' => 'completed',
                'thoigian_hoanthanh' => now()
            ]);

            DB::commit();

            // Lưu dữ liệu vào session để hiển thị trong review
            $this->storeReviewDataInSession($phieuKhaoSat, $dotKhaoSat);

            return response()->json([
                'success' => true,
                'message' => 'Gửi khảo sát thành công',
                'redirect' => route('khao-sat.review')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 400);
        }
    }

    public function thanks()
    {
        return view('thanks');
    }

    public function review()
    {
        $reviewData = session('khao_sat_review_data');

        if (!$reviewData) {
            return redirect()->route('khao-sat.index')
                ->with('error', 'Không tìm thấy dữ liệu khảo sát để xem lại.');
        }

        return view('khao-sat.review', compact('reviewData'));
    }

    private function storeReviewDataInSession($phieuKhaoSat, $dotKhaoSat)
    {
        // Lấy thông tin phiếu khảo sát
        $phieuKhaoSatWithDetails = PhieuKhaoSat::with([
            'dotKhaoSat.mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('is_personal_info', 'desc')
                    ->orderBy('page', 'asc')
                    ->orderBy('thutu', 'asc');
            },
            'chiTiet.phuongAn'
        ])->find($phieuKhaoSat->id);

        $ctdtQuestion = optional($phieuKhaoSatWithDetails->dotKhaoSat->mauKhaoSat->cauHoi)
            ->where('loai_cauhoi', 'select_ctdt')
            ->first();

        if ($ctdtQuestion) {
            foreach ($phieuKhaoSatWithDetails->chiTiet as $chiTiet) {
                if ($chiTiet->cauhoi_id === $ctdtQuestion->id) {
                    $ma = $chiTiet->giatri_text ?? $chiTiet->giatri_number ?? null;
                    if ($ma) {
                        $tenCtdt = Ctdt::where('mactdt', $ma)->value('tenctdt');
                        if ($tenCtdt) {
                            $chiTiet->giatri_text = $tenCtdt;
                        }
                    }
                }
            }
        }

        // Chuẩn bị dữ liệu thông tin phiếu
        $phieuInfo = [
            'id' => $phieuKhaoSat->id,
            'ten_dot' => $dotKhaoSat->ten_dot ?? 'N/A',
            'thoi_gian_nop' => $phieuKhaoSat->thoigian_hoanthanh ?
                $phieuKhaoSat->thoigian_hoanthanh : 'N/A',

            // Tính thời gian làm bài dưới dạng phút:giây
            'thoi_gian_lam_bai' => (function () use ($phieuKhaoSat) {
                $batDau = $phieuKhaoSat->thoigian_batdau;
                $hoanThanh = $phieuKhaoSat->thoigian_hoanthanh;
                if (!$batDau || !$hoanThanh)
                    return 'N/A';

                $diffSeconds = $batDau->diffInSeconds($hoanThanh);
                $minutes = floor($diffSeconds / 60);
                $seconds = $diffSeconds % 60;
                $time = $minutes . ':' . $seconds;
                return $time;
            })(),
        ];

        // Chuẩn hóa dữ liệu câu trả lời
        $allQuestions = $phieuKhaoSatWithDetails->dotKhaoSat->mauKhaoSat->cauHoi ?? collect();
        $personalInfoQuestions = $allQuestions->where('is_personal_info', true)->values();
        $surveyQuestions = $allQuestions->where('is_personal_info', false)->values();

        // Nhóm câu trả lời theo câu hỏi
        $answersByQuestionId = $phieuKhaoSatWithDetails->chiTiet->groupBy('cauhoi_id');

        $personalInfoAnswers = [];
        $surveyAnswers = [];

        // Xử lý thông tin cá nhân
        foreach ($personalInfoQuestions as $question) {
            $answers = $answersByQuestionId->get($question->id);
            $display = $this->formatAnswerForDisplay($answers, $question);

            $personalInfoAnswers[] = [
                'cau_hoi' => $question->noidung_cauhoi,
                'cau_tra_loi' => $display ?: '(Không trả lời)'
            ];
        }

        // Xử lý câu hỏi khảo sát
        foreach ($surveyQuestions as $index => $question) {
            $answers = $answersByQuestionId->get($question->id);

            // Xử lý loại câu hỏi chọn nhiều đáp án (multiple_choice)
            if ($question->loai_cauhoi === 'multiple_choice' && $answers) {
                $display = $answers->map(function ($ans) {
                    return $ans->phuongAn->noidung ?? '';
                })->filter()->implode('; ');
            } else {
                $display = $this->formatAnswerForDisplay($answers, $question);
            }

            $surveyAnswers[] = [
                'cau_hoi' => $question->noidung_cauhoi,
                'cau_tra_loi' => $display ?: '(Không trả lời)'
            ];
        }

        // Lưu vào session
        session([
            'khao_sat_review_data' => [
                'phieu_info' => $phieuInfo,
                'personal_info_answers' => $personalInfoAnswers,
                'survey_answers' => $surveyAnswers,
                'total_questions' => $allQuestions->count(),
                'personal_info_count' => $personalInfoQuestions->count(),
                'survey_questions_count' => $surveyQuestions->count()
            ]
        ]);
    }

    /**
     * Chuẩn hóa câu trả lời để hiển thị
     */
    private function formatAnswerForDisplay($answers, $question)
    {
        if (!$answers || $answers->count() === 0) {
            return '';
        }

        switch ($question->loai_cauhoi) {
            case 'multiple_choice':
                return $answers->map(function ($ans) {
                    return $ans->phuongAn->noidung ?? '';
                })->filter()->implode('; ');

            case 'single_choice':
            case 'likert':
                $first = $answers->first();
                return $first->phuongAn->noidung ?? '';

            case 'rating':
            case 'number':
                $first = $answers->first();
                return $first->giatri_number ?? '';

            case 'date':
                $first = $answers->first();
                return $first->giatri_date ?
                    $first->giatri_date : '';

            case 'select_ctdt':
            case 'text':
            default:
                $first = $answers->first();
                return $first->giatri_text ?? '';
        }
    }
}