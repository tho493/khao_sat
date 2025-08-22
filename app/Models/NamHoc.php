<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NamHoc extends Model
{
    protected $table = 'namhoc';

    protected $fillable = ['namhoc', 'trangthai'];

    protected $casts = [
        'trangthai' => 'boolean'
    ];

    /**
     * Quan hệ với đợt khảo sát
     */
    public function dotKhaoSat()
    {
        return $this->hasMany(DotKhaoSat::class, 'namhoc_id');
    }

    /**
     * Scope lấy năm học active
     */
    public function scopeActive($query)
    {
        return $query->where('trangthai', 1);
    }

    /**
     * Lấy năm học hiện tại
     */
    public static function current()
    {
        $currentMonth = date('n');
        $currentYear = date('Y');

        if ($currentMonth >= 8) {
            $namhoc = $currentYear . '-' . ($currentYear + 1);
        } else {
            $namhoc = ($currentYear - 1) . '-' . $currentYear;
        }

        return self::firstOrCreate(['namhoc' => $namhoc]);
    }
}