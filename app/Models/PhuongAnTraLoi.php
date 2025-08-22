<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhuongAnTraLoi extends Model
{

    protected $table = 'phuongan_traloi';

    protected $fillable = [
        'cauhoi_id',
        'noidung',
        'giatri',
        'thutu',
        'cho_nhap_khac'
    ];

    protected $casts = [
        'cho_nhap_khac' => 'boolean'
    ];

    /**
     * Quan hệ với câu hỏi
     */
    public function cauHoi()
    {
        return $this->belongsTo(CauHoiKhaoSat::class, 'cauhoi_id');
    }

    /**
     * Quan hệ với chi tiết phiếu khảo sát
     */
    public function chiTietPhieu()
    {
        return $this->hasMany(PhieuKhaoSatChiTiet::class, 'phuongan_id');
    }

    /**
     * Scope sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('thutu');
    }

    /**
     * Đếm số lượt chọn
     */
    public function getSoLuotChonAttribute()
    {
        return $this->chiTietPhieu()->count();
    }
}