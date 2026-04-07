<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AcUnit;
use App\Services\MqttService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RunAcTimer extends Command
{
    protected $signature = 'ac:run-timer';
    protected $description = 'Run AC timer ON/OFF (Anti Miss + Anti Double)';

    public function handle()
    {
        $now = Carbon::now();

        $mqtt = new MqttService();

        $acs = AcUnit::with('room')->get();

        foreach ($acs as $ac) {


            if (!$ac->room) continue;

            $topic = "room/{$ac->room->name}/ac/{$ac->ac_number}/control";

            if ($ac->timer_on) {

                $timerOn = Carbon::parse($ac->timer_on);

                $diff = $now->diffInSeconds($timerOn, false);

                if ($diff >= 0 && $diff <= 60) {

                    $key = "timer_on_{$ac->id}_" . $timerOn->format('H:i');

                    if (!Cache::has($key)) {

                        $mqtt->publish($topic, json_encode([
                            "power" => "ON"
                        ]));

                        Cache::put($key, true, 120);

                        $this->info("🟢 TIMER ON → AC {$ac->ac_number}");
                    }
                }
            }


            if ($ac->timer_off) {

                $timerOff = Carbon::parse($ac->timer_off);

                $diff = $now->diffInSeconds($timerOff, false);

                if ($diff >= 0 && $diff <= 60) {

                    $key = "timer_off_{$ac->id}_" . $timerOff->format('H:i');

                    if (!Cache::has($key)) {

                        $mqtt->publish($topic, json_encode([
                            "power" => "OFF"
                        ]));

                        Cache::put($key, true, 120);

                        $this->info("🔴 TIMER OFF → AC {$ac->ac_number}");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
