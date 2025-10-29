<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldBackups extends Command
{
    protected $signature = 'backup:cleanup {--days=30 : Số ngày để giữ lại backup} {--dry-run : Chỉ hiển thị file sẽ bị xóa mà không thực sự xóa}';
    protected $description = 'Xóa các bản backup cũ hơn số ngày chỉ định (mặc định 30 ngày)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $dir = 'backup/db';
        $dirPath = storage_path("app/{$dir}");

        if (!is_dir($dirPath)) {
            $this->info("Thư mục backup không tồn tại: {$dirPath}");
            return self::SUCCESS;
        }

        $cutoffTime = now()->subDays($days)->timestamp;
        $files = collect(scandir($dirPath))
            ->filter(fn($file) => $file !== '.' && $file !== '..')
            ->filter(fn($file) => str_ends_with(strtolower($file), '.sql') || str_ends_with(strtolower($file), '.sql.gz'))
            ->map(fn($file) => [
                'name' => $file,
                'path' => "{$dirPath}/{$file}",
                'time' => filemtime("{$dirPath}/{$file}"),
                'size' => filesize("{$dirPath}/{$file}"),
            ])
            ->filter(fn($file) => $file['time'] < $cutoffTime);

        if ($files->isEmpty()) {
            $this->info("Không có file backup nào cũ hơn {$days} ngày để xóa.");
            return self::SUCCESS;
        }

        $totalSize = $files->sum('size');
        $this->info("Tìm thấy {$files->count()} file backup cũ hơn {$days} ngày:");

        foreach ($files as $file) {
            $sizeKB = number_format($file['size'] / 1024, 1);
            $date = date('d/m/Y H:i:s', $file['time']);
            $this->line("  - {$file['name']} ({$sizeKB} KB) - {$date}");
        }

        $totalSizeMB = number_format($totalSize / (1024 * 1024), 2);
        $this->info("Tổng dung lượng sẽ được giải phóng: {$totalSizeMB} MB");

        if ($dryRun) {
            $this->warn("Chế độ DRY RUN - Không có file nào bị xóa thực sự.");
            return self::SUCCESS;
        }

        if (!$this->confirm("Bạn có chắc chắn muốn xóa {$files->count()} file backup này?")) {
            $this->info("Đã hủy thao tác xóa backup.");
            return self::SUCCESS;
        }

        $deletedCount = 0;
        $deletedSize = 0;
        $errors = [];

        foreach ($files as $file) {
            try {
                if (unlink($file['path'])) {
                    $deletedCount++;
                    $deletedSize += $file['size'];
                    $this->line("✓ Đã xóa: {$file['name']}");
                } else {
                    $errors[] = "Không thể xóa: {$file['name']}";
                }
            } catch (\Exception $e) {
                $errors[] = "Lỗi khi xóa {$file['name']}: " . $e->getMessage();
            }
        }

        if ($deletedCount > 0) {
            $deletedSizeMB = number_format($deletedSize / (1024 * 1024), 2);
            $this->info("✓ Đã xóa thành công {$deletedCount} file backup, giải phóng {$deletedSizeMB} MB.");
        }

        if (!empty($errors)) {
            $this->error("Có lỗi xảy ra:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
