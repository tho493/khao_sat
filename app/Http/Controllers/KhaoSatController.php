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
use Illuminate\Support\Str;

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
            },
            'cauHoi.dataSource.values'
        ])->first();

        if (!$mauKhaoSat) {
            return redirect()->route('khao-sat.index')
                ->with('error', 'Không tìm thấy mẫu khảo sát cho đợt này.');
        }

        // Phân loại câu hỏi: thông tin cá nhân và câu hỏi thường, rồi gom nhóm theo trang
        $personalInfoQuestions = $mauKhaoSat->cauHoi->where('is_personal_info', true);
        $questionsByPage = $mauKhaoSat->cauHoi
            ->where('is_personal_info', false)
            ->groupBy('page');

        // Nếu là admin (đăng nhập), hiển thị cảnh báo chế độ admin
        $adminModeWarning = ($isAdminMode) ? 'Bạn đang ở chế độ quản trị viên (Admin) nên có thể xem trước. Khảo sát đang ở chế độ ' . $dotKhaoSat->trangthai : null;
        return view('khao-sat.show', compact('dotKhaoSat', 'mauKhaoSat', 'questionsByPage', 'personalInfoQuestions', 'adminModeWarning'));
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

        $hasDuplicateAnswer = false;
        $duplicateQuestionNames = [];

        // Lấy tất cả các câu hỏi của mẫu khảo sát này được đánh dấu là cần kiểm tra trùng lặp
        $duplicateCheckQuestions = $dotKhaoSat->mauKhaoSat->cauHoi()
            ->where('check_duplicate', 1)
            ->get()
            ->keyBy('id');

        // Thực hiện kiểm tra trùng lặp trước khi bắt đầu transaction
        foreach ($request->input('cau_tra_loi', []) as $cauHoiId => $traLoi) {
            // Chỉ kiểm tra nếu câu hỏi này được đánh dấu là check_duplicate
            if (isset($duplicateCheckQuestions[$cauHoiId])) {
                $question = $duplicateCheckQuestions[$cauHoiId];

                // Bỏ qua nếu câu trả lời rỗng, vì câu trả lời rỗng không thể bị trùng lặp
                if (is_null($traLoi) || (is_string($traLoi) && trim($traLoi) === '') || (is_array($traLoi) && empty($traLoi))) {
                    continue;
                }

                // Xây dựng truy vấn để kiểm tra các câu trả lời hiện có trong đợt khảo sát này
                $existingAnswerQuery = PhieuKhaoSatChiTiet::where('cauhoi_id', $cauHoiId)
                    ->whereHas('phieuKhaoSat', function ($query) use ($dotKhaoSat) {
                        $query->where('dot_khaosat_id', $dotKhaoSat->id)
                            ->where('trangthai', 'completed'); // Chỉ kiểm tra với các phiếu đã hoàn thành
                    });

                $isDuplicate = false;

                switch ($question->loai_cauhoi) {
                    case 'single_choice':
                    case 'likert':
                        $isDuplicate = $existingAnswerQuery->where('phuongan_id', $traLoi)->exists();
                        break;
                    case 'rating':
                    case 'number':
                        $isDuplicate = $existingAnswerQuery->where('giatri_number', $traLoi)->exists();
                        break;
                    case 'date':
                        $isDuplicate = $existingAnswerQuery->where('giatri_date', $traLoi)->exists();
                        break;
                    case 'custom_select':
                    case 'text':
                        $isDuplicate = $existingAnswerQuery->where('giatri_text', $traLoi)->exists();
                        break;
                    case 'multiple_choice':
                        // Loại câu hỏi này không cần thiết
                        break;
                }

                if ($isDuplicate) {
                    $hasDuplicateAnswer = true;
                    $duplicateQuestionNames[] = $question->noidung_cauhoi;
                }
            }
        }

        DB::beginTransaction();
        try {
            // Tạo phiếu khảo sát, bao gồm trạng thái trùng lặp
            $phieuKhaoSat = PhieuKhaoSat::create([
                'dot_khaosat_id' => $dotKhaoSat->id,
                'thoigian_batdau' => $request->metadata['thoigian_batdau'] ?? null,
                'trangthai' => 'draft',
                'ip_address' => $request->ip(), // Lưu địa chỉ IP của người dùng
                'user_agent' => $request->userAgent(), // Lưu thông tin trình duyệt/thiết bị
                'is_duplicate' => $hasDuplicateAnswer ? 1 : 0, // Đặt cờ trùng lặp
                'token' => Str::uuid(), // Token bảo mật
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

                    case 'custom_select':
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

            // Chuẩn bị thông báo phản hồi
            $message = 'Gửi khảo sát thành công';
            if ($hasDuplicateAnswer) {
                $message .= ' Tuy nhiên, một hoặc nhiều câu trả lời của bạn cho các câu hỏi sau đã được ghi nhận trước đó trong đợt khảo sát này: ' . implode(', ', array_unique($duplicateQuestionNames)) . '. Bạn không nên spam khảo sát này nữa';
            }

            // Lưu dữ liệu vào session để hiển thị trong review (nếu người dùng xem ngay)
            $reviewData = $this->getReviewData($phieuKhaoSat);
            session(['khao_sat_review_data' => $reviewData]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('khao-sat.review'),
                'submission_id' => $phieuKhaoSat->id,
                'token' => $phieuKhaoSat->token
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
            return redirect()->route('khao-sat.thanks')
                ->with('error', 'Không tìm thấy dữ liệu khảo sát để xem lại.');
        }

        return view('khao-sat.review', compact('reviewData'));
    }

    public function reviewHistory(PhieuKhaoSat $phieuKhaoSat, Request $request)
    {
        // Kiểm tra token bảo mật strict mode
        if (!$phieuKhaoSat->token || $request->query('token') !== $phieuKhaoSat->token) {
            abort(403, 'Bạn không có quyền xem kết quả khảo sát này.');
        }

        // Cho phép xem lịch sử nếu có thông tin
        $reviewData = $this->getReviewData($phieuKhaoSat);
        return view('khao-sat.review', compact('reviewData'));
    }

    private function getReviewData($phieuKhaoSat)
    {
        // Lấy thông tin phiếu khảo sát
        $phieuKhaoSatWithDetails = PhieuKhaoSat::with([
            'dotKhaoSat.mauKhaoSat.cauHoi.dataSource.values',
            'chiTiet.phuongAn'
        ])->find($phieuKhaoSat->id);

        $dotKhaoSat = $phieuKhaoSatWithDetails->dotKhaoSat;
        $hasDuplicateAnswer = $phieuKhaoSatWithDetails->is_duplicate;

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
            $display = $this->formatAnswerForDisplay($answers, $question);

            $surveyAnswers[] = [
                'cau_hoi' => $question->noidung_cauhoi,
                'cau_tra_loi' => $display ?: '(Không trả lời)'
            ];
        }

        return [
            'phieu_info' => $phieuInfo,
            'personal_info_answers' => $personalInfoAnswers,
            'survey_answers' => $surveyAnswers,
            'total_questions' => $allQuestions->count(),
            'personal_info_count' => $personalInfoQuestions->count(),
            'survey_questions_count' => $surveyQuestions->count(),
            'has_duplicate_answer' => $hasDuplicateAnswer
        ];
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

            case 'custom_select':
                $first = $answers->first();
                $value = $first->giatri_text ?? '';
                if ($question->dataSource && $question->dataSource->values) {
                    $option = $question->dataSource->values->firstWhere('value', $value);
                    return $option->label ?? $value;
                }
                return $value;

            case 'text':
            default:
                $first = $answers->first();
                return $first->giatri_text ?? '';
        }
    }
}