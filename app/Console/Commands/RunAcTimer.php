<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AcUnit;
use App\Services\MqttService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RunAcTimer extends Command
{
    protected $signature = 'ac:run-timer';
    protected $description = 'Run AC timer ON/OFF (Anti Miss + Anti Double)';

    const WINDOW_BEFORE = -30; // detik sebelum
    const WINDOW_AFTER = 60;   // detik setelah
    const EXECUTION_BUFFER = 60; // anti double 1 menit

    public function handle()
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = Carbon::today('Asia/Jakarta');

        $mqtt = new MqttService();

        $acs = AcUnit::with('room:id,name')
            ->select('id', 'room_id', 'ac_number', 'timer_on', 'timer_off', 'power_status')
            ->whereHas('room')
            ->where(function ($q) {
                $q->whereNotNull('timer_on')
                  ->orWhereNotNull('timer_off');
            })
            ->get();

        foreach ($acs as $ac) {

            $version = Cache::get("timer_version_{$ac->id}", 1);
            $topic   = "room/{$ac->room->name}/ac/{$ac->ac_number}/control";

            foreach (['on', 'off'] as $type) {

                $timerField     = "timer_{$type}";
                $expectedStatus = strtoupper($type);

                if (!$ac->$timerField) continue;

                $timer = $today->copy()->setTimeFromTimeString($ac->$timerField);

                $diff = $now->diffInSeconds($timer, false);
                $alreadyExecuted = $now->gt($timer->copy()->addSeconds(self::EXECUTION_BUFFER));

                $key = "timer_{$type}_{$ac->id}_v{$version}_" . $timer->format('Y-m-d_H:i');

                if (
                    $diff >= self::WINDOW_BEFORE &&
                    $diff <= self::WINDOW_AFTER &&
                    !$alreadyExecuted &&
                    $ac->power_status !== $expectedStatus
                ) {

                    $lock = Cache::lock("lock:{$key}", 10);

                    if (!$lock->get()) {
                        continue;
                    }

                    try {

                        // double check (extra safety)
                        if (Cache::has($key)) {
                            continue;
                        }

                        $mqtt->publish($topic, json_encode([
                            "power" => $expectedStatus
                        ]));

                        $ac->update([
                            'power_status' => $expectedStatus
                        ]);

                        Cache::put($key, true, 300);

                        Log::info("TIMER {$expectedStatus} SUCCESS", [
                            'ac'   => $ac->ac_number,
                            'time' => $now->toDateTimeString()
                        ]);

                        $this->info("TIMER {$expectedStatus} → AC {$ac->ac_number}");

                    } catch (\Exception $e) {

                        Log::error("MQTT {$expectedStatus} ERROR", [
                            'ac'    => $ac->ac_number,
                            'error' => $e->getMessage()
                        ]);

                    } finally {
                        optional($lock)->release();
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
