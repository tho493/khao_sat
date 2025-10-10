<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NamHoc;
use Illuminate\Http\Request;

class NamHocController extends Controller
{
    public function index(Request $request)
    {
        $query = NamHoc::withCount('dotKhaoSat');

        if ($request->filled('search')) {
            $query->where('namhoc', 'like', '%' . $request->search . '%');
        }

        $namHocs = $query->orderBy('namhoc', 'desc')->paginate(10);
        return view('admin.nam-hoc.index', compact('namHocs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Rule regex để đảm bảo định dạng "YYYY-YYYY"
            'namhoc' => 'required|string|unique:namhoc,namhoc|regex:/^\d{4}-\d{4}$/',
            'trangthai' => 'boolean',
        ], [
            'namhoc.unique' => 'Năm học này đã tồn tại.',
            'namhoc.regex' => 'Định dạng năm học không hợp lệ. Ví dụ đúng: 2024-2025.',
        ]);

        $validated['trangthai'] = $request->has('trangthai');
        NamHoc::create($validated);

        return back()->with('success', 'Thêm năm học thành công.');
    }

    public function update(Request $request, NamHoc $namHoc)
    {
        $validated = $request->validate([
            'namhoc' => 'required|string|unique:namhoc,namhoc,' . $namHoc->id . '|regex:/^\d{4}-\d{4}$/',
            'trangthai' => 'boolean',
        ], [
            'namhoc.unique' => 'Năm học này đã tồn tại.',
            'namhoc.regex' => 'Định dạng năm học không hợp lệ.',
        ]);

        $validated['trangthai'] = $request->has('trangthai');
        $namHoc->update($validated);

        return redirect()->route('admin.nam-hoc.index')->with('success', 'Cập nhật năm học thành công.');
    }

    public function destroy(NamHoc $namHoc)
    {
        // Kiểm tra xem năm học có đang được sử dụng không
        if ($namHoc->dotKhaoSat()->count() > 0) {
            return back()->with('error', 'Không thể xóa. Năm học này đang được sử dụng bởi các đợt khảo sát.');
        }

        $namHoc->delete();
        return redirect()->route('admin.nam-hoc.index')->with('success', 'Xóa năm học thành công.');
    }
}