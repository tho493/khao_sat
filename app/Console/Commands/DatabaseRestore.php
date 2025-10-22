<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseRestore extends Command
{
    protected $signature = 'restore:db {file : Tên file .sql hoặc .sql.gz trong storage/app/backups/db} {--force}';
    protected $description = 'Khôi phục MySQL từ file .sql/.sql.gz (GHI ĐÈ dữ liệu hiện tại)';

    public function handle(): int
    {
        $file = basename($this->argument('file'));
        $relPath = "backup/db/{$file}";
        $absPath = storage_path("app/{$relPath}");

        if (!is_file($absPath)) {
            $this->error("Không tìm thấy file: {$relPath}");
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm("Restore từ {$file}? TẤT CẢ dữ liệu hiện tại sẽ bị ghi đè!", false)) {
            $this->info('Hủy.');
            return self::SUCCESS;
        }

        $host = env('DB_HOST', '127.0.0.1');
        $port = (int) env('DB_PORT', 3306);
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $db = env('DB_DATABASE');

        $binDir = rtrim((string) env('DB_CLIENT_BIN', ''), DIRECTORY_SEPARATOR);
        $mysqlBin = $binDir ? $binDir . DIRECTORY_SEPARATOR . 'mysql' : 'mysql';

        if ($pass)
            putenv('MYSQL_PWD=' . $pass);

        $mysqlCmd = sprintf(
            '%s --default-character-set=utf8mb4 -h%s -P%s -u%s %s',
            escapeshellcmd($mysqlBin),
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($user),
            escapeshellarg($db)
        );

        $isGz = str_ends_with(strtolower($file), '.gz');

        if ($isGz) {
            $tempFile = tempnam(sys_get_temp_dir(), 'db_restore_');

            // Giải nén file gzip bằng PHP
            $gz = gzopen($absPath, 'rb');
            if ($gz) {
                $fp = fopen($tempFile, 'wb');
                if ($fp) {
                    while (!gzeof($gz)) {
                        fwrite($fp, gzread($gz, 8192));
                    }
                    fclose($fp);
                }
                gzclose($gz);

                // Restore từ file tạm
                $cmdStr = sprintf('%s < %s', $mysqlCmd, escapeshellarg($tempFile));

                $exit = 0;
                $output = [];
                exec($cmdStr, $output, $exit);

                unlink($tempFile); // Xóa file tạm
            } else {
                $this->error("Không thể mở file gzip: {$file}");
                return self::FAILURE;
            }
        } else {
            $cmdStr = sprintf('%s < %s', $mysqlCmd, escapeshellarg($absPath));

            $exit = 0;
            $output = [];
            exec($cmdStr, $output, $exit);
        }

        if ($pass)
            putenv('MYSQL_PWD');

        if ($exit === 0) {
            $this->info("Đã restore DB từ {$file}");
            return self::SUCCESS;
        }

        $this->error("Restore thất bại. Kiểm tra quyền và đường dẫn DB_CLIENT_BIN.");
        return self::FAILURE;
    }
}
