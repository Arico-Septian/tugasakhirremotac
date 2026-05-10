<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Room;
use App\Models\RoomTemperature;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with(['acUnits.status'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

        foreach ($rooms as $room) {
            $deviceId = strtolower(trim((string) $room->device_id));

            $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
            $lastSeen = $this->lastSeenFrom(Cache::get("device_{$deviceId}_last_seen"))
                ?? $this->lastSeenFrom($room->last_seen);

            $isOnline = ($status === 'online' || $status === 'available') && $lastSeen && now()->diffInSeconds($lastSeen, true) <= 30;
            $room->device_status = $isOnline ? 'online' : 'offline';

            $room->temperature = optional(
                $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
            )->temperature;
        }

        $roomsByFloor = $rooms->groupBy(fn ($room) => $room->floor ?: 'Lainnya');

        return view('rooms.index', compact('rooms', 'roomsByFloor'));
    }

    /* === CREATE ROOM === */
    public function store(Request $request)
    {
        $request->merge([
            'name' => strtolower(trim((string) $request->name)),
            'device_id' => strtolower(trim((string) $request->device_id)),
        ]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rooms', 'name'),
            ],
            'device_id' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('rooms', 'device_id'),
            ],
        ], [
            'device_id.regex' => 'ESP ID hanya boleh berisi huruf kecil, angka, underscore, dan strip.',
        ]);

        $deviceId = $request->device_id;

        $room = Room::create([
            'name' => $request->name,
            'device_id' => $deviceId,
            'floor' => $request->filled('floor') ? trim($request->floor) : null,
        ]);

        $mqttPublished = true;

        try {
            $mqtt = new MqttService;
            $topic = "device/{$deviceId}/config";

            $mqtt->publish(
                $topic,
                json_encode([
                    'room' => $room->name,
                ]),
                1,
                true
            );
        } catch (\Throwable $e) {
            $mqttPublished = false;

            Log::warning('Failed to publish room config to MQTT', [
                'room_id' => $room->id,
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
        }

        Cache::put("device_status_{$deviceId}", 'offline');

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'add_room',
        ]);

        $message = $mqttPublished
            ? 'Room berhasil ditambahkan'
            : 'Room berhasil ditambahkan, tetapi konfigurasi MQTT gagal dikirim';

        return redirect('/rooms')->with('success', $message);
    }

    /* === DELETE ROOM === */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $deviceId = strtolower(trim((string) $room->device_id));

        $mqttPublished = true;

        try {
            $mqtt = new MqttService;

            $mqtt->publish(
                "device/{$deviceId}/clear",
                json_encode(new \stdClass),
                1,
                true
            );
        } catch (\Throwable $e) {
            $mqttPublished = false;

            Log::warning('Failed to publish room clear command to MQTT', [
                'room_id' => $room->id,
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
        }

        Cache::forget("device_status_{$deviceId}");
        Cache::forget("device_{$deviceId}_last_seen");

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'delete_room',
        ]);

        $room->delete();

        $message = $mqttPublished
            ? 'Room berhasil dihapus'
            : 'Room berhasil dihapus, tetapi perintah clear ke MQTT gagal dikirim';

        return redirect('/rooms')->with('success', $message);
    }

    /* === OVERVIEW ALL ROOMS === */
    public function overview()
    {
        $rooms = Room::with(['acUnits.status'])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

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
                && now()->diffInSeconds($lastSeen, true) <= 30;

            $room->device_status = $isOnline ? 'online' : 'offline';
        }

        $roomsByFloor = $rooms->groupBy(fn ($r) => $r->floor ?: 'Lainnya');

        return view('rooms.overview', compact('rooms', 'roomsByFloor'));
    }

    /* === DETAIL STATUS AC === */
    public function status($id)
    {
        $room = Room::findOrFail($id);

        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();
        $room->temperature = optional(
            $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
        )->temperature;

        $deviceId = strtolower(trim((string) $room->device_id));
        $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
        $lastSeen = $this->lastSeenFrom(Cache::get("device_{$deviceId}_last_seen"))
            ?? $this->lastSeenFrom($room->last_seen);

        $isOnline = ($status === 'online' || $status === 'available')
            && $lastSeen
            && now()->diffInSeconds($lastSeen, true) <= 30;

        $room->device_status = $isOnline ? 'online' : 'offline';

        $acs = AcUnit::with('status')
            ->where('room_id', $id)
            ->get();

        return view('rooms.status', compact('room', 'acs'));
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
