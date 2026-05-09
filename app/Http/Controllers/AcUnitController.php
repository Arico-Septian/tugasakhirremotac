<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AcUnitController extends Controller
{
    public function index($id)
    {
        $room = Room::findOrFail($id);

        $this->setCurrentDeviceStatus($room);

        $acs = AcUnit::with('status')
            ->where('room_id', $id)
            ->get();

        return view('ac.index', compact('room', 'acs'));
    }

    public function store(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);

        if ($room->acUnits()->count() >= 15) {
            return back()->with('error', 'Maksimal 15 AC per ruangan');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'brand' => 'required|string|max:50',
            'ac_number' => [
                'required',
                'integer',
                'min:1',
                'max:15',
                Rule::unique('ac_units')->where(fn ($q) => $q->where('room_id', $roomId)),
            ],
        ]);

        $ac = AcUnit::create([
            'name' => $request->name,
            'room_id' => $roomId,
            'brand' => $request->brand,
            'ac_number' => $request->ac_number,
        ]);

        AcStatus::create([
            'ac_unit_id' => $ac->id,
            'power' => 'OFF',
            'mode' => 'COOL',
            'set_temperature' => 24,
            'fan_speed' => 'AUTO',
            'swing' => 'OFF',
            'room_temperature' => 24,
        ]);

        (new MqttService)->resendConfig($room->device_id);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC '.$ac->ac_number,
            'activity' => 'add_ac',
        ]);

        return back()->with('new_ac_id', $ac->id);
    }

    public function update(Request $request, $id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $request->validate([
            'name' => 'required|string|max:50',
            'brand' => 'required|string|max:50',
            'ac_number' => [
                'required', 'integer', 'min:1', 'max:15',
                Rule::unique('ac_units')
                    ->where(fn ($q) => $q->where('room_id', $ac->room_id))
                    ->ignore($ac->id),
            ],
        ]);

        $ac->update([
            'name' => $request->name,
            'brand' => $request->brand,
            'ac_number' => $request->ac_number,
        ]);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC '.$ac->ac_number.($ac->name ? ' '.$ac->name : ''),
            'activity' => 'edit_ac',
        ]);

        return back()->with('success', 'AC unit berhasil diperbarui');
    }

    public function destroy($id)
    {
        $ac = AcUnit::findOrFail($id);

        $room = Room::findOrFail($ac->room_id);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC '.$ac->ac_number,
            'activity' => 'delete_ac',
        ]);

        $room_id = $ac->room_id;

        $ac->delete();

        $mqtt = new MqttService;

        $mqtt->resendConfig($room->device_id);

        return redirect('/rooms/'.$room_id.'/ac');
    }

    private function setCurrentDeviceStatus(Room $room): void
    {
        $deviceId = strtolower(trim((string) $room->device_id));

        if ($deviceId === '') {
            $room->device_status = 'offline';

            return;
        }

        $status = Cache::get("device_status_{$deviceId}", $room->device_status ?? 'offline');
        $lastSeen = $this->lastSeenFrom(Cache::get("device_{$deviceId}_last_seen"))
            ?? $this->lastSeenFrom($room->last_seen);

        $isOnline = ($status === 'online' || $status === 'available')
            && $lastSeen
            && now()->diffInSeconds($lastSeen, true) <= 30;

        $room->device_status = $isOnline ? 'online' : 'offline';
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

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'timer_on' => 'nullable|date_format:H:i',
            'timer_off' => 'nullable|date_format:H:i',
        ]);

        if ($request->timer_on && $request->timer_off) {
            if ($request->timer_off <= $request->timer_on) {
                return back()->withErrors([
                    'Timer OFF harus lebih besar dari ON',
                ])->withInput();
            }
        }

        $ac = AcUnit::findOrFail($id);

        $ac->update([
            'timer_on' => $request->timer_on,
            'timer_off' => $request->timer_off,
        ]);

        return back()->with('success', 'Timer disimpan');
    }
}
