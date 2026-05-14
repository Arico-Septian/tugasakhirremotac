<?php

namespace App\Models;

use App\Events\UserLogCreated;
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
        return $this->belongsTo(\App\Models\User::class)->withDefault(function ($user, $log) {
            $user->name = $log->user_id === null ? 'System' : 'Deleted User';
        });
    }

    protected static function booted(): void
    {
        static::created(function (UserLog $log) {
            event(new UserLogCreated($log));
        });
    }
}
