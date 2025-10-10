<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauHoiKhaoSat extends Model
{
    protected $table = 'cauhoi_khaosat';

    protected $fillable = [
        'mau_khaosat_id',
        'noidung_cauhoi',
        'loai_cauhoi',
        'batbuoc',
        'is_personal_info',
        'thutu',
        'page',
        'cau_dieukien_id',
        'dieukien_hienthi',
        'trangthai'
    ];

    protected $casts = [
        'dieukien_hienthi' => 'array',
        'batbuoc' => 'boolean',
        'is_personal_info' => 'boolean',
        'trangthai' => 'boolean'
    ];

    public function mauKhaoSat()
    {
        return $this->belongsTo(MauKhaoSat::class, 'mau_khaosat_id');
    }

    public function phuongAnTraLoi()
    {
        return $this->hasMany(PhuongAnTraLoi::class, 'cauhoi_id')->orderBy('thutu');
    }


}