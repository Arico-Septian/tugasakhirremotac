<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTemperature extends Model
{
    protected $fillable = ['room', 'temperature'];

    public static function normalizeRoomName($room): string
    {
        $room = strtolower(trim((string) $room));
        // rapihin spasi dobel jadi satu
        return preg_replace('/\s+/', ' ', $room) ?? $room;
    }

    public static function latestByNormalizedRoom()
    {
        // ambil id terakhir per room langsung dari database
        $latestIds = static::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('room')
            ->pluck('id');

        return static::query()
            ->whereIn('id', $latestIds)
            ->get()
            ->keyBy(fn ($t) => static::normalizeRoomName($t->room));
    }
}
