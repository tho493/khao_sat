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

        // Thay thế mã thành tên đối với các câu hỏi custom_select dùng DataSource
        $customSelectQuestions = optional($phieuKhaoSat->dotKhaoSat->mauKhaoSat->cauHoi)
            ->where('loai_cauhoi', 'custom_select')
            ->whereNotNull('data_source_id');

        if ($customSelectQuestions && $customSelectQuestions->isNotEmpty()) {
            $dataSourceIds = $customSelectQuestions->pluck('data_source_id')->unique()->all();
            $sourceValues = \App\Models\DataSourceValue::whereIn('data_source_id', $dataSourceIds)
                ->get()
                ->groupBy('data_source_id');

            foreach ($customSelectQuestions as $question) {
                $valuesMap = isset($sourceValues[$question->data_source_id])
                    ? $sourceValues[$question->data_source_id]->pluck('label', 'value')->all()
                    : [];

                if (!empty($valuesMap)) {
                    foreach ($phieuKhaoSat->chiTiet as $chiTiet) {
                        if ($chiTiet->cauhoi_id === $question->id) {
                            $ma = $chiTiet->giatri_text ?? $chiTiet->giatri_number ?? null;
                            if ($ma !== null && isset($valuesMap[$ma])) {
                                $chiTiet->giatri_text = $valuesMap[$ma];
                            }
                        }
                    }
                }
            }
        }

        return response()->json($phieuKhaoSat);
    }
}