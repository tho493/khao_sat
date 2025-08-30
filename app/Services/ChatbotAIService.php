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
    public function getSmartResponse(string $userMessage, DotKhaoSat $dotKhaoSat): array
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
        $surveyContext = $this->getSurveyContext($dotKhaoSat);
        $questionsContext = $this->getSurveyQuestionsContext($dotKhaoSat);

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

            **1. Công cụ `fill_text` (Dành cho Nhập văn bản)**
            -   **Mô tả:** Dùng để điền một chuỗi văn bản vào các ô input có `type: 'text'`, `textarea`, `number`, `date`.
            -   **Kích hoạt khi:** Người dùng cung cấp thông tin như mã số, họ tên, hoặc câu trả lời tự luận.
                -   *Ví dụ user:* "mã số của tôi là sv123"
                -   *Ví dụ user:* "câu 2 tôi trả lời là abc xyz"
            -   **Định dạng JSON:** `{"action": "fill_text", "selector": "[name='tên_thuộc_tính']", "value": "chuỗi văn bản"}`
            -   **Ví dụ JSON:** `{"action": "fill_text", "selector": "[name='ma_nguoi_traloi']", "value": "sv123"}`

            **2. Công cụ `select_single` (Dành cho Chọn một)**
            -   **Mô tả:** Dùng để chọn MỘT phương án duy nhất cho các câu hỏi `single_choice` (radio button) hoặc `likert`.
            -   **Kích hoạt khi:** Người dùng chỉ định MỘT lựa chọn cho một câu hỏi.
                -   *Ví dụ user:* "câu 1 tôi chọn rất tốt"
                -   *Ví dụ user:* "câu 3 là có"
            -   **Định dạng JSON:** `{"action": "select_single", "selector": "[name='tên_thuộc_tính']", "value": "giá_trị_phương_án"}`
            -   **Lưu ý:** `value` trong JSON phải là `value` của phương án (ví dụ: '201'), không phải là nội dung ('Rất tốt').
            -   **Ví dụ JSON:** `{"action": "select_single", "selector": "[name='cau_tra_loi[101]']", "value": "201"}`

            **3. Công cụ `select_multiple` (Dành cho Chọn nhiều)**
            -   **Mô tả:** Dùng để chọn MỘT hoặc NHIỀU phương án cho câu hỏi `multiple_choice` (checkbox).
            -   **Kích hoạt khi:** Người dùng chỉ định MỘT hoặc NHIỀU lựa chọn cho câu hỏi có thể chọn nhiều.
                -   *Ví dụ user:* "câu 4 tôi chọn phương án A và B"
                -   *Ví dụ user:* "ở câu 5, tick vào ô đầu tiên"
            -   **Định dạng JSON:** `{"action": "select_multiple", "selector": "[name='tên_thuộc_tính[]']", "values": ["giá_trị_1", "giá_trị_2"]}`
            -   **Lưu ý:** `values` trong JSON phải là một MẢNG chứa các `value` của phương án. Ngay cả khi chỉ có một lựa chọn, nó vẫn phải là một mảng.
            -   **Ví dụ JSON:** `{"action": "select_multiple", "selector": "[name='cau_tra_loi[104][]']", "values": ["208", "209"]}`

            **4. Công cụ `scroll_to_question`**
            -   **Mô tả:** Dùng để cuộn trang đến một câu hỏi cụ thể.
            -   **Kích hoạt khi:** Người dùng yêu cầu di chuyển. Ví dụ: "đến câu 5", "tới câu hỏi số ba".
            -   **Định dạng JSON:** `{"action": "scroll_to_question", "question_number": số_nguyên}`
            -   **Lưu ý:** Tự động chuyển đổi chữ số ("một", "hai"...) thành số nguyên.

            **5. Công cụ `check_missing`**
            -   **Mô tả:** Dùng để kiểm tra các câu hỏi bắt buộc chưa được trả lời.
            -   **Kích hoạt khi:** Người dùng hỏi về việc hoàn thành. Ví dụ: "kiểm tra giúp tôi", "còn thiếu câu nào không".
            -   **Định dạng JSON:** `{"action": "check_missing"}`

            ---

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

    protected function getFaqData()
    {
        $faqs = DB::table('chatbot_qa')->where('is_enabled', true)->get();
        $faqString = "";
        foreach ($faqs as $faq) {
            $faqString .= "- Nếu người dùng hỏi về '{$faq->keywords}', hãy trả lời: '{$faq->answer}'\n";
        }
        return $faqString;
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
            $inputName = "cau_tra_loi[{$question->id}]";

            $questionString .= "- Câu " . ($index + 1) . ": {$question->noidung_cauhoi} (name attribute: '{$inputName}')\n";

            if (in_array($question->loai_cauhoi, ['single_choice', 'multiple_choice', 'likert']) && $question->phuongAnTraLoi->isNotEmpty()) {

                $questionString .= "  Các lựa chọn có thể:\n";
                foreach ($question->phuongAnTraLoi->sortBy('thutu') as $pa) {
                    $questionString .= "  - '{$pa->noidung}' (có value là: '{$pa->id}')\n";
                }
            }
        }

        return trim($questionString);
    }
}