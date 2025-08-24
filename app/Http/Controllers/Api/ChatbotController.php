<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $userMessage = strtolower($validated['message']);

        // 2. Tách tin nhắn của người dùng thành các từ
        $userWords = preg_split('/[\s,]+/', $userMessage);

        // 3. Xây dựng câu query tìm kiếm
        $query = DB::table('chatbot_qa')->where('is_enabled', true);

        // Tìm kiếm dựa trên từng từ trong tin nhắn của người dùng
        $query->where(function ($q) use ($userWords) {
            foreach ($userWords as $word) {
                if (strlen($word) > 2) { // Chỉ tìm các từ có hơn 2 ký tự
                    $q->orWhere('keywords', 'LIKE', '%' . $word . '%');
                }
            }
        });

        // Ưu tiên các kết quả khớp nhiều từ khóa hơn (nếu DB hỗ trợ)
        // Hoặc đơn giản là lấy kết quả đầu tiên
        $bestMatch = $query->first();

        // 4. Chuẩn bị câu trả lời
        if ($bestMatch) {
            $answer = $bestMatch->answer;
        } else {
            // Câu trả lời mặc định nếu không tìm thấy
            $answer = "Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể thử hỏi bằng cách khác hoặc liên hệ bộ phận hỗ trợ để được giúp đỡ.";
        }

        return response()->json([
            'answer' => $answer,
        ]);
    }
}