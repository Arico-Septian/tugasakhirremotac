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

    const WINDOW_BEFORE = -30;
    const WINDOW_AFTER = 60;
    const EXECUTION_BUFFER = 60;
    const COOLDOWN_SECONDS = 5;

    public function handle()
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = Carbon::today('Asia/Jakarta');

        $this->info("🕐 Checking AC timers at " . $now->toDateTimeString());

        $mqtt = new MqttService();

        $acs = AcUnit::with('room:id,name')
            ->select('id', 'room_id', 'ac_number', 'timer_on', 'timer_off', 'power_status')
            ->whereHas('room')
            ->where(function ($q) {
                $q->whereNotNull('timer_on')
                  ->orWhereNotNull('timer_off');
            })
            ->get();

        if ($acs->isEmpty()) {
            $this->line("No active timers found.");
        }

        foreach ($acs as $ac) {

            // Cek cooldown untuk mencegah spam
            $cooldownKey = "ac_cooldown_{$ac->id}";
            if (Cache::has($cooldownKey)) {
                continue; // Skip jika masih dalam cooldown
            }

            $version = Cache::get("timer_version_{$ac->id}", 1);
            $roomName = strtolower(trim($ac->room->name));
            $topic   = "room/{$roomName}/ac/{$ac->ac_number}/control";

            foreach (['on', 'off'] as $type) {

                $timerField     = "timer_{$type}";
                $expectedStatus = strtoupper($type); // "ON" atau "OFF"

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
                        $this->warn("Lock not acquired for AC {$ac->ac_number} {$type}");
                        continue;
                    }

                    try {
                        // Double check (extra safety)
                        if (Cache::has($key)) {
                            $this->line("Already executed: AC {$ac->ac_number} {$type}");
                            continue;
                        }

                        $this->info("⏰ Executing timer: AC {$ac->ac_number} → {$expectedStatus}");

                        // Kirim perintah ke MQTT
                        $mqtt->publish($topic, json_encode([
                            "power" => $expectedStatus
                        ]), 1, false);

                        // Update database
                        $ac->update([
                            'power_status' => $expectedStatus
                        ]);

                        // Tandai sudah dieksekusi
                        Cache::put($key, true, 300);

                        // Set cooldown
                        Cache::put($cooldownKey, true, self::COOLDOWN_SECONDS);

                        Log::info("TIMER {$expectedStatus} SUCCESS", [
                            'ac_id'    => $ac->ac_number,
                            'room'     => $roomName,
                            'time'     => $now->toDateTimeString(),
                            'timer_at' => $timer->toDateTimeString()
                        ]);

                        $this->info("✅ TIMER {$expectedStatus} → AC {$ac->ac_number}");

                    } catch (\Exception $e) {
                        Log::error("MQTT {$expectedStatus} ERROR", [
                            'ac_id'  => $ac->ac_number,
                            'error'  => $e->getMessage(),
                            'topic'  => $topic
                        ]);
                        $this->error("❌ Failed: AC {$ac->ac_number} - " . $e->getMessage());
                    } finally {
                        optional($lock)->release();
                    }
                }
            }
        }

        $this->info("✅ Timer check completed");
        return Command::SUCCESS;
    }
}
