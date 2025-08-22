<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'taikhoan';

    protected $fillable = [
        'id',
        'tendangnhap',
        'matkhau',
        'hoten'
    ];

    protected $hidden = ['matkhau'];

    public $timestamps = false;

    public function getAuthPassword()
    {
        return $this->matkhau;
    }

    // public function getAuthIdentifierName()
    // {
    //     return 'tendangnhap';
    // }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    // public function getAuthIdentifier()
    // {
    //     return 'id';
    // }
}