<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuThayDoi extends Model
{
    protected $table = 'lichsu_thaydoi';

    protected $fillable = [
        'bang_thaydoi',
        'id_banghi',
        'nguoi_thuchien_id',
        'hanhdong',
        'noidung_cu',
        'noidung_moi',
        'ghi_chu',
        'thoigian'
    ];

    protected $casts = [
        'noidung_cu' => 'array',
        'noidung_moi' => 'array',
        'thoigian' => 'datetime'
    ];

    public $timestamps = false;

    /**
     * Quan hệ với người thực hiện
     */
    public function nguoiThucHien()
    {
        return $this->belongsTo(User::class, 'nguoi_thuchien_id');
    }

    /**
     * Tạo log
     */
    public static function log($table, $recordId, $action, $oldData = null, $newData = null, $note = null)
    {
        return self::create([
            'bang_thaydoi' => $table,
            'id_banghi' => $recordId,
            'nguoi_thuchien_id' => auth()->id(),
            'hanhdong' => $action,
            'noidung_cu' => $oldData,
            'noidung_moi' => $newData,
            'ghi_chu' => $note,
            'thoigian' => now()
        ]);
    }
}