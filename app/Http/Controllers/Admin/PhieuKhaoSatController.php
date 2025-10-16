<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhieuKhaoSat;
use App\Models\Ctdt;

class PhieuKhaoSatController extends Controller
{
    /**
     * Trả về dữ liệu chi tiết của một phiếu khảo sát dưới dạng JSON.
     */
    public function showJson(PhieuKhaoSat $phieuKhaoSat)
    {
        $phieuKhaoSat->load([
            'dotKhaoSat.mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('is_personal_info', 'desc')
                    ->orderBy('page', 'asc')
                    ->orderBy('thutu', 'asc');
            },
            'chiTiet.phuongAn'
        ]);

        // thay mã của ctdt thành tên ctdt
        $ctdtQuestion = optional($phieuKhaoSat->dotKhaoSat->mauKhaoSat->cauHoi)
            ->where('loai_cauhoi', 'select_ctdt')
            ->first();

        if ($ctdtQuestion) {
            foreach ($phieuKhaoSat->chiTiet as $chiTiet) {
                if ($chiTiet->cauhoi_id === $ctdtQuestion->id) {
                    $ma = $chiTiet->giatri_text ?? $chiTiet->giatri_number ?? null;
                    if ($ma) {
                        $tenCtdt = Ctdt::where('mactdt', $ma)->value('tenctdt');
                        if ($tenCtdt) {
                            $chiTiet->giatri_text = $tenCtdt;
                        }
                    }
                }
            }
        }

        return response()->json($phieuKhaoSat);
    }
}