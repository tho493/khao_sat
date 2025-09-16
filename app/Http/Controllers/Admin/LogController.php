<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LichSuThayDoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = LichSuThayDoi::with('nguoiThucHien');

        if ($request->filled('bang_thaydoi')) {
            $query->where('bang_thaydoi', $request->bang_thaydoi);
        }

        if ($request->filled('hanhdong')) {
            $query->where('hanhdong', $request->hanhdong);
        }

        if ($request->filled('nguoi_thuchien_id')) {
            $query->where('nguoi_thuchien_id', $request->nguoi_thuchien_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('thoigian', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('thoigian', '<=', $request->to_date);
        }

        $logs = $query->orderBy('thoigian', 'desc')->paginate(20);

        $tables = LichSuThayDoi::distinct()->pluck('bang_thaydoi');
        $actions = LichSuThayDoi::distinct()->pluck('hanhdong');
        $users = DB::table('taikhoan')->select('id', 'hoten')->get();

        return view('admin.logs.index', compact('logs', 'tables', 'actions', 'users'));
    }

    public function userLogs(Request $request)
    {
        $userId = auth()->user()->id;

        $logs = LichSuThayDoi::where('nguoi_thuchien_id', $userId)
            ->orderBy('thoigian', 'desc')
            ->paginate(20);

        $stats = [
            'total' => LichSuThayDoi::where('nguoi_thuchien_id', $userId)->count(),
            'create' => LichSuThayDoi::where('nguoi_thuchien_id', $userId)->where('hanhdong', 'create')->count(),
            'update' => LichSuThayDoi::where('nguoi_thuchien_id', $userId)->where('hanhdong', 'update')->count(),
            'delete' => LichSuThayDoi::where('nguoi_thuchien_id', $userId)->where('hanhdong', 'delete')->count(),
        ];

        return view('admin.logs.user', compact('logs', 'stats'));
    }

    public function download(Request $request)
    {
        $file = $request->get('file');

        if (!$file || !preg_match('/^[\w\-\.]+\.log$/', $file)) {
            abort(404, 'Invalid file name');
        }

        $path = storage_path('logs/' . $file);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return Response::download($path);
    }

    public function show($id)
    {
        $log = LichSuThayDoi::with('nguoiThucHien')->find($id);

        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        return response()->json([
            'id' => $log->id,
            'thoigian' => $log->thoigian ? $log->thoigian->format('d/m/Y H:i:s') : 'N/A',
            'nguoi_thuchien' => $log->nguoiThucHien->hoten ?? null,
            'hanhdong' => $log->hanhdong,
            'bang_thaydoi' => $log->bang_thaydoi,
            'id_banghi' => $log->id_banghi,
            'ghi_chu' => $log->ghi_chu,
            'noidung_cu' => $log->noidung_cu,
            'noidung_moi' => $log->noidung_moi
        ]);
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:activity,system',
            'before_date' => 'required|date|before:today'
        ]);

        if ($validated['type'] == 'activity') {
            $deleted = LichSuThayDoi::where('thoigian', '>=', $validated['before_date'] . ' 00:00:00')->delete();

            return back()->with('success', "Đã xóa {$deleted} bản ghi log hoạt động");
        } else {
            $logFiles = Storage::disk('logs')->files();
            $deleted = 0;

            foreach ($logFiles as $file) {
                $lastModified = Storage::disk('logs')->lastModified($file);
                if ($lastModified < strtotime($validated['before_date'])) {
                    Storage::disk('logs')->delete($file);
                    $deleted++;
                }
            }

            return back()->with('success', "Đã xóa {$deleted} file log hệ thống");
        }
    }

}