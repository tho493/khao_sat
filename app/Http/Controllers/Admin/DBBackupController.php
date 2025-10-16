<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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

        return view('admin.db-backups.index', ['files' => $files]);
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
        ]);

        $file = basename($request->string('file')->toString());
        $force = $request->boolean('force');

        // Bật/tắt maintenance tuỳ môi trường của bạn
        // Artisan::call('down');

        Artisan::call('restore:db', [
            'file' => $file,
            '--force' => $force,
        ]);

        // Artisan::call('up');

        return back()->with('status', "Đã khôi phục DB từ {$file}");
    }
}