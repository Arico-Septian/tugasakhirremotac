<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\RoomTemperature;
use App\Services\FuzzyMamdaniService;
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
        $room = Room::with(['acUnits.status'])->findOrFail($id);

        $this->setCurrentDeviceStatus($room);

        $acs = AcUnit::with('status')
            ->where('room_id', $id)
            ->get();

        // =========================
        // FUZZY LOGIC
        // =========================

        $fuzzyService = new FuzzyMamdaniService();

        $normalized = RoomTemperature::normalizeRoomName($room->name);

        $tempHistory = RoomTemperature::where('room', $normalized)
            ->latest()
            ->take(2)
            ->get();

        $currentTemp = $tempHistory->first()?->temperature;

        $previousTemp = $tempHistory->count() > 1
            ? $tempHistory[1]->temperature
            : $currentTemp;

        $deltaT = ($currentTemp !== null && $previousTemp !== null)
            ? ($currentTemp - $previousTemp)
            : 0;

        if ($currentTemp !== null) {

            $room->temperature = round($currentTemp, 1);

            $room->delta_t = round($deltaT, 2);

            $fuzzyResult = $fuzzyService->calculate(
                $currentTemp,
                $deltaT
            );

            $room->fuzzy = $fuzzyResult;

            // ambil setpoint dari AC pertama
            $currentSetpoint = (int) (
                $room->acUnits->first()?->status?->set_temperature ?? 24
            );

            $room->decision = $fuzzyService->decideAction(
                $fuzzyResult,
                $currentSetpoint
            );
        } else {

            $room->temperature = null;
            $room->delta_t = 0;
            $room->fuzzy = null;
            $room->decision = null;
        }

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
                Rule::unique('ac_units')->where(fn($q) => $q->where('room_id', $roomId)),
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
            'ac' => 'AC ' . $ac->ac_number,
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
                'required',
                'integer',
                'min:1',
                'max:15',
                Rule::unique('ac_units')
                    ->where(fn($q) => $q->where('room_id', $ac->room_id))
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
            'ac' => 'AC ' . $ac->ac_number . ($ac->name ? ' ' . $ac->name : ''),
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
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'delete_ac',
        ]);

        $room_id = $ac->room_id;

        $ac->delete();

        $mqtt = new MqttService;

        $mqtt->resendConfig($room->device_id);

        return redirect('/rooms/' . $room_id . '/ac');
    }

    public function applyFuzzy($id)
    {
        $room = Room::with(['acUnits.status'])->findOrFail($id);

        $fuzzyService = new FuzzyMamdaniService();

        $normalized = RoomTemperature::normalizeRoomName($room->name);

        $tempHistory = RoomTemperature::where('room', $normalized)
            ->latest()
            ->take(2)
            ->get();

        $currentTemp = $tempHistory->first()?->temperature;

        $previousTemp = $tempHistory->count() > 1
            ? $tempHistory[1]->temperature
            : $currentTemp;

        $deltaT = ($currentTemp !== null && $previousTemp !== null)
            ? ($currentTemp - $previousTemp)
            : 0;

        if ($currentTemp === null) {
            return back()->with('error', 'Data suhu belum tersedia');
        }

        $fuzzyResult = $fuzzyService->calculate(
            $currentTemp,
            $deltaT
        );

        $currentSetpoint = (int) (
            $room->acUnits->first()?->status?->set_temperature ?? 24
        );

        $decision = $fuzzyService->decideAction(
            $fuzzyResult,
            $currentSetpoint
        );

        $targetSetpoint = (int) (
            $decision['setpoint_after'] ?? $currentSetpoint
        );

        // =========================
        // COOLDOWN
        // =========================

        $cooldownKey = 'fuzzy_room_' . $room->id;

        if (Cache::has($cooldownKey)) {
            return back()->with(
                'warning',
                'Cooldown fuzzy masih aktif'
            );
        }

        Cache::put($cooldownKey, true, 30);

        // =========================
        // APPLY KE SEMUA AC
        // =========================

        $acController = new AcControlController();

        foreach ($room->acUnits as $ac) {

            $acController->fuzzySetTemp(
                $ac,
                $targetSetpoint
            );
        }

        return back()->with(
            'success',
            'Fuzzy berhasil diterapkan ke semua AC'
        );
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
