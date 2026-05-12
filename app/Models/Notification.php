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
}
