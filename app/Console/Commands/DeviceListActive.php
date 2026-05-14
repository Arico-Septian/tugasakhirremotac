<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DeviceListActive extends Command
{
    protected $signature = 'device:list-active';

    protected $description = 'List semua MQTT device_id yang baru-baru ini ping ke broker';

    public function handle(): int
    {
        $seen = Cache::get('seen_device_ids', []);

        if (empty($seen)) {
            $this->warn('Belum ada device yang terdeteksi.');
            $this->line('Tip: pastikan `php artisan mqtt:subscribe` sedang berjalan dan tunggu device ping.');

            return self::SUCCESS;
        }

        $registered = Room::whereNotNull('device_id')
            ->get(['device_id', 'name'])
            ->mapWithKeys(fn ($room) => [strtolower(trim($room->device_id)) => $room->name])
            ->all();

        $rows = [];
        $unregistered = 0;

        foreach ($seen as $deviceId) {
            $lastSeen = Cache::get("device_{$deviceId}_last_seen") ?? '—';
            $status = Cache::get("device_status_{$deviceId}") ?? 'offline';
            $room = $registered[$deviceId] ?? null;

            if ($room === null) {
                $unregistered++;
            }

            $statusCell = $status === 'online'
                ? '<fg=green>online</>'
                : '<fg=red>offline</>';

            $roomCell = $room ?? '<fg=red>TIDAK TERDAFTAR</>';

            $rows[] = [$deviceId, $statusCell, $lastSeen, $roomCell];
        }

        $this->table(['Device ID', 'Status', 'Last Seen', 'Assigned Room'], $rows);

        if ($unregistered > 0) {
            $this->newLine();
            $this->warn("{$unregistered} device tidak terdaftar di DB — kemungkinan ESP32 lama yang masih nyala.");
            $this->line('Solusi: cabut device fisik, reflash dengan device_id baru, atau hapus retained message.');
        }

        return self::SUCCESS;
    }
}
