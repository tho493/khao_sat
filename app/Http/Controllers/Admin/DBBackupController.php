<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class DbBackupController extends Controller
{
    private string $dir = 'backups/db';

    public function index()
    {
        $files = collect(Storage::files($this->dir))
            ->filter(fn($p) => str_ends_with(strtolower($p), '.sql') || str_ends_with(strtolower($p), '.sql.gz'))
            ->map(fn($p) => [
                'name' => basename($p),
                'size' => Storage::size($p),
                'time' => Storage::lastModified($p),
            ])
            ->sortByDesc('time')
            ->values();

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
        $path = "{$this->dir}/{$file}";
        abort_unless(Storage::exists($path), 404);

        return Response::download(Storage::path($path), $file);
    }

    public function destroy(string $file)
    {
        $file = basename($file);
        $path = "{$this->dir}/{$file}";
        abort_unless(Storage::exists($path), 404);

        Storage::delete($path);
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
