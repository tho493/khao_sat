<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseRepair extends Command
{
    protected $signature = 'db:repair {--check-only : Chỉ kiểm tra không sửa chữa}';
    protected $description = 'Kiểm tra và sửa chữa các vấn đề trong database sau khi restore';

    public function handle(): int
    {
        $checkOnly = $this->option('check-only');

        $this->info('Bắt đầu kiểm tra database...');

        $issues = [];

        // Kiểm tra các bảng bị hỏng
        $this->line('Kiểm tra các bảng bị hỏng...');
        $corruptedTables = $this->checkCorruptedTables();
        if (!empty($corruptedTables)) {
            $issues = array_merge($issues, $corruptedTables);
        }

        // Kiểm tra foreign key constraints
        $this->line('Kiểm tra foreign key constraints...');
        $fkIssues = $this->checkForeignKeyConstraints();
        if (!empty($fkIssues)) {
            $issues = array_merge($issues, $fkIssues);
        }

        // Kiểm tra duplicate data
        $this->line('Kiểm tra dữ liệu trùng lặp...');
        $duplicateIssues = $this->checkDuplicateData();
        if (!empty($duplicateIssues)) {
            $issues = array_merge($issues, $duplicateIssues);
        }

        // Kiểm tra missing indexes
        $this->line('Kiểm tra missing indexes...');
        $indexIssues = $this->checkMissingIndexes();
        if (!empty($indexIssues)) {
            $issues = array_merge($issues, $indexIssues);
        }

        if (empty($issues)) {
            $this->info('Database không có vấn đề gì!');
            return self::SUCCESS;
        }

        $this->warn('Phát hiện ' . count($issues) . ' vấn đề:');
        foreach ($issues as $issue) {
            $this->line("  - {$issue}");
        }

        if ($checkOnly) {
            $this->info('Chế độ kiểm tra chỉ đọc. Không thực hiện sửa chữa.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Bạn có muốn sửa chữa các vấn đề này không?')) {
            $this->info('Hủy sửa chữa.');
            return self::SUCCESS;
        }

        $this->info('Bắt đầu sửa chữa...');

        // Sửa chữa các vấn đề
        $fixed = $this->repairIssues($issues);

        if ($fixed > 0) {
            $this->info("Đã sửa chữa {$fixed} vấn đề.");
        } else {
            $this->warn('Không thể sửa chữa tự động. Vui lòng kiểm tra thủ công.');
        }

        return self::SUCCESS;
    }

    private function checkCorruptedTables(): array
    {
        $issues = [];

        try {
            $tables = DB::select("SHOW TABLES");
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];

                // Kiểm tra CHECK TABLE
                $result = DB::select("CHECK TABLE `{$tableName}`");
                foreach ($result as $row) {
                    if (isset($row->Msg_text) && strpos($row->Msg_text, 'error') !== false) {
                        $issues[] = "Bảng {$tableName} bị hỏng: " . $row->Msg_text;
                    }
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Lỗi khi kiểm tra bảng: " . $e->getMessage();
        }

        return $issues;
    }

    private function checkForeignKeyConstraints(): array
    {
        $issues = [];

        try {
            // Kiểm tra foreign key constraints bị vi phạm
            $result = DB::select("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($result as $fk) {
                // Kiểm tra xem referenced table có tồn tại không
                $refTableExists = DB::select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.TABLES 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$fk->REFERENCED_TABLE_NAME}'
                ");

                if ($refTableExists[0]->count == 0) {
                    $issues[] = "Foreign key constraint '{$fk->CONSTRAINT_NAME}' tham chiếu đến bảng không tồn tại: {$fk->REFERENCED_TABLE_NAME}";
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Lỗi khi kiểm tra foreign keys: " . $e->getMessage();
        }

        return $issues;
    }

    private function checkDuplicateData(): array
    {
        $issues = [];

        try {
            // Kiểm tra các bảng có thể có duplicate data
            $tables = ['taikhoan', 'dot_khao_sat', 'mau_khao_sat', 'cau_hoi_khao_sat'];

            foreach ($tables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    continue;
                }

                // Kiểm tra duplicate trong bảng taikhoan
                if ($table === 'taikhoan') {
                    $duplicates = DB::select("
                        SELECT tendangnhap, COUNT(*) as count 
                        FROM {$table} 
                        GROUP BY tendangnhap 
                        HAVING COUNT(*) > 1
                    ");

                    foreach ($duplicates as $dup) {
                        $issues[] = "Duplicate username '{$dup->tendangnhap}' trong bảng {$table} ({$dup->count} records)";
                    }
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Lỗi khi kiểm tra duplicate data: " . $e->getMessage();
        }

        return $issues;
    }

    private function checkMissingIndexes(): array
    {
        $issues = [];

        try {
            // Kiểm tra các index quan trọng có tồn tại không
            $expectedIndexes = [
                'taikhoan' => ['tendangnhap', 'email'],
                'dot_khao_sat' => ['nam_hoc_id', 'trang_thai'],
                'mau_khao_sat' => ['dot_khao_sat_id'],
                'cau_hoi_khao_sat' => ['mau_khao_sat_id', 'loai_cau_hoi'],
            ];

            foreach ($expectedIndexes as $table => $columns) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    continue;
                }

                foreach ($columns as $column) {
                    $indexes = DB::select("
                        SELECT INDEX_NAME 
                        FROM information_schema.STATISTICS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = '{$table}' 
                        AND COLUMN_NAME = '{$column}'
                    ");

                    if (empty($indexes)) {
                        $issues[] = "Missing index cho cột {$table}.{$column}";
                    }
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Lỗi khi kiểm tra indexes: " . $e->getMessage();
        }

        return $issues;
    }

    private function repairIssues(array $issues): int
    {
        $fixed = 0;

        foreach ($issues as $issue) {
            try {
                if (strpos($issue, 'bị hỏng') !== false) {
                    // Sửa chữa bảng bị hỏng
                    preg_match('/Bảng (\w+) bị hỏng/', $issue, $matches);
                    if (isset($matches[1])) {
                        DB::statement("REPAIR TABLE `{$matches[1]}`");
                        $this->line("  Đã sửa chữa bảng {$matches[1]}");
                        $fixed++;
                    }
                } elseif (strpos($issue, 'Duplicate username') !== false) {
                    // Xóa duplicate username (giữ lại record đầu tiên)
                    preg_match("/Duplicate username '(\w+)' trong bảng (\w+)/", $issue, $matches);
                    if (isset($matches[1], $matches[2])) {
                        DB::statement("
                            DELETE t1 FROM {$matches[2]} t1
                            INNER JOIN {$matches[2]} t2 
                            WHERE t1.tendangnhap = t2.tendangnhap 
                            AND t1.id > t2.id
                        ");
                        $this->line("  Đã xóa duplicate username {$matches[1]}");
                        $fixed++;
                    }
                } elseif (strpos($issue, 'Missing index') !== false) {
                    // Tạo missing index
                    preg_match("/Missing index cho cột (\w+)\.(\w+)/", $issue, $matches);
                    if (isset($matches[1], $matches[2])) {
                        $indexName = "idx_{$matches[1]}_{$matches[2]}";
                        DB::statement("CREATE INDEX `{$indexName}` ON `{$matches[1]}` (`{$matches[2]}`)");
                        $this->line("  Đã tạo index {$indexName}");
                        $fixed++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Không thể sửa chữa: {$issue} - " . $e->getMessage());
            }
        }

        return $fixed;
    }
}
