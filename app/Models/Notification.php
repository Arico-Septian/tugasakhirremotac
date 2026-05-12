<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'severity',
        'title',
        'message',
        'link',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUserOrBroadcast($query, ?int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public static function notify(string $type, string $title, array $opts = []): self
    {
        return self::create([
            'user_id' => $opts['user_id'] ?? null,
            'type' => $type,
            'severity' => $opts['severity'] ?? 'info',
            'title' => $title,
            'message' => $opts['message'] ?? null,
            'link' => $opts['link'] ?? null,
            'meta' => $opts['meta'] ?? null,
        ]);
    }

    public static function deviceOffline(string $roomName, ?string $deviceId = null): ?self
    {
        $stateKey = "device_state:{$roomName}";
        $prevState = cache()->get($stateKey);

        if ($prevState === 'offline') {
            return null;
        }

        cache()->put($stateKey, 'offline', now()->addDays(7));

        return self::notify('device_offline', "ESP {$roomName} offline", [
            'severity' => 'error',
            'message' => "Device {$deviceId} di ruangan " . ucwords($roomName) . " tidak terhubung. Cek koneksi WiFi atau power.",
            'meta' => ['room' => $roomName, 'device_id' => $deviceId],
        ]);
    }

    public static function deviceOnline(string $roomName, ?string $deviceId = null): ?self
    {
        $stateKey = "device_state:{$roomName}";
        $prevState = cache()->get($stateKey);

        if ($prevState === 'online') {
            return null;
        }

        cache()->put($stateKey, 'online', now()->addDays(7));

        return self::notify('device_online', "ESP {$roomName} online", [
            'severity' => 'info',
            'message' => "Device {$deviceId} di ruangan " . ucwords($roomName) . " terhubung kembali.",
            'meta' => ['room' => $roomName, 'device_id' => $deviceId],
        ]);
    }

    public static function fuzzyAction(string $roomName, string $action, int $setpointBefore, int $setpointAfter): ?self
    {
        $stateKey = "fuzzy_action:{$roomName}";
        $prevAction = cache()->get($stateKey);

        if ($prevAction === $action) {
            return null;
        }

        cache()->put($stateKey, $action, now()->addDays(7));

        $title = "Fuzzy Logic: {$roomName}";
        $message = self::buildFuzzyMessage($roomName, $action, $setpointBefore, $setpointAfter);
        $severity = $action === 'DIAM' ? 'info' : 'warning';

        return self::notify('fuzzy_action', $title, [
            'severity' => $severity,
            'message' => $message,
            'meta' => [
                'room' => $roomName,
                'action' => $action,
                'setpoint_before' => $setpointBefore,
                'setpoint_after' => $setpointAfter,
            ],
        ]);
    }

    private static function buildFuzzyMessage(string $roomName, string $action, int $before, int $after): string
    {
        $room = ucwords($roomName);

        return match ($action) {
            'TURUNKAN' => "AC {$room}: Sistem mendeteksi panas, mendinginkan ({$before}°C → {$after}°C)",
            'NAIKKAN' => "AC {$room}: Sistem mendeteksi dingin, memanaskan ({$before}°C → {$after}°C)",
            default => "AC {$room}: Status stabil ({$before}°C)",
        };
    }

    public static function fuzzyWarning(string $roomName, string $reason = 'temperature_offline'): ?self
    {
        $stateKey = "fuzzy_warning:{$roomName}:{$reason}";
        $lastWarning = cache()->get($stateKey);

        if ($lastWarning === 'warned') {
            return null;
        }

        cache()->put($stateKey, 'warned', now()->addMinutes(30));

        $message = match ($reason) {
            'device_offline' => "ESP ruangan " . ucwords($roomName) . " offline — Fuzzy logic tidak berjalan. Periksa koneksi device.",
            default => "Sensor suhu ruangan " . ucwords($roomName) . " offline — Fuzzy logic tidak berjalan. Periksa koneksi sensor.",
        };

        return self::notify('fuzzy_warning', "Fuzzy Logic: {$roomName}", [
            'severity' => 'error',
            'message' => $message,
            'meta' => ['room' => $roomName, 'reason' => $reason],
        ]);
    }

    public static function fuzzyRecovery(string $roomName): ?self
    {
        $stateKey = "fuzzy_warning:{$roomName}";
        $wasWarned = cache()->has($stateKey);

        cache()->forget($stateKey);

        if (!$wasWarned) {
            return null;
        }

        return self::notify('fuzzy_recovery', "Fuzzy Logic: {$roomName}", [
            'severity' => 'info',
            'message' => "Sensor suhu ruangan " . ucwords($roomName) . " online — Fuzzy logic active kembali.",
            'meta' => ['room' => $roomName, 'reason' => 'temperature_online'],
        ]);
    }
}
