<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ctdt;
use Illuminate\Http\Request;

class CtdtController extends Controller
{
    public function index(Request $request)
    {
        $query = Ctdt::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('mactdt', 'like', "%{$search}%")
                ->orWhere('tenctdt', 'like', "%{$search}%");
        }

        $ctdts = $query->orderBy('tenctdt', 'asc')->paginate(15);
        return view('admin.ctdt.index', compact('ctdts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mactdt' => 'required|string|max:10|unique:ctdt,mactdt',
            'tenctdt' => 'required|string|max:255',
        ], [
            'mactdt.unique' => 'Mã CTĐT này đã tồn tại.',
        ]);

        Ctdt::create($validated);
        return back()->with('success', 'Thêm Chương trình đào tạo thành công.');
    }

    public function update(Request $request, $mactdt)
    {
        $ctdt = Ctdt::findOrFail($mactdt);

        $validated = $request->validate([
            'tenctdt' => 'required|string|max:255',
        ]);

        $ctdt->update($validated);
        return redirect()->route('admin.ctdt.index')->with('success', 'Cập nhật Chương trình đào tạo thành công.');
    }

    public function destroy($mactdt)
    {
        $ctdt = Ctdt::findOrFail($mactdt);

        // TODO: Thêm kiểm tra ràng buộc ở đây nếu CTĐT có liên kết với các bảng khác
        // Ví dụ:
        // if ($ctdt->mauKhaoSat()->count() > 0) {
        //     return back()->with('error', 'Không thể xóa. CTĐT này đang được sử dụng.');
        // }

        $ctdt->delete();
        return redirect()->route('admin.ctdt.index')->with('success', 'Xóa Chương trình đào tạo thành công.');
    }
}