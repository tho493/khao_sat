<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuKhaoSat extends Model
{
    protected $table = 'phieu_khaosat';

    protected $appends = ['device_summary', 'device_os', 'device_browser', 'device_type', 'device_app', 'device_source'];

    protected $fillable = [
        'dot_khaosat_id',
        'trangthai',
        'thoigian_batdau',
        'thoigian_hoanthanh',
        'ip_address',
        'user_agent',
        'is_duplicate',
        'token'
    ];

    /**
     * Tóm tắt thiết bị của phiếu khảo sát
     */
    public function getDeviceSummaryAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['summary'];
    }

    public function getDeviceOsAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['os'];
    }

    public function getDeviceBrowserAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['browser'];
    }

    public function getDeviceTypeAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['device'];
    }

    public function getDeviceAppAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['app'];
    }

    public function getDeviceSourceAttribute()
    {
        return \App\Helpers\UserAgentParser::parse($this->user_agent)['source'];
    }

    protected $casts = [
        'thoigian_batdau' => 'datetime',
        'thoigian_hoanthanh' => 'datetime'
    ];

    /**
     * Quan hệ với đợt khảo sát
     */
    public function dotKhaoSat()
    {
        return $this->belongsTo(DotKhaoSat::class, 'dot_khaosat_id');
    }

    /**
     * Quan hệ với chi tiết phiếu
     */
    public function chiTiet()
    {
        return $this->hasMany(PhieuKhaoSatChiTiet::class, 'phieu_khaosat_id');
    }

    /**
     * Kiểm tra phiếu đã hoàn thành
     */
    public function isCompleted()
    {
        return $this->trangthai === 'completed';
    }

    /**
     * Tính thời gian hoàn thành (phút) // Chưa hoạt động
     */
    // public function getThoiGianHoanThanhAttribute()
    // {
    //     if (
    //         !$this->thoigian_batdau || !$this->thoigian_hoanthanh ||
    //         !($this->thoigian_batdau instanceof \Carbon\Carbon) ||
    //         !($this->thoigian_hoanthanh instanceof \Carbon\Carbon)
    //     ) {
    //         return null;
    //     }

    //     return $this->thoigian_batdau->diffInMinutes($this->thoigian_hoanthanh);
    // }

    /**
     * Scope lọc theo trạng thái
     */
    public function scopeCompleted($query)
    {
        return $query->where('trangthai', 'completed');
    }

    /**
     * Scope lọc theo đợt khảo sát
     */
    public function scopeForDot($query, $dotId)
    {
        return $query->where('dot_khaosat_id', $dotId);
    }
}