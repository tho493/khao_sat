<?php

namespace App\Services;

use App\Models\DotKhaoSat;
use Illuminate\Support\Facades\DB;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Carbon\Carbon;


class ChatbotAIService
{
    /**
     * Lấy câu trả lời thông minh từ Google Gemini.
     */
    public function getSmartResponse(string $userMessage, ?DotKhaoSat $dotKhaoSat): array
    {
        // Kiểm tra xem API key có được cấu hình không
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            \Log::error("Gemini API key is not configured.");
            return [
                'success' => false,
                'answer' => 'Dịch vụ AI chưa được cấu hình. Vui lòng liên hệ quản trị viên.'
            ];
        }

        $faqData = $this->getFaqData();

        // Xử lý trường hợp dotKhaoSat = null
        if (is_null($dotKhaoSat)) {
            $surveyContext = "Không có thông tin về đợt khảo sát hiện tại.";
            $questionsContext = "Không có câu hỏi khảo sát nào được cung cấp.";
        } else {
            $surveyContext = $this->getSurveyContext($dotKhaoSat);
            $questionsContext = $this->getSurveyQuestionsContext($dotKhaoSat);
        }

        $systemPrompt = <<<PROMPT
            BẠN LÀ MỘT API PHÂN TÍCH. Vai trò của bạn là phân tích yêu cầu của người dùng và trả về kết quả dưới dạng một chuỗi JSON hợp lệ để điều khiển giao diện form.

            **QUY TẮC VÀNG:**
            -   **Luôn trả lời bằng JSON.** Nếu người dùng hỏi một câu thông thường, hãy trả lời bằng JSON có định dạng `{"action": "show_message", "message": "Nội dung câu trả lời của bạn."}`.
            -   **KHÔNG BAO GIỜ** được thêm giải thích, lời chào, hay ký tự ``` vào câu trả lời JSON. Câu trả lời phải bắt đầu bằng `{` và kết thúc bằng `}`.

            ---

            ### **CẤU TRÚC FORM KHẢO SÁT**
            Đây là danh sách các ô nhập liệu và câu hỏi trên trang.
            {$surveyContext}

            **PHẦN 1: THÔNG TIN CÁ NHÂN**
            - Mã số: (name: 'ma_nguoi_traloi', type: 'text')
            - Họ và tên: (name: 'metadata[hoten]', type: 'text')
            - Đơn vị/Khoa: (name: 'metadata[donvi]', type: 'text')
            - Email: (name: 'metadata[email]', type: 'text')

            **PHẦN 2: DANH SÁCH CÂU HỎI**
            {$questionsContext}

            ---

            ### **DANH SÁCH CÔNG CỤ (JSON ACTIONS)**

            Dựa vào yêu cầu của người dùng và cấu trúc form ở trên, hãy chọn và tạo JSON action phù hợp.

            **Công cụ 1: `show_message`**
            - **Mô tả:** Dùng để trả lời các câu hỏi thông thường hoặc khi không có công cụ nào khác phù hợp.
            - **Kích hoạt khi:** Người dùng hỏi "khảo sát này để làm gì?", "hạn cuối là khi nào?", "chào bạn", v.v.
            - **Kiến thức:** Sử dụng BỐI CẢNH và KIẾN THỨC NỀN (FAQ) dưới đây để trả lời.
            - **Định dạng JSON:** `{"action": "show_message", "message": "Nội dung câu trả lời của bạn."}`

            **Công cụ 2: `fill_text` (Nhập văn bản / số / ngày)**
            - **Mô tả:** Dùng để điền giá trị vào các ô input loại `text`, `textarea`, `number`, `date`.
            - **Kích hoạt khi:** Người dùng cung cấp thông tin cá nhân hoặc câu trả lời tự do.
                - *Ví dụ:* "mã số của tôi là sv123"
                - *Ví dụ:* "câu 2 tôi trả lời là..."
                - *Ví dụ:* "cho câu 6 là ngày 25/12/2024"
            - **Định dạng JSON:** `{"action": "fill_text", "selector": "[name='tên_thuộc_tính']", "value": "chuỗi_giá_trị"}`
            - **Lưu ý:** Với ngày tháng, hãy cố gắng chuyển đổi thành định dạng `YYYY-MM-DD`. Ví dụ, "ngày mai" -> `{{ now()->addDay()->format('Y-m-d') }}`.

            **Công cụ 3: `select_single` (Chọn một)**
            - **Mô tả:** Dùng để chọn MỘT phương án cho câu hỏi `single_choice`, `likert`, hoặc `rating`.
            - **Kích hoạt khi:** Người dùng chỉ định MỘT lựa chọn.
                - *Ví dụ:* "câu 1 tôi chọn rất tốt"
                - *Ví dụ:* "câu 5 tôi đánh giá 4 sao"
            - **Định dạng JSON:** `{"action": "select_single", "selector": "[name='tên_thuộc_tính']", "value": "giá_trị_phương_án"}`
            - **Lưu ý:** `value` trong JSON phải là `value` số của phương án (ví dụ: '201'), không phải nội dung ('Rất tốt').

            **Công cụ 4: `select_multiple` (Chọn nhiều)**
            - **Mô tả:** Dùng để chọn MỘT hoặc NHIỀU phương án cho câu hỏi `multiple_choice`.
            - **Kích hoạt khi:** Người dùng chỉ định MỘT hoặc NHIỀU lựa chọn.
                - *Ví dụ:* "câu 4 tôi chọn phương án A và B"
            - **Định dạng JSON:** `{"action": "select_multiple", "selector": "[name='tên_thuộc_tính[]']", "values": ["giá_trị_1", "giá_trị_2"]}`

            **Công cụ 5: `scroll_to_question`**
            - **Mô tả:** Cuộn trang đến một câu hỏi.
            - **Định dạng JSON:** `{"action": "scroll_to_question", "question_number": số_nguyên}`

            **Công cụ 6: `check_missing`**
            - **Mô tả:** Kiểm tra các câu bắt buộc còn thiếu.
            - **Định dạng JSON:** `{"action": "check_missing"}`

            ---

            ### **KIẾN THỨC FAQ**
            {$faqData}

            ### **YÊU CẦU CỦA NGƯỜI DÙNG CẦN PHÂN TÍCH:**
            "{$userMessage}"
        PROMPT;

        try {
            $response = Gemini::generativeModel(model: 'gemini-2.5-pro')->generateContent(Content::parse(part: [trim($systemPrompt)]));

            $aiMessage = $response->text();

            if (preg_match('/```json\s*(\{.*?\})\s*```/s', $aiMessage, $matches)) {
                $aiMessage = $matches[1];
            } else {
                $aiMessage = trim($aiMessage, " \t\n\r\0\x0B`");
            }

            $actionData = json_decode($aiMessage, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($actionData['action'])) {
                return [
                    'success' => true,
                    'type' => 'action',
                    'data' => $actionData,
                ];
            }

            // Nếu không, đây là một tin nhắn văn bản bình thường
            return [
                'success' => true,
                'type' => 'message',
                'answer' => $aiMessage,
            ];

        } catch (\Exception $e) {
            \Log::error("Gemini API Error: " . $e->getMessage());
            return [
                'success' => false,
                'answer' => $e->getMessage()
                // 'Xin lỗi, tôi đang gặp sự cố kết nối với trợ lý AI. Vui lòng thử lại sau.'
            ];
        }
    }


    public function summarizeText(string $textToSummarize, string $questionContext): array
    {
        // prompt cho việc tóm tắt
        $prompt = <<<PROMPT
            BẠN LÀ MỘT CHUYÊN GIA PHÂN TÍCH DỮ LIỆU.
            Nhiệm vụ của bạn là đọc tất cả các ý kiến phản hồi dưới đây cho câu hỏi khảo sát: "{$questionContext}"

            Sau đó, hãy tóm tắt các ý kiến này thành 3 đến 5 gạch đầu dòng (bullet points) chính. Mỗi gạch đầu dòng cần nêu bật được một chủ đề hoặc một vấn đề nổi cộm được nhiều người đề cập nhất.
            Sử dụng ngôn ngữ trang trọng, khách quan và trình bày dưới dạng HTML với thẻ `<ul>` và `<li>`.
            **KHÔNG BAO GIỜ** được thêm giải thích hoặc thông tin không liên quan.

            Dưới đây là danh sách các phản hồi cần tóm tắt:
            - {$textToSummarize}
        PROMPT;

        try {
            $response = Gemini::generativeModel(model: 'gemini-2.5-flash')->generateContent(
                Content::parse(part: [trim($prompt)])
            );

            return [
                'success' => true,
                'text' => $response->text()
            ];

        } catch (\Exception $e) {
            \Log::error("Gemini Summarize Error: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function getFaqData()
    {
        $faqs = DB::table('chatbot_qa')->where('is_enabled', true)->get();
        $faqString = "";
        foreach ($faqs as $faq) {
            $faqString .= "- Nếu người dùng hỏi về '{$faq->keywords}', hãy trả lời: '{$faq->answer}'\n";
        }
        return $faqString ? $faqString : null;
    }

    protected function getSurveyContext(DotKhaoSat $dotKhaoSat)
    {
        $mauKhaoSat = $dotKhaoSat->mauKhaoSat;

        // Lấy số lượng câu hỏi một cách an toàn
        $soLuongCauHoi = $mauKhaoSat ? $mauKhaoSat->cauHoi->count() : 0;
        $endDate = Carbon::parse($dotKhaoSat->denngay);

        return "
        - Tên đợt khảo sát: {$dotKhaoSat->ten_dot}
        - Ngày kết thúc: {$endDate->format('d/m/Y')}
        - Tổng số câu hỏi: {$soLuongCauHoi} câu";
    }

    protected function getSurveyQuestionsContext(?DotKhaoSat $dotKhaoSat): string
    {
        if (!$dotKhaoSat || !$dotKhaoSat->mauKhaoSat) {
            return "Không có.";
        }

        $questions = $dotKhaoSat->mauKhaoSat->cauHoi;

        if ($questions->isEmpty()) {
            return "Khảo sát này chưa có câu hỏi nào.";
        }

        $questionString = "";
        foreach ($questions as $index => $question) {
            $questionString .= "- Câu " . ($index + 1) . ": {$question->noidung_cauhoi}\n";
            $questionString .= "  { \"type\": \"{$question->loai_cauhoi}\", \"name\": \"cau_tra_loi[{$question->id}]" . ($question->loai_cauhoi == 'multiple_choice' ? '[]' : '') . "\" }\n";

            if (in_array($question->loai_cauhoi, ['single_choice', "multiple_choice", "likert"]) && $question->phuongAnTraLoi->isNotEmpty()) {
                $questionString .= "  Lựa chọn:\n";
                foreach ($question->phuongAnTraLoi->sortBy('thutu') as $pa) {
                    $questionString .= "  - '{$pa->noidung}' (value: '{$pa->id}')\n";
                }
            } elseif ($question->loai_cauhoi == 'rating') {
                $questionString .= "  Lựa chọn: các số từ 1 đến 5 (1=Tệ, 5=Tốt)\n";
            }
        }
        return trim($questionString);
    }
}