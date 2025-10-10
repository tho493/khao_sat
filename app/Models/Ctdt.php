<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctdt extends Model
{
    use HasFactory;

    protected $table = 'ctdt';
    protected $primaryKey = 'mactdt';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'mactdt',
        'tenctdt',
    ];

}