<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTemperature extends Model
{
    protected $fillable = ['room', 'temperature'];

    public static function normalizeRoomName($room): string
    {
        return strtolower(trim((string) $room));
    }

    public static function latestByNormalizedRoom()
    {
        return static::latest()
            ->get()
            ->unique(fn($temperature) => static::normalizeRoomName($temperature->room))
            ->keyBy(fn($temperature) => static::normalizeRoomName($temperature->room));
    }
}
