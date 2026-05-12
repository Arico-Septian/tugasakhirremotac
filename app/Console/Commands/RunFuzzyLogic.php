<?php

namespace App\Console\Commands;

use App\Http\Controllers\AcControlController;
use App\Models\Room;
use App\Models\RoomTemperature;
use App\Services\FuzzyMamdaniService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('fuzzy:run')]
#[Description('Apply fuzzy logic to automatically adjust AC setpoints based on room temperature and trends')]
class RunFuzzyLogic extends Command
{
    public function handle()
    {
        $rooms = Room::with(['acUnits.status'])->get();

        if ($rooms->isEmpty()) {
            $this->info('No rooms found');
            return Command::SUCCESS;
        }

        $processed = 0;
        $skipped = 0;

        foreach ($rooms as $room) {
            $cooldownKey = 'fuzzy_room_' . $room->id;

            if (Cache::has($cooldownKey)) {
                $skipped++;
                continue;
            }

            $fuzzyService = new FuzzyMamdaniService();
            $normalized = RoomTemperature::normalizeRoomName($room->name);

            $tempHistory = RoomTemperature::where('room', $normalized)
                ->latest()
                ->take(2)
                ->get();

            if ($tempHistory->isEmpty()) {
                continue;
            }

            $currentTemp = $tempHistory->first()->temperature;
            $previousTemp = $tempHistory->count() > 1
                ? $tempHistory[1]->temperature
                : $currentTemp;

            $deltaT = ($currentTemp !== null && $previousTemp !== null)
                ? ($currentTemp - $previousTemp)
                : 0;

            $fuzzyResult = $fuzzyService->calculate($currentTemp, $deltaT);

            $currentSetpoint = (int) round(
                $room->acUnits
                    ->map(fn($ac) => $ac->status?->set_temperature ?? 24)
                    ->avg()
            );

            $decision = $fuzzyService->decideAction($fuzzyResult, $currentSetpoint);

            if ($decision['action'] === 'DIAM') {
                continue;
            }

            Cache::put($cooldownKey, true, 60);

            $acController = new AcControlController();

            foreach ($room->acUnits as $ac) {
                $acController->fuzzySetTemp(
                    $ac,
                    $decision['setpoint_after']
                );
            }

            $processed++;
            $this->info("Room '{$room->name}': {$decision['action']} (setpoint {$decision['setpoint_before']}°C → {$decision['setpoint_after']}°C, temp: {$currentTemp}°C)");
        }

        $this->newLine();
        $this->info("Fuzzy logic applied to {$processed} room(s), {$skipped} skipped (cooldown)");

        return Command::SUCCESS;
    }
}
