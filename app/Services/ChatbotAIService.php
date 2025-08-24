<?php

namespace App\Services;

use App\Models\DotKhaoSat;
use Illuminate\Support\Facades\DB;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Content;
use Gemini\Enums\Role;


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

        $chatHistory = [
            Content::parse(
                role: Role::USER,
                part: [
                    "Bối cảnh của bạn như sau: 
                    Bạn là một trợ lý ảo thông minh, thân thiện và chuyên nghiệp của 'Hệ thống Khảo sát Trường Đại học Sao Đỏ'.
                    Nhiệm vụ của bạn là hỗ trợ người dùng hoàn thành khảo sát. Luôn trả lời bằng tiếng Việt, lịch sự, và ngắn gọn.
                    
                    ### THÔNG TIN KHẢO SÁT HIỆN TẠI:
                    {$surveyContext}
        
                    ### KIẾN THỨC NỀN (FAQ) - Sử dụng thông tin này để trả lời:
                    {$faqData}
                    
                    Bây giờ, hãy sẵn sàng trả lời câu hỏi của người dùng."
                ]
            ),
            Content::parse(
                role: Role::MODEL, // AI (Model) xác nhận đã hiểu
                part: [
                    "Đã hiểu. Tôi là trợ lý ảo của Hệ thống Khảo sát SDU. Tôi đã sẵn sàng hỗ trợ."
                ]
            ),
        ];

        try {
            $chat = Gemini::chat(model: 'gemini-2.5-flash')
                ->startChat(history: $chatHistory);

            // Gửi tin nhắn mới của người dùng
            $response = $chat->sendMessage($userMessage);

            $aiMessage = $response->text();

            return [
                'success' => true,
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
        return "
            - Tên đợt khảo sát: {$dotKhaoSat->ten_dot}
            - Ngày kết thúc: {$dotKhaoSat->denngay->format('d/m/Y')}
        ";
    }
}