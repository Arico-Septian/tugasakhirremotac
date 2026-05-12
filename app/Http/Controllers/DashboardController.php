<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomTemperature;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['acUnits.status'])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

        $onlineRooms = 0;
        $offlineRooms = 0;

        foreach ($rooms as $room) {
            $room->temperature = optional(
                $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
            )->temperature;

            $deviceId = strtolower(trim((string) $room->device_id));
            $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
            $lastSeen = $this->lastSeenFrom(Cache::get("device_{$deviceId}_last_seen"))
                ?? $this->lastSeenFrom($room->last_seen);

            $isOnline = ($status === 'online' || $status === 'available')
                && $lastSeen
                && now()->diffInSeconds($lastSeen, true) <= 60;

            $room->device_status = $isOnline ? 'online' : 'offline';

            $isOnline ? $onlineRooms++ : $offlineRooms++;
        }

        $totalRooms = $rooms->count();

        $allAcUnits = $rooms->flatMap->acUnits;
        $totalAc = $allAcUnits->count();
        $activeAc = $allAcUnits->filter(fn ($ac) => optional($ac->status)->power === 'ON')->count();
        $inactiveAc = $totalAc - $activeAc;

        return view('dashboard.dashboard', compact(
            'rooms',
            'totalRooms',
            'totalAc',
            'activeAc',
            'inactiveAc',
            'onlineRooms',
            'offlineRooms'
        ));
    }

    private function lastSeenFrom(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
