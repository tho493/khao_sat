<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    use HasFactory;

    protected $table = 'data_sources';

    protected $fillable = ['name', 'slug'];

    public function values()
    {
        return $this->hasMany(DataSourceValue::class);
    }

    public function questions()
    {
        return $this->hasMany(CauHoiKhaoSat::class);
    }
}
