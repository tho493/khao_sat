<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:db {--name=} {--gzip}';
    protected $description = 'Backup MySQL thành file .sql (hoặc .sql.gz) ở storage/app/backups/db';

    public function handle(): int
    {
        $dir = 'backup/db';
        $dirPath = storage_path("app/{$dir}");
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $ts = now()->format('Ymd_His');
        $base = $this->option('name') ? $this->option('name') . ".sql" : "db_{$ts}.sql";
        $gzip = (bool) $this->option('gzip');

        if ($gzip && !str_ends_with($base, '.gz')) {
            $base .= '.gz';
        }

        $relPath = "{$dir}/{$base}";
        $absPath = storage_path("app/{$relPath}");

        $host = env('DB_HOST', '127.0.0.1');
        $port = (int) env('DB_PORT', 3306);
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $db = env('DB_DATABASE');

        $binDir = rtrim((string) env('DB_CLIENT_BIN', ''), DIRECTORY_SEPARATOR);
        $dumpBinary = $binDir ? $binDir . DIRECTORY_SEPARATOR . 'mysqldump' : 'mysqldump';

        // Build command
        $cmd = [
            escapeshellcmd($dumpBinary),
            '--single-transaction',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            '-h' . escapeshellarg($host),
            '-P' . escapeshellarg((string) $port),
            '-u' . escapeshellarg($user),
        ];

        if ($pass) {
            putenv('MYSQL_PWD=' . $pass);
        }

        $cmd[] = escapeshellarg($db);

        // Xuất ra file (có thể gzip)
        if ($gzip) {
            // Sử dụng PHP gzip thay vì shell gzip để tương thích Windows/Linux
            $tempFile = tempnam(sys_get_temp_dir(), 'db_backup_');
            $cmdStr = implode(' ', $cmd) . ' > ' . escapeshellarg($tempFile);

            // Thực thi mysqldump
            $exit = 0;
            $output = [];
            exec($cmdStr, $output, $exit);

            if ($exit === 0 && is_file($tempFile)) {
                // Nén file bằng PHP gzip
                $gz = gzopen($absPath, 'wb9');
                if ($gz) {
                    $fp = fopen($tempFile, 'rb');
                    if ($fp) {
                        while (!feof($fp)) {
                            gzwrite($gz, fread($fp, 8192));
                        }
                        fclose($fp);
                    }
                    gzclose($gz);
                }
                unlink($tempFile); // Xóa file tạm
            }
        } else {
            $cmdStr = implode(' ', $cmd) . ' > ' . escapeshellarg($absPath);

            // Thực thi
            $exit = 0;
            $output = [];
            exec($cmdStr, $output, $exit);
        }

        // Clear MYSQL_PWD để an toàn
        if ($pass)
            putenv('MYSQL_PWD');

        if ($exit === 0 && is_file($absPath)) {
            $this->info("Đã backup DB: {$relPath}");
            return self::SUCCESS;
        }

        $this->error("Backup thất bại. Kiểm tra quyền, đường dẫn DB_CLIENT_BIN và client mysql/mysqldump.");
        return self::FAILURE;
    }
}
