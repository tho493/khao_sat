<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CauHinhHeThong extends Model
{
    protected $table = 'cau_hinh_dich_vu';
    public $timestamps = false;

    protected $fillable = [
        'ma_cauhinh',
        'giatri',
        'mota',
        'nhom_cauhinh'
    ];

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::saved(function ($config) {
            Cache::forget('system_configs');
        });

        static::deleted(function ($config) {
            Cache::forget('system_configs');
        });
    }
}