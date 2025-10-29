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

        // Tạo command với các options để xử lý foreign keys và duplicate data
        $mysqlCmd = sprintf(
            '%s --default-character-set=utf8mb4 --force -h%s -P%s -u%s %s',
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

                // Restore từ file tạm với xử lý lỗi chi tiết
                $result = $this->executeRestore($mysqlCmd, $tempFile, $file);
                unlink($tempFile); // Xóa file tạm

                return $result;
            } else {
                $this->error("Không thể mở file gzip: {$file}");
                return self::FAILURE;
            }
        } else {
            return $this->executeRestore($mysqlCmd, $absPath, $file);
        }
    }

    private function executeRestore(string $mysqlCmd, string $filePath, string $originalFile): int
    {
        // Tạo file SQL với các commands để xử lý foreign keys
        $tempSqlFile = tempnam(sys_get_temp_dir(), 'db_restore_processed_');

        // Đọc file SQL gốc
        $sqlContent = file_get_contents($filePath);

        // Thêm các commands để xử lý foreign keys và duplicate data
        $processedSql = $this->processSqlForRestore($sqlContent);

        // Ghi file SQL đã xử lý
        file_put_contents($tempSqlFile, $processedSql);

        // Thực thi restore
        $cmdStr = sprintf('%s < %s', $mysqlCmd, escapeshellarg($tempSqlFile));

        $exit = 0;
        $output = [];
        $errorOutput = [];

        // Capture cả output và error
        exec($cmdStr . ' 2>&1', $output, $exit);

        // Xóa file tạm
        unlink($tempSqlFile);

        if ($pass = env('DB_PASSWORD'))
            putenv('MYSQL_PWD');

        if ($exit === 0) {
            $this->info("Đã restore DB từ {$originalFile}");

            // Hiển thị thông tin về các bảng đã được restore
            if (!empty($output)) {
                $this->line("Chi tiết restore:");
                foreach ($output as $line) {
                    if (strpos($line, 'Query OK') !== false || strpos($line, 'Records:') !== false) {
                        $this->line("  " . $line);
                    }
                }
            }

            return self::SUCCESS;
        }

        $this->error("Restore thất bại với exit code: {$exit}");

        // Hiển thị lỗi chi tiết
        if (!empty($output)) {
            $this->error("Chi tiết lỗi:");
            foreach ($output as $line) {
                if (strpos($line, 'ERROR') !== false || strpos($line, 'Duplicate') !== false) {
                    $this->error("  " . $line);
                }
            }
        }

        $this->error("Kiểm tra quyền, đường dẫn DB_CLIENT_BIN và cấu trúc database.");
        return self::FAILURE;
    }

    private function processSqlForRestore(string $sqlContent): string
    {
        // Thêm các commands để xử lý foreign keys và duplicate data
        $processedSql = "-- Restore processed SQL\n";
        $processedSql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $processedSql .= "SET UNIQUE_CHECKS = 0;\n";
        $processedSql .= "SET AUTOCOMMIT = 0;\n";
        $processedSql .= "START TRANSACTION;\n\n";

        // Thêm SQL gốc
        $processedSql .= $sqlContent;

        // Thêm các commands để kết thúc
        $processedSql .= "\n\nCOMMIT;\n";
        $processedSql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $processedSql .= "SET UNIQUE_CHECKS = 1;\n";
        $processedSql .= "SET AUTOCOMMIT = 1;\n";

        return $processedSql;
    }
}
