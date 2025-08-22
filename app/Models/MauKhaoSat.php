<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MauKhaoSat extends Model
{

    protected $table = 'mau_khaosat';

    protected $fillable = [
        'ten_mau',
        'mota',
        'version',
        'trangthai',
        'nguoi_tao_id'
    ];

    public function nguoiTao()
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }

    public function cauHoi()
    {
        return $this->hasMany(CauHoiKhaoSat::class, 'mau_khaosat_id')->orderBy('thutu');
    }

    public function dotKhaoSat()
    {
        return $this->hasMany(DotKhaoSat::class, 'mau_khaosat_id');
    }
}