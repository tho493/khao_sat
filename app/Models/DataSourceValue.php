<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSourceValue extends Model
{
    use HasFactory;

    protected $table = 'data_source_values';

    protected $fillable = ['data_source_id', 'value', 'label'];

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }
}
