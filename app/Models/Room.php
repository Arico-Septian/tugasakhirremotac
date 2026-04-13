<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'device_id'
    ];

    public function acUnits()
    {
        return $this->hasMany(AcUnit::class);
    }

    public function temperatureData()
    {
        return $this->hasOne(\App\Models\RoomTemperature::class, 'room', 'name')
            ->latestOfMany();
    }
}
