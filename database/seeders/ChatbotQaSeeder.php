<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotQaSeeder extends Seeder
{
    public function run()
    {
        DB::table('chatbot_qa')->insert([
            [
                'keywords' => 'bảo mật,an toàn,dữ liệu,cá nhân,thông tin',
                'question' => 'Thông tin của tôi có được bảo mật không?',
                'answer' => 'Chào bạn, chúng tôi cam kết mọi thông tin cá nhân và câu trả lời của bạn đều được <strong>bảo mật tuyệt đối</strong> và chỉ được sử dụng cho mục đích thống kê, nghiên cứu tổng hợp.',
                'is_enabled' => true,
            ],
            [
                'keywords' => 'hạn,cuối,hạn chót,kết thúc,nộp',
                'question' => 'Khi nào là hạn cuối của khảo sát này?',
                'answer' => 'Chào bạn, mỗi đợt khảo sát sẽ có thời gian kết thúc riêng. Bạn có thể xem hạn cuối được ghi rõ ở đầu trang khảo sát nhé!',
                'is_enabled' => true,
            ],
            [
                'keywords' => 'bắt buộc,thiếu,bỏ qua,câu hỏi',
                'question' => 'Tôi có phải trả lời tất cả câu hỏi không?',
                'answer' => 'Chào bạn, bạn nên trả lời tất cả các câu hỏi để cung cấp thông tin đầy đủ nhất. Tuy nhiên, chỉ những câu hỏi có dấu sao màu đỏ (*) là bắt buộc phải trả lời.',
                'is_enabled' => true,
            ],
            [
                'keywords' => 'lỗi,gửi,submit,không được',
                'question' => 'Tôi bị lỗi không gửi được khảo sát?',
                'answer' => 'Chào bạn, nếu bạn không gửi được khảo sát, vui lòng thử các bước sau: <br>1. Kiểm tra lại kết nối mạng. <br>2. Đảm bảo đã trả lời tất cả các câu hỏi bắt buộc (*). <br>3. Xác thực reCAPTCHA "Tôi không phải người máy". <br>4. Thử tải lại trang và làm lại. Dữ liệu của bạn đã được tự động lưu.',
                'is_enabled' => true,
            ],
            [
                'keywords' => 'cảm ơn,ok,chào,hello',
                'question' => 'Chào bot',
                'answer' => 'Chào bạn, tôi là trợ lý ảo của hệ thống khảo sát. Tôi có thể giúp gì cho bạn?',
                'is_enabled' => true,
            ],
        ]);
    }
}