<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\DotKhaoSat;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Update survey statuses based on start/end times
Artisan::command('surveys:update-status', function () {
    $now = now();

    $activatedCount = DotKhaoSat::where('trangthai', 'draft')
        ->where('tungay', '<=', $now)
        ->update(['trangthai' => 'active']);

    if ($activatedCount > 0) {
        Log::info("[Auto Check] Activated {$activatedCount} draft surveys.");
        $this->info("Activated {$activatedCount} draft surveys.");
    }

    $closedCount = DotKhaoSat::where('trangthai', 'active')
        ->where('denngay', '<', $now)
        ->update(['trangthai' => 'closed']);

    if ($closedCount > 0) {
        Log::info("[Auto Check] Closed {$closedCount} active surveys.");
        $this->info("Closed {$closedCount} active surveys.");
    }
})->purpose('Update survey statuses according to schedule');
