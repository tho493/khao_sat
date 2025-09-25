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
        $frequencyInMinutes = 1;
        $lastRun = Cache::get('survey_status_last_run', 0);
        $lastRunTime = $lastRun ? Carbon::createFromTimestamp($lastRun) : null;

        $shouldRun = is_null($lastRunTime)
            || Carbon::now()->diffInMinutes($lastRunTime) >= $frequencyInMinutes;

        if ($shouldRun && Cache::add('survey_status_running', true, 10)) {
            try {
                Artisan::call('surveys:update-status');
                Cache::put('survey_status_last_run', Carbon::now()->timestamp);
            } catch (\Throwable $e) {
                \Log::error('[Auto Check] Middleware failed to run surveys:update-status: ' . $e->getMessage());
            } finally {
                Cache::forget('survey_status_running');
            }
        }

        return $next($request);
    }

    /**
     * Logic cập nhật trạng thái
     *
     * @return void
     */
    protected function runUpdateLogic()
    {
        Artisan::call('surveys:update-status');
    }
}