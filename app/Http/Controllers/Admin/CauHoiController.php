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
            'loai_cauhoi' => 'required|in:single_choice,multiple_choice,text,likert,rating,date,number',
            'batbuoc' => 'boolean',
            'thutu' => 'integer|min:0',
            // Yêu cầu mảng phuong_an nếu loại câu hỏi là lựa chọn
            'phuong_an' => 'required_if:loai_cauhoi,single_choice,multiple_choice,likert|array|min:2',
            'phuong_an.*' => 'required|string|max:500' // Mỗi phương án không quá 500 ký tự
        ], [
            'phuong_an.required_if' => 'Vui lòng cung cấp các phương án trả lời.',
            'phuong_an.min' => 'Phải có ít nhất 2 phương án trả lời.'
        ]);

        DB::beginTransaction();
        try {
            // Lấy thứ tự lớn nhất hiện tại và cộng thêm 1
            $thutu = $mauKhaoSat->cauHoi()->max('thutu') + 1;

            // Tạo câu hỏi
            $cauHoi = $mauKhaoSat->cauHoi()->create([
                'noidung_cauhoi' => $validated['noidung_cauhoi'],
                'loai_cauhoi' => $validated['loai_cauhoi'],
                'batbuoc' => $validated['batbuoc'] ?? true,
                'thutu' => $validated['thutu'] > 0 ? $validated['thutu'] : $thutu
            ]);

            // Tạo các phương án trả lời nếu có
            if (isset($validated['phuong_an'])) {
                $phuongAnData = [];
                foreach ($validated['phuong_an'] as $index => $phuongAn) {
                    $phuongAnData[] = [
                        'noidung' => $phuongAn,
                        'giatri' => $index + 1, // Giá trị có thể dùng để phân tích
                        'thutu' => $index + 1
                    ];
                }
                $cauHoi->phuongAnTraLoi()->createMany($phuongAnData);
            }

            DB::commit();

            // Trả về JSON để JavaScript xử lý
            return response()->json([
                'success' => true,
                'message' => 'Thêm câu hỏi thành công!',
                'cauHoi' => $cauHoi->load('phuongAnTraLoi') // Gửi lại dữ liệu câu hỏi vừa tạo
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
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
            'noidung_cauhoi' => 'required|string',
            'loai_cauhoi' => 'required|in:single_choice,multiple_choice,text,likert,rating,date,number',
            'batbuoc' => 'boolean',
            'thutu' => 'integer|min:0',
            'phuong_an' => 'sometimes|array|min:2',
            'phuong_an.*' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật thông tin chính của câu hỏi
            $cauHoi->update([
                'noidung_cauhoi' => $validated['noidung_cauhoi'],
                'loai_cauhoi' => $validated['loai_cauhoi'],
                'batbuoc' => $validated['batbuoc'] ?? true,
                'thutu' => $validated['thutu']
            ]);

            // Xóa các phương án cũ và tạo lại nếu có phương án mới
            if (isset($validated['phuong_an'])) {
                $cauHoi->phuongAnTraLoi()->delete(); // Xóa hết phương án cũ

                $phuongAnData = [];
                foreach ($validated['phuong_an'] as $index => $phuongAn) {
                    $phuongAnData[] = [
                        'noidung' => $phuongAn,
                        'giatri' => $index + 1,
                        'thutu' => $index + 1
                    ];
                }
                $cauHoi->phuongAnTraLoi()->createMany($phuongAnData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật câu hỏi thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(CauHoiKhaoSat $cauHoi)
    {
        $cauHoi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa câu hỏi thành công'
        ]);
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