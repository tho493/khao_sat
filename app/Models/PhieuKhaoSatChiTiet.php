<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuKhaoSatChiTiet extends Model
{
    protected $table = 'phieu_khaosat_chitiet';

    protected $fillable = [
        'phieu_khaosat_id',
        'cauhoi_id',
        'phuongan_id',
        'giatri_text',
        'giatri_number',
        'giatri_date',
        'giatri_json',
        'thoigian'
    ];

    protected $casts = [
        'giatri_json' => 'array',
        'giatri_date' => 'date',
        'giatri_number' => 'decimal:2',
        'thoigian' => 'datetime'
    ];

    /**
     * Quan hệ với phiếu khảo sát
     */
    public function phieuKhaoSat()
    {
        return $this->belongsTo(PhieuKhaoSat::class, 'phieu_khaosat_id');
    }

    /**
     * Quan hệ với câu hỏi
     */
    public function cauHoi()
    {
        return $this->belongsTo(CauHoiKhaoSat::class, 'cauhoi_id');
    }

    /**
     * Quan hệ với phương án trả lời
     */
    public function phuongAn()
    {
        return $this->belongsTo(PhuongAnTraLoi::class, 'phuongan_id');
    }

    /**
     * Lấy giá trị trả lời
     */
    public function getGiaTriAttribute()
    {
        if ($this->phuongan_id) {
            return $this->phuongAn->noidung ?? '';
        }

        if ($this->giatri_text) {
            return $this->giatri_text;
        }

        if ($this->giatri_number !== null) {
            return $this->giatri_number;
        }

        if ($this->giatri_date && $this->giatri_date instanceof \Carbon\Carbon) {
            return $this->giatri_date->format('d/m/Y');
        }

        if ($this->giatri_json) {
            return implode(', ', $this->giatri_json);
        }

        return '';
    }
}