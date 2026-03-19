<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
        'user_id',
        'room',
        'ac',
        'activity'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
