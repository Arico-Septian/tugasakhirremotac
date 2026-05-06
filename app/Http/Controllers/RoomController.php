<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Room;
use App\Models\RoomTemperature;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with(['acUnits.status'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('name')
            ->get();
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

        foreach ($rooms as $room) {
            $deviceId = strtolower(trim((string) $room->device_id));

            $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
            $lastSeen = Cache::get("device_{$deviceId}_last_seen") ?: $room->last_seen;

            if ($lastSeen) {
                $lastSeen = $lastSeen instanceof Carbon ? $lastSeen : Carbon::parse($lastSeen);
            }

            $isOnline = ($status === 'online' || $status === 'available') && $lastSeen && now()->diffInSeconds($lastSeen) <= 30;
            $room->device_status = $isOnline ? 'online' : 'offline';

            $room->temperature = optional(
                $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
            )->temperature;
        }

        return view('rooms.index', compact('rooms'));
    }

    /*=== CREATE ROOM ===*/
    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->name),
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
            'device_id' => $deviceId
        ]);

        $mqttPublished = true;

        try {
            $mqtt = new MqttService();
            $topic = "device/{$deviceId}/config";

            $mqtt->publish(
                $topic,
                json_encode([
                    "room" => $room->name
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
            'activity' => 'add_room'
        ]);

        $message = $mqttPublished
            ? 'Room berhasil ditambahkan'
            : 'Room berhasil ditambahkan, tetapi konfigurasi MQTT gagal dikirim';

        return redirect('/rooms')->with('success', $message);
    }

    /*=== DELETE ROOM ===*/
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $deviceId = strtolower(trim((string) $room->device_id));

        $mqttPublished = true;

        try {
            $mqtt = new MqttService();

            $mqtt->publish(
                "device/{$deviceId}/clear",
                json_encode(new \stdClass()),
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
            'activity' => 'delete_room'
        ]);

        $room->delete();

        $message = $mqttPublished
            ? 'Room berhasil dihapus'
            : 'Room berhasil dihapus, tetapi perintah clear ke MQTT gagal dikirim';

        return redirect('/rooms')->with('success', $message);
    }

    /*=== DETAIL STATUS AC ===*/
    public function status($id)
    {
        $room = Room::findOrFail($id);

        // Tambahkan suhu terbaru agar bisa ditampilkan di halaman detail status
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();
        $room->temperature = optional(
            $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
        )->temperature;

        $acs = AcUnit::with('status')
            ->where('room_id', $id)
            ->get();

        return view('rooms.status', compact('room', 'acs'));
    }
}
