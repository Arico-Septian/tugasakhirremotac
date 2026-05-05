<?php

use App\Http\Controllers\AcControlController;
use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLogController;
use App\Models\AcStatus;
use App\Models\Room;
use App\Models\RoomTemperature;
use App\Models\User;
use App\Services\MqttService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()
        ->route('login')
        ->with('error', 'Registrasi publik dinonaktifkan. Hubungi admin untuk membuat akun.');
});

Route::get('/system-check', function () {
    return response()->json(['status' => 'online']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/rooms/{id}/status', [RoomController::class, 'status']);

    Route::get('/api/ac-status', function () {
        return AcStatus::with('acUnit.room')->get();
    });

    Route::get('/ac-status', function () {
        return AcStatus::with('acUnit')->get();
    });

    Route::get('/device-status', function () {
        return Room::whereNotNull('device_id')
            ->orderBy('id')
            ->get()
            ->map(function ($room) {
                $deviceId = strtolower(trim($room->device_id));
                $lastSeen = Cache::get("device_{$deviceId}_last_seen");

                $lastSeenAt = null;
                $isOnline = false;

                if ($lastSeen) {
                    $lastSeenAt = $lastSeen instanceof Carbon ? $lastSeen : Carbon::parse($lastSeen);
                    $isOnline = now()->diffInSeconds($lastSeenAt) <= 15;
                }

                return [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'device_id' => $deviceId,
                    'is_online' => $isOnline,
                    'status' => $isOnline ? 'online' : 'offline',
                    'last_seen' => optional($lastSeenAt)->toDateTimeString(),
                ];
            })
            ->values();
    });

    Route::post('/update-activity', function () {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $user->last_activity = now();
            $user->save();
        }

        return response()->json(['status' => 'ok']);
    });

    Route::get('/my-status', function () {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->last_activity) {
            return response()->json(['status' => 'offline']);
        }

        return response()->json([
            'status' => now()->diffInSeconds($user->last_activity) < 60 ? 'online' : 'offline',
        ]);
    });

    $temperatureEndpoint = function () {
        $latestTemperatures = RoomTemperature::latest()
            ->get()
            ->unique('room')
            ->keyBy('room');

        return Room::orderBy('id')
            ->get()
            ->map(function ($room) use ($latestTemperatures) {
                $temperature = optional($latestTemperatures->get($room->name))->temperature;

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'temp' => $temperature,
                    'temperature' => $temperature,
                ];
            })
            ->values();
    };

    Route::get('/temperature', $temperatureEndpoint);
    Route::get('/temperatures', $temperatureEndpoint);

    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::post('/rooms/add', [RoomController::class, 'store']);
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

        Route::get('/rooms/{id}/ac', [AcUnitController::class, 'index']);
        Route::post('/rooms/{id}/ac', [AcUnitController::class, 'store']);
        Route::delete('/ac/{id}', [AcUnitController::class, 'destroy']);

        Route::get('/ac/{id}/on', [AcControlController::class, 'powerOn']);
        Route::get('/ac/{id}/off', [AcControlController::class, 'powerOff']);
        Route::get('/ac/{id}/temp/{value}', [AcControlController::class, 'setTemp']);
        Route::get('/ac/{id}/mode/{mode}', [AcControlController::class, 'setMode']);
        Route::post('/ac/{id}/toggle', [AcControlController::class, 'togglePower']);
        Route::post('/ac/{id}/schedule', [TimerController::class, 'schedule']);

        Route::get('/set-room/{room}', function ($room) {
            $mqtt = new MqttService();
            $topic = 'device/esp32_01/config';

            $mqtt->publish($topic, json_encode(['room' => $room]));

            return "Room set to {$room}";
        });

        Route::get('/add-ac/{room}/{id}/{brand}', function ($room, $id, $brand) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/add";

            $mqtt->publish($topic, json_encode([
                'id' => $id,
                'brand' => $brand,
            ]));

            return 'AC added';
        });

        Route::get('/remove-ac/{room}/{id}', function ($room, $id) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/remove";

            $mqtt->publish($topic, json_encode(['id' => $id]));

            return 'AC dihapus';
        });

        Route::get('/ac-control/{room}/{id}/{mode}/{temp}', function ($room, $id, $mode, $temp) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/{$id}/control";

            $mqtt->publish($topic, json_encode([
                'power' => 'ON',
                'mode' => $mode,
                'temp' => $temp,
            ]));

            return 'AC berhasil dikontrol';
        });

        Route::get('/ac-on/{room}/{id}', function ($room, $id) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/{$id}/control";

            $mqtt->publish($topic, json_encode(['power' => 'ON']));

            return 'AC dinyalakan';
        });

        Route::get('/ac-off/{room}/{id}', function ($room, $id) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/{$id}/control";

            $mqtt->publish($topic, json_encode(['power' => 'OFF']));

            return 'AC dimatikan';
        });

        Route::get('/ac-mode/{room}/{id}/{mode}', function ($room, $id, $mode) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/{$id}/control";

            $mqtt->publish($topic, json_encode(['mode' => $mode]));

            return 'Mode AC diubah';
        });

        Route::get('/ac-temp/{room}/{id}/{temp}', function ($room, $id, $temp) {
            $mqtt = new MqttService();
            $topic = "room/{$room}/ac/{$id}/control";

            $mqtt->publish($topic, json_encode(['temp' => $temp]));

            return 'Temperatur diubah';
        });
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/status/{id}', [UserController::class, 'changeStatus']);

        Route::get('/logs', [UserLogController::class, 'index']);
        Route::delete('/logs/delete-all', [UserLogController::class, 'destroyAll']);

        Route::get('/users-online', function () {
            return response()->json([
                'count' => User::where('last_activity', '>=', now()->subMinutes(5))->count(),
            ]);
        });
    });

    Route::get('/cek-driver', function () {
        return config('cache.default');
    });

    Route::get('/test-cache', function () {
        Cache::put('test_key', 'OK', 60);

        return Cache::get('test_key');
    });

    Route::get('/suhu-raspi', function () {
        $data = file_get_contents('http://192.168.79.28:8000/suhu.php');
        preg_match('/([0-9.]+)/', $data, $matches);

        return [
            'suhu' => ($matches[1] ?? null) ? $matches[1] . ' C' : null,
        ];
    });

    Route::get('/monitoring', function () {
        return view('suhu');
    });
});
