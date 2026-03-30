<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcStatus extends Model
{
    protected $fillable = [
        'ac_unit_id',
        'power',
        'set_temperature',
        'mode'
    ];

    public function acUnit()
    {
        return $this->belongsTo(\App\Models\AcUnit::class);
    }
}
