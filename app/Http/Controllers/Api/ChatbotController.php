<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChatbotAIService;
use App\Models\DotKhaoSat;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    protected ChatbotAIService $aiService;

    public function __construct(ChatbotAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'survey_id' => 'sometimes|exists:dot_khaosat,id',
        ]);

        $userMessage = $validated['message'];

        // $exactAnswer = $this->findExactAnswer($userMessage);

        // if ($exactAnswer) {
        //     return response()->json([
        //         'success' => true,
        //         'answer' => $exactAnswer,
        //         'source' => 'database' // nguồn để trả lời
        //     ]);
        // }

        $dotKhaoSat = DotKhaoSat::find($request->input('survey_id'));
        if (!$dotKhaoSat) {
            $dotKhaoSat = new DotKhaoSat(['ten_dot' => 'Chung', 'denngay' => now()->addDays(7)]);
        }

        // tạo câu trả lời bằng Service AI
        $response = $this->aiService->getSmartResponse($userMessage, $dotKhaoSat);

        // $response['source'] = 'openai';

        $statusCode = $response['success'] ? 200 : 500;
        return response()->json($response, $statusCode);
    }

    /**
     * Helper: Tìm kiếm câu trả lời chính xác trong bảng chatbot_qa.
     * @param string $userMessage
     * @return string|null
     */
    private function findExactAnswer(string $userMessage): ?string
    {
        $userMessageLower = mb_strtolower($userMessage, 'UTF-8');
        $userWords = array_filter(preg_split('/[\s,;.!?]+/', $userMessageLower));
        if (empty($userWords)) {
            return null;
        }

        $whereClauses = [];
        foreach ($userWords as $word) {
            if (mb_strlen($word) > 2) {
                $whereClauses[] = "keywords LIKE '%" . addslashes($word) . "%'";
            }
        }

        if (empty($whereClauses)) {
            return null;
        }

        $rawWhere = implode(' OR ', $whereClauses);
        $bestMatch = DB::table('chatbot_qa')
            ->where('is_enabled', true)
            ->whereRaw("({$rawWhere})")
            ->first();

        return $bestMatch ? $bestMatch->answer : null;
    }
}