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
use App\Services\FuzzyMamdaniService;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with(['acUnits.status'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

        // =========================
        // FUZZY SERVICE
        // =========================
        $fuzzyService = new FuzzyMamdaniService();

        foreach ($rooms as $room) {

            $deviceId = strtolower(trim((string) $room->device_id));

            $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');

            $lastSeen = $this->lastSeenFrom(
                Cache::get("device_{$deviceId}_last_seen")
            ) ?? $this->lastSeenFrom($room->last_seen);

            $isOnline =
                ($status === 'online' || $status === 'available')
                && $lastSeen
                && now()->diffInSeconds($lastSeen, true) <= 30;

            $room->device_status = $isOnline ? 'online' : 'offline';

            // =========================
            // SUHU TERBARU
            // =========================

            $lastTempRecord = $latestTemperatures->get(
                RoomTemperature::normalizeRoomName($room->name)
            );

            $room->temperature = optional($lastTempRecord)->temperature;

            // Check if temperature data is stale (offline)
            $room->temperature_is_offline = false;
            if ($lastTempRecord && $lastTempRecord->created_at) {
                $secondsSinceLastTemp = now()->diffInSeconds($lastTempRecord->created_at, true);
                $room->temperature_is_offline = $secondsSinceLastTemp > 60;
            } elseif (!$lastTempRecord) {
                $room->temperature_is_offline = true;
            }

            // =========================
            // AMBIL 2 DATA TERBARU
            // =========================

            $tempHistory = RoomTemperature::where(
                'room',
                RoomTemperature::normalizeRoomName($room->name)
            )
                ->latest()
                ->take(2)
                ->get();

            $currentTemp = $tempHistory->first()?->temperature;

            $previousTemp = $tempHistory->count() > 1
                ? $tempHistory[1]->temperature
                : $currentTemp;

            // =========================
            // HITUNG DELTA T
            // =========================

            $deltaT = ($currentTemp !== null && $previousTemp !== null)
                ? ($currentTemp - $previousTemp)
                : 0;

            // =========================
            // FUZZY CALCULATION
            // =========================

            if ($currentTemp !== null) {

                $fuzzyResult = $fuzzyService->calculate(
                    $currentTemp,
                    $deltaT
                );

                $room->temperature = round($currentTemp, 1);

                $room->delta_t = round($deltaT, 2);

                $room->fuzzy = $fuzzyResult;

                // =========================
                // FUZZY DECISION
                // =========================

                // sementara setpoint default dulu
                $currentSetpoint = 24;

                $decision = $fuzzyService->decideAction(
                    $fuzzyResult,
                    $currentSetpoint
                );

                $room->decision = $decision;
            } else {

                $room->delta_t = 0;
                $room->fuzzy = null;
                $room->decision = null;
            }
        }

        $roomsByFloor = $rooms->groupBy(
            fn($room) => $room->floor ?: 'Lainnya'
        );

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
        Cache::forget("device_{$deviceId}_last_seen");

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

        $onlineRooms = 0;
        $offlineRooms = 0;

        foreach ($rooms as $room) {
            $lastTempRecord = $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name));
            $room->temperature = optional($lastTempRecord)->temperature;

            $room->temperature_is_offline = false;
            if ($lastTempRecord && $lastTempRecord->created_at) {
                $secondsSinceLastTemp = now()->diffInSeconds($lastTempRecord->created_at, true);
                $room->temperature_is_offline = $secondsSinceLastTemp > 60;
            } elseif (!$lastTempRecord) {
                $room->temperature_is_offline = true;
            }

            $deviceId = strtolower(trim((string) $room->device_id));
            $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
            $lastSeen = $this->lastSeenFrom(Cache::get("device_{$deviceId}_last_seen"))
                ?? $this->lastSeenFrom($room->last_seen);

            $isOnline = ($status === 'online' || $status === 'available')
                && $lastSeen
                && now()->diffInSeconds($lastSeen, true) <= 30;

            $room->device_status = $isOnline ? 'online' : 'offline';

            $isOnline ? $onlineRooms++ : $offlineRooms++;
        }

        $roomsByFloor = $rooms->groupBy(fn($r) => $r->floor ?: 'Lainnya');

        return view('rooms.overview', compact('rooms', 'roomsByFloor'));
    }

    /* === DETAIL STATUS AC === */
    public function status($id)
    {
        $room = Room::findOrFail($id);

        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();
        $lastTempRecord = $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name));
        $room->temperature = optional($lastTempRecord)->temperature;

        $room->temperature_is_offline = false;
        if ($lastTempRecord && $lastTempRecord->created_at) {
            $secondsSinceLastTemp = now()->diffInSeconds($lastTempRecord->created_at, true);
            $room->temperature_is_offline = $secondsSinceLastTemp > 60;
        } elseif (!$lastTempRecord) {
            $room->temperature_is_offline = true;
        }

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
