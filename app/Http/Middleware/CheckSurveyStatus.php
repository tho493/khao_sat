<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CheckSurveyStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Thời gian chờ giữa các lần kiểm tra (tính bằng phút)
        $frequencyInMinutes = 60; // Chạy mỗi 60 phút (1 giờ)

        // Sử dụng Cache của Laravel để lưu thời gian, hiệu quả hơn đọc file
        $lastRun = Cache::get('survey_status_last_run', 0);

        // Chuyển timestamp thành đối tượng Carbon
        $lastRunTime = Carbon::createFromTimestamp($lastRun);

        // Kiểm tra xem đã đủ thời gian để chạy lại chưa
        if (Carbon::now()->diffInMinutes($lastRunTime) >= $frequencyInMinutes) {

            // Đặt một "khóa" để tránh nhiều người dùng cùng kích hoạt một lúc
            // `add` sẽ chỉ thành công nếu key chưa tồn tại.
            if (Cache::add('survey_status_running', true, 10)) { // Khóa tồn tại trong 10 phút

                // --- GỌI TRỰC TIẾP LOGIC CỦA COMMAND ---
                // Đây là cách tốt hơn thay vì gọi `Artisan::call`
                $this->runUpdateLogic();

                // Cập nhật lại thời gian chạy cuối cùng vào cache
                Cache::put('survey_status_last_run', Carbon::now()->timestamp);

                // Xóa khóa
                Cache::forget('survey_status_running');
            }
        }

        // Cho phép request tiếp tục đi đến controller
        return $next($request);
    }

    /**
     * Logic cập nhật trạng thái, được tách ra từ Artisan Command.
     *
     * @return void
     */
    protected function runUpdateLogic()
    {
        try {
            $today = Carbon::today();

            // Kích hoạt các đợt khảo sát "Nháp"
            \App\Models\DotKhaoSat::where('trangthai', 'draft')
                ->where('tungay', '<=', $today)
                ->update(['trangthai' => 'active']);

            // Đóng các đợt khảo sát "Đang hoạt động" đã hết hạn
            \App\Models\DotKhaoSat::where('trangthai', 'active')
                ->where('denngay', '<', $today)
                ->update(['trangthai' => 'closed']);

            // Ghi lại log hệ thống (tùy chọn)
            \Log::info('[Auto Check] Survey statuses updated successfully.');

        } catch (\Exception $e) {
            \Log::error('[Auto Check] Failed to update survey statuses: ' . $e->getMessage());
        }
    }
}