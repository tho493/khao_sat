<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DotKhaoSat;
use App\Models\MauKhaoSat;
use App\Models\NamHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DotKhaoSatController extends Controller
{
    public function index(Request $request)
    {
        $query = DotKhaoSat::with(['mauKhaoSat', 'namHoc'])
            ->withCount([
                'phieuKhaoSat',
                'phieuKhaoSat as phieu_hoan_thanh' => function ($q) {
                    $q->where('trangthai', 'completed');
                }
            ]);

        // Filters
        if ($request->filled('trangthai')) {
            $query->where('trangthai', $request->trangthai);
        }

        if ($request->filled('namhoc_id')) {
            $query->where('namhoc_id', $request->namhoc_id);
        }

        if ($request->filled('search')) {
            $query->where('ten_dot', 'like', '%' . $request->search . '%');
        }

        $dotKhaoSats = $query->orderBy('created_at', 'desc')->paginate(10);
        $namHocs = NamHoc::orderBy('namhoc', 'desc')->get();

        return view('admin.dot-khao-sat.index', compact('dotKhaoSats', 'namHocs'));
    }

    public function create()
    {
        $mauKhaoSats = MauKhaoSat::where('trangthai', 'active')
            ->get();
        $namHocs = NamHoc::where('trangthai', 1)->orderBy('namhoc', 'desc')->get();

        return view('admin.dot-khao-sat.create', compact('mauKhaoSats', 'namHocs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_dot' => 'required|max:255',
            'mau_khaosat_id' => 'required|exists:mau_khaosat,id',
            'namhoc_id' => 'required|exists:namhoc,id',
            'tungay' => 'required|date',
            'denngay' => 'required|date|after:tungay',
            'mota' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ], [
            'ten_dot.required' => 'Vui lòng nhập tên đợt khảo sát',
            'mau_khaosat_id.required' => 'Vui lòng chọn mẫu khảo sát',
            'namhoc_id.required' => 'Vui lòng chọn năm học',
            'tungay.required' => 'Vui lòng chọn ngày bắt đầu',
            'denngay.required' => 'Vui lòng chọn ngày kết thúc',
            'denngay.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'image.required' => 'Vui lòng kiểm tra ảnh của bạn'
        ]);

        DB::beginTransaction();
        try {
            $dataToCreate = [
                'ten_dot' => $validated['ten_dot'],
                'mau_khaosat_id' => $validated['mau_khaosat_id'],
                'namhoc_id' => $validated['namhoc_id'],
                'tungay' => $validated['tungay'],
                'denngay' => $validated['denngay'],
                'mota' => $validated['mota'],
                'trangthai' => 'draft',
                'nguoi_tao_id' => Auth::user()->id
            ];

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('survey_images', 'public');
                $dataToCreate['image_url'] = $path;
            }

            $dotKhaoSat = DotKhaoSat::create($dataToCreate);

            DB::commit();

            return redirect()
                ->route('admin.dot-khao-sat.show', $dotKhaoSat)
                ->with('success', 'Tạo đợt khảo sát thành công');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(DotKhaoSat $dotKhaoSat)
    {
        $dotKhaoSat->load(['mauKhaoSat', 'namHoc']);

        // Thống kê
        $thongKe = [
            'tong_phieu' => $dotKhaoSat->phieuKhaoSat()->count(),
            'phieu_hoan_thanh' => $dotKhaoSat->phieuKhaoSat()->where('trangthai', 'completed')->count(),
        ];

        // Thống kê theo đơn vị (nếu có metadata)
        $thongKeTheoDonVi = [];
        try {
            $thongKeTheoDonVi = DB::table('phieu_khaosat')
                ->where('dot_khaosat_id', $dotKhaoSat->id)
                ->selectRaw("
                    JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.donvi')) as donvi,
                    COUNT(*) as tong_phieu,
                    SUM(CASE WHEN trangthai = 'completed' THEN 1 ELSE 0 END) as phieu_hoanthanh,
                    ROUND(SUM(CASE WHEN trangthai = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as ty_le
                ")
                ->groupBy('donvi')
                ->get();
        } catch (\Exception $e) {
            // Nếu lỗi JSON function thì bỏ qua
        }

        return view('admin.dot-khao-sat.show', compact('dotKhaoSat', 'thongKe', 'thongKeTheoDonVi'));
    }

    public function activate(DotKhaoSat $dotKhaoSat)
    {
        if ($dotKhaoSat->trangthai !== 'draft') {
            return back()->with('error', 'Chỉ có thể kích hoạt đợt khảo sát ở trạng thái nháp');
        }

        $dotKhaoSat->update(['trangthai' => 'active']);

        return back()->with('success', 'Kích hoạt đợt khảo sát thành công');
    }

    public function close(DotKhaoSat $dotKhaoSat)
    {
        $dotKhaoSat->update(['trangthai' => 'closed']);

        return back()->with('success', 'Đóng đợt khảo sát thành công');
    }

    public function edit(DotKhaoSat $dotKhaoSat)
    {
        // Chỉ cho phép sửa khi ở trạng thái draft
        if ($dotKhaoSat->trangthai !== 'draft') {
            return redirect()->route('admin.dot-khao-sat.show', $dotKhaoSat)
                ->with('error', 'Không thể sửa đợt khảo sát đã kích hoạt');
        }

        $mauKhaoSats = MauKhaoSat::where('trangthai', 'active')->get();
        $namHocs = NamHoc::where('trangthai', 1)->orderBy('namhoc', 'desc')->get();

        return view('admin.dot-khao-sat.edit', compact('dotKhaoSat', 'mauKhaoSats', 'namHocs'));
    }

    public function update(Request $request, DotKhaoSat $dotKhaoSat)
    {
        // Chỉ cho phép sửa khi ở trạng thái draft
        if ($dotKhaoSat->trangthai !== 'draft') {
            return back()->with('error', 'Không thể sửa đợt khảo sát đã kích hoạt');
        }

        $validated = $request->validate(
            [
                'ten_dot' => 'required|max:255',
                'mau_khaosat_id' => 'required|exists:mau_khaosat,id',
                'namhoc_id' => 'required|exists:namhoc,id',
                'tungay' => 'required|date',
                'denngay' => 'required|date|after:tungay',
                'mota' => 'nullable',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]
        );

        DB::beginTransaction();
        try {
            $dataToUpdate = [
                'ten_dot' => $validated['ten_dot'],
                'mau_khaosat_id' => $validated['mau_khaosat_id'],
                'namhoc_id' => $validated['namhoc_id'],
                'tungay' => $validated['tungay'],
                'denngay' => $validated['denngay'],
                'mota' => $validated['mota'],
                'trangthai' => 'draft'
            ];

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($dotKhaoSat->image_url && Storage::disk('public')->exists($dotKhaoSat->image_url)) {
                    Storage::disk('public')->delete($dotKhaoSat->image_url);
                }

                // Lưu ảnh mới
                $path = $request->file('image')->store('survey_images', 'public');
                $dataToUpdate['image_url'] = $path;
            }

            $dotKhaoSat->update($dataToUpdate);
            DB::commit();

            return redirect()
                ->route('admin.dot-khao-sat.show', $dotKhaoSat)
                ->with('success', 'Cập nhật đợt khảo sát thành công.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(DotKhaoSat $dotKhaoSat)
    {
        // Chỉ cho phép xóa khi ở trạng thái draft và chưa có phiếu khảo sát
        if ($dotKhaoSat->trangthai !== 'draft') {
            return back()->with('error', 'Chỉ có thể xóa đợt khảo sát ở trạng thái nháp');
        }

        if ($dotKhaoSat->phieuKhaoSat()->count() > 0) {
            return back()->with('error', 'Không thể xóa đợt khảo sát đã có phiếu trả lời');
        }

        $dotKhaoSat->delete();

        return redirect()->route('admin.dot-khao-sat.index')
            ->with('success', 'Xóa đợt khảo sát thành công');
    }
}