<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CauHoiKhaoSat;
use App\Models\MauKhaoSat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CauHoiController extends Controller
{
    public function store(Request $request, MauKhaoSat $mauKhaoSat)
    {
        $validated = $request->validate([
            'noidung_cauhoi' => 'required|string',
            'loai_cauhoi' => 'required|in:single_choice,multiple_choice,text,likert,rating,date,number,custom_select',
            'batbuoc' => 'boolean',
            'is_personal_info' => 'boolean',
            'page' => 'required|integer|min:1',
            'check_duplicate' => 'nullable|boolean',
            'phuong_an' => 'required_if:loai_cauhoi,single_choice,multiple_choice,likert|array|min:2',
            'phuong_an.*' => 'required|string|max:500',
            'cau_dieukien_id' => 'nullable|exists:cauhoi_khaosat,id',
            'dieukien_hienthi' => 'nullable|json',
            'data_source_id' => 'required_if:loai_cauhoi,custom_select|nullable|exists:data_sources,id',
        ]);

        DB::beginTransaction();
        try {
            $thutu = $mauKhaoSat->cauHoi()->max('thutu') + 1;

            $cauHoi = $mauKhaoSat->cauHoi()->create([
                'noidung_cauhoi' => $validated['noidung_cauhoi'],
                'loai_cauhoi' => $validated['loai_cauhoi'],
                'batbuoc' => $validated['batbuoc'] ?? true,
                'is_personal_info' => $validated['is_personal_info'] ?? false,
                'thutu' => $thutu,
                'page' => $validated['page'],
                'check_duplicate' => $validated['check_duplicate'] ?? 0,
                'trangthai' => 1,
                'cau_dieukien_id' => $validated['cau_dieukien_id'] ?? null,
                'dieukien_hienthi' => $validated['dieukien_hienthi'] ?? null,
                'data_source_id' => $validated['data_source_id'] ?? null,
            ]);

            if (isset($validated['phuong_an'])) {
                $phuongAnData = [];
                foreach ($validated['phuong_an'] as $index => $phuongAn) {
                    $phuongAnData[] = ['noidung' => $phuongAn, 'thutu' => $index + 1];
                }
                $cauHoi->phuongAnTraLoi()->createMany($phuongAnData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Thêm câu hỏi thành công!', 'cauHoi' => $cauHoi->load('phuongAnTraLoi')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function show(CauHoiKhaoSat $cauHoi)
    {
        $cauHoi->load([
            'phuongAnTraLoi' => function ($query) {
                $query->orderBy('thutu', 'asc');
            }
        ]);

        return response()->json($cauHoi);
    }

    public function update(Request $request, CauHoiKhaoSat $cauHoi)
    {
        $validated = $request->validate([
            'noidung_cauhoi' => 'sometimes|required|string',
            'loai_cauhoi' => 'sometimes|required|in:single_choice,multiple_choice,text,likert,rating,date,number,custom_select',
            'batbuoc' => 'sometimes|boolean',
            'is_personal_info' => 'sometimes|boolean',
            'page' => 'sometimes|required|integer|min:1',
            'check_duplicate' => 'nullable|boolean',
            'phuong_an' => 'sometimes|array',
            'phuong_an.*' => 'required|string|max:500',
            'cau_dieukien_id' => 'nullable|exists:cauhoi_khaosat,id',
            'dieukien_hienthi' => 'nullable|json',
            'data_source_id' => 'required_if:loai_cauhoi,custom_select|nullable|exists:data_sources,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [];

            if ($request->has('noidung_cauhoi'))
                $updateData['noidung_cauhoi'] = $validated['noidung_cauhoi'];
            if ($request->has('loai_cauhoi'))
                $updateData['loai_cauhoi'] = $validated['loai_cauhoi'];
            if ($request->has('batbuoc'))
                $updateData['batbuoc'] = $validated['batbuoc'];
            if ($request->has('is_personal_info'))
                $updateData['is_personal_info'] = $validated['is_personal_info'];
            if ($request->has('page'))
                $updateData['page'] = $validated['page'];
            if ($request->has('check_duplicate'))
                $updateData['check_duplicate'] = $validated['check_duplicate'] ?? 0;
            if ($request->has('data_source_id'))
                $updateData['data_source_id'] = $validated['data_source_id'] ?? null;

            // Always allow updating conditional logic
            if ($request->has('cau_dieukien_id'))
                $updateData['cau_dieukien_id'] = $validated['cau_dieukien_id'];
            if ($request->has('dieukien_hienthi'))
                $updateData['dieukien_hienthi'] = $validated['dieukien_hienthi'];

            $cauHoi->update($updateData);

            if ($request->has('phuong_an')) {
                $cauHoi->phuongAnTraLoi()->delete();
                if (!empty($validated['phuong_an'])) {
                    $phuongAnData = [];
                    foreach ($validated['phuong_an'] as $index => $phuongAn) {
                        $phuongAnData[] = ['noidung' => $phuongAn, 'thutu' => $index + 1];
                    }
                    $cauHoi->phuongAnTraLoi()->createMany($phuongAnData);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật câu hỏi thành công!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(CauHoiKhaoSat $cauHoi)
    {
        $dependentCount = CauHoiKhaoSat::where('cau_dieukien_id', $cauHoi->id)->count();
        if ($dependentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa. Có {$dependentCount} câu hỏi khác đang phụ thuộc vào câu hỏi này."
            ], 409); // Conflict
        }

        $cauHoi->delete();
        return response()->json(['success' => true, 'message' => 'Xóa câu hỏi thành công.']);
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:cauhoi_khaosat,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['order'] as $index => $id) {
                CauHoiKhaoSat::where('id', $id)->update(['thutu' => $index + 1]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thứ tự câu hỏi.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi cập nhật thứ tự.'
            ], 500);
        }
    }

}