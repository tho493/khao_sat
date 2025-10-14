<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhieuKhaoSat;

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

        return response()->json($phieuKhaoSat);
    }
}