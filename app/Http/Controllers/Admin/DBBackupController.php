<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DBBackupController extends Controller
{
    private string $dir = 'backup/db';

    public function index()
    {
        $dirPath = storage_path("app/{$this->dir}");
        $files = collect();

        if (is_dir($dirPath)) {
            $files = collect(scandir($dirPath))
                ->filter(fn($file) => $file !== '.' && $file !== '..')
                ->filter(fn($file) => str_ends_with(strtolower($file), '.sql') || str_ends_with(strtolower($file), '.sql.gz'))
                ->map(fn($file) => [
                    'name' => $file,
                    'size' => filesize("{$dirPath}/{$file}"),
                    'time' => filemtime("{$dirPath}/{$file}"),
                ])
                ->sortByDesc('time')
                ->values();
        }

        return view('admin.db-backups.index', [
            'files' => $files,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'gzip' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        $args = [];
        if ($request->boolean('gzip'))
            $args['--gzip'] = true;
        if ($request->filled('name'))
            $args['--name'] = $request->string('name')->toString();

        Artisan::call('backup:db', $args);
        return back()->with('status', 'Đã tạo bản backup DB.');
    }

    public function download(string $file)
    {
        $file = basename($file);
        $path = storage_path("app/{$this->dir}/{$file}");
        abort_unless(file_exists($path), 404);

        return Response::download($path, $file);
    }

    public function destroy(string $file)
    {
        $file = basename($file);
        $path = storage_path("app/{$this->dir}/{$file}");
        abort_unless(file_exists($path), 404);

        unlink($path);
        return back()->with('status', 'Đã xóa bản backup.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
            'force' => 'nullable|boolean',
            'repair' => 'nullable|boolean',
        ]);

        $file = basename($request->string('file')->toString());
        $force = $request->boolean('force');
        $repair = true; // Luôn repair sau restore

        // Bật/tắt maintenance tuỳ môi trường của bạn
        // Artisan::call('down');

        $result = Artisan::call('restore:db', [
            'file' => $file,
            '--force' => $force,
        ]);

        if ($result === 0 && $repair) {
            // Tự động repair database sau khi restore thành công
            Artisan::call('db:repair');
        }

        // Artisan::call('up');

        if ($result === 0) {
            return back()->with('status', "Đã khôi phục DB từ {$file} và sửa chữa các vấn đề");
        } else {
            return back()->with('error', "Khôi phục DB thất bại từ {$file}");
        }
    }

    public function upload(Request $request)
    {
        // Validation thủ công
        if (!$request->hasFile('backup')) {
            return back()->withErrors(['backup' => 'Vui lòng chọn file backup']);
        }

        $file = $request->file('backup');
        if (!$file->isValid()) {
            return back()->withErrors(['backup' => 'File upload không hợp lệ']);
        }

        $original = $file->getClientOriginalName();
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        // Chỉ chấp nhận .sql hoặc .sql.gz
        $isGz = false;
        if ($ext === 'gz' && Str::endsWith(strtolower($original), '.sql.gz')) {
            $isGz = true;
        } elseif ($ext !== 'sql') {
            return back()->withErrors(['backup' => 'Chỉ chấp nhận file .sql hoặc .sql.gz']);
        }

        // Lưu file vào thư mục backup của app với tên chuẩn
        $safeName = 'uploaded_' . now()->format('Ymd_His') . ($isGz ? '.sql.gz' : '.sql');
        $destPath = storage_path('app/' . $this->dir . '/' . $safeName);
        if (!is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }
        $file->move(dirname($destPath), basename($destPath));

        $doRestore = $request->boolean('restore');
        $force = $request->boolean('force');
        $repair = true; // Luôn repair sau restore

        if ($doRestore) {
            $result = Artisan::call('restore:db', [
                'file' => $safeName,
                '--force' => $force,
            ]);

            if ($result === 0 && $repair) {
                Artisan::call('db:repair');
            }

            if ($result === 0) {
                return back()->with('status', "Đã tải lên và khôi phục DB từ {$original} và sửa chữa các vấn đề");
            }

            return back()->with('error', 'Đã tải lên nhưng khôi phục thất bại. Vui lòng kiểm tra file trong danh sách để restore thủ công.');
        }

        return back()->with('status', "Đã tải lên file backup: {$safeName}");
    }

    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'nullable|numeric|min:1|max:365',
            'confirm' => 'required|boolean',
        ]);

        $days = $request->integer('days', 30);
        $confirm = $request->boolean('confirm');

        if (!$confirm) {
            return back()->with('error', 'Vui lòng xác nhận để tiếp tục xóa backup cũ.');
        }

        $dirPath = storage_path("app/{$this->dir}");

        if (!is_dir($dirPath)) {
            return back()->with('error', 'Thư mục backup không tồn tại.');
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
            return back()->with('status', "Không có file backup nào cũ hơn {$days} ngày để xóa.");
        }

        $deletedCount = 0;
        $deletedSize = 0;
        $errors = [];

        foreach ($files as $file) {
            try {
                if (unlink($file['path'])) {
                    $deletedCount++;
                    $deletedSize += $file['size'];
                } else {
                    $errors[] = "Không thể xóa: {$file['name']}";
                }
            } catch (\Exception $e) {
                $errors[] = "Lỗi khi xóa {$file['name']}: " . $e->getMessage();
            }
        }

        if ($deletedCount > 0) {
            $deletedSizeMB = number_format($deletedSize / (1024 * 1024), 2);
            $message = "Đã xóa thành công {$deletedCount} file backup cũ hơn {$days} ngày, giải phóng {$deletedSizeMB} MB.";

            if (!empty($errors)) {
                $message .= " Tuy nhiên có " . count($errors) . " lỗi xảy ra.";
            }

            return back()->with('status', $message);
        }

        return back()->with('error', 'Có lỗi xảy ra khi xóa backup cũ. ' . implode(' ', $errors));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|string',
        ]);

        $files = $request->input('files', []);
        $deletedCount = 0;
        $errors = [];

        foreach ($files as $file) {
            $file = basename($file);
            $path = storage_path("app/{$this->dir}/{$file}");

            if (file_exists($path)) {
                try {
                    if (unlink($path)) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa: {$file}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Lỗi khi xóa {$file}: " . $e->getMessage();
                }
            } else {
                $errors[] = "File không tồn tại: {$file}";
            }
        }

        if ($deletedCount > 0) {
            $message = "Đã xóa thành công {$deletedCount} file backup.";
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi xảy ra.";
            }
            return back()->with('status', $message);
        }

        return back()->with('error', 'Không có file nào được xóa. ' . implode(' ', $errors));
    }
}