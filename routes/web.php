<?php

use Illuminate\Support\Facades\Route;
use App\Models\Room;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Services\MqttService;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AcControlController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLogController;
use App\Models\AcStatus;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\TimerController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login');
Route::view('/register', 'auth.register');

// halaman login
Route::get('/login', function () {
    return view('auth.login');
});

// proses login
Route::post('/login', [AuthController::class, 'login']);

// halaman register
Route::get('/register', function () {
    return view('auth.register');
});

// logout
Route::get('/logout', [AuthController::class, 'logout']);

// Setting room manual
Route::get('/set-room/{room}', function ($room) {

    $mqtt = new MqttService();

    $topic = "device/esp32_01/config";

    $payload = json_encode([
        "room" => $room
    ]);

    $mqtt->publish($topic, $payload);

    return "Room set to $room";
});

//menambah ac unit
Route::get('/add-ac/{room}/{id}/{brand}', function ($room, $id, $brand) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/add";

    $payload = json_encode([
        "id" => $id,
        "brand" => $brand
    ]);

    $mqtt->publish($topic, $payload);

    return "AC added";
});

Route::get('/set-room/{room}', function ($room) {

    $mqtt = new MqttService();;

    $topic = "device/esp32_01/config";

    $payload = json_encode([
        "room" => $room
    ]);

    $mqtt->publish($topic, $payload);

    return "Room berhasil diset";
});

Route::get('/add-ac/{room}/{id}/{brand}', function ($room, $id, $brand) {

    $mqtt = new MqttService();;

    $topic = "room/$room/ac/add";

    $payload = json_encode([
        "id" => $id,
        "brand" => $brand
    ]);

    $mqtt->publish($topic, $payload);

    return "AC berhasil ditambahkan";
});

Route::get('/remove-ac/{room}/{id}', function ($room, $id) {

    $mqtt = new MqttService();;

    $topic = "room/$room/ac/remove";

    $payload = json_encode([
        "id" => $id
    ]);

    $mqtt->publish($topic, $payload);

    return "AC dihapus";
});

Route::get('/ac-control/{room}/{id}/{mode}/{temp}', function ($room, $id, $mode, $temp) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "power" => "ON",
        "mode"  => $mode,
        "temp"  => $temp
    ]);

    $mqtt->publish($topic, $payload);

    return "AC berhasil dikontrol";
});

Route::get('/ac-on/{room}/{id}', function ($room, $id) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "power" => "ON"
    ]);

    $mqtt->publish($topic, $payload);

    return "AC dinyalakan";
});

Route::get('/ac-off/{room}/{id}', function ($room, $id) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "power" => "OFF"
    ]);

    $mqtt->publish($topic, $payload);

    return "AC dimatikan";
});

Route::get('/ac-mode/{room}/{id}/{mode}', function ($room, $id, $mode) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "mode" => $mode
    ]);

    $mqtt->publish($topic, $payload);

    return "Mode AC diubah";
});

Route::get('/ac-temp/{room}/{id}/{temp}', function ($room, $id, $temp) {

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "temp" => $temp
    ]);

    $mqtt->publish($topic, $payload);

    return "Temperatur diubah";
});

Route::get('/system-check', function () {
    return response()->json(['status' => 'online']);
});

// setting melalui dashboard
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);

Route::post('/rooms/add', [RoomController::class, 'store']);

Route::get('/rooms/{id}/ac', [AcUnitController::class, 'index']);
Route::post('/rooms/{id}/ac', [AcUnitController::class, 'store']);

Route::get('/ac/{id}/on', [AcControlController::class, 'powerOn']);
Route::get('/ac/{id}/off', [AcControlController::class, 'powerOff']);
Route::get('/ac/{id}/temp/{value}', [AcControlController::class, 'setTemp']);
Route::get('/ac/{id}/mode/{mode}', [AcControlController::class, 'setMode']);
Route::post('/ac/{id}/toggle', [AcControlController::class, 'togglePower']);


Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
Route::delete('/ac/{id}', [AcUnitController::class, 'destroy']);

Route::get('/rooms/{id}/status', [RoomController::class, 'status']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::post('/ac/{id}/schedule', [AcControlController::class, 'setSchedule']);

Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile']);

Route::middleware(['auth'])->group(function () {

    // semua role (admin, operator, user)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // hanya admin + operator
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::resource('/rooms', RoomController::class);
        Route::resource('/ac', AcUnitController::class);
    });

    // hanya admin
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('/users', UserController::class);
    });
});

Route::resource('/users', UserController::class)
    ->middleware('role:admin');

Route::post('/users/status/{id}', [UserController::class, 'changeStatus']);

Route::get('/logs', [UserLogController::class, 'index']);

Route::get('/api/ac-status', function () {
    return AcStatus::with('acUnit.room')->get();
});

Route::get('/users-online', function () {
    return response()->json([
        'count' => \App\Models\User::where('last_activity', '>=', now()->subMinutes(5))->count()
    ]);
});

Route::get('/cek-driver', function () {
    return config('cache.default');
});

Route::get('/test-cache', function () {
    Cache::put('test_key', 'OK', 60);
    return Cache::get('test_key');
});

Route::post('/ac/{id}/schedule', [TimerController::class, 'schedule']);

Route::get('/device-status', function () {

    $rooms = Room::all();

    return $rooms->map(function ($room) {

        $deviceId = strtolower($room->device_id);

        $lastSeen = Cache::get("device_{$deviceId}_last_seen");

        $isOnline = $lastSeen && now()->diffInSeconds(
            $lastSeen instanceof Carbon ? $lastSeen : Carbon::parse($lastSeen)
        ) <= 15;

        return [
            'device_id' => $deviceId,
            'is_online' => $isOnline
        ];
    });

});

Route::post('/update-activity', function () {

    if (Auth::check()) {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->last_activity = now();
        $user->save();
    }

    return response()->json(['status' => 'ok']);
});

Route::get('/my-status', function () {

    $user = Auth::user();

    if (!$user || !$user->last_activity) {
        return response()->json(['status' => 'offline']);
    }

    $isOnline = now()->diffInSeconds($user->last_activity) < 60;

    return response()->json([
        'status' => $isOnline ? 'online' : 'offline'
    ]);
});

Route::get('/temperatures', function () {
    return \App\Models\Room::select('name','temperature')->get();
});

Route::get('/temperature-stream', function () {

    return response()->stream(function () {

        while (true) {

            $rooms = \App\Models\Room::with('temperatureData')->get();

            $data = $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'temperature' => optional($room->temperatureData)->temperature
                ];
            });

            echo "data: " . json_encode($data) . "\n\n";

            ob_flush();
            flush();

            sleep(1);
        }

    }, 200, [
        "Content-Type" => "text/event-stream",
        "Cache-Control" => "no-cache",
        "Connection" => "keep-alive",
    ]);
});

Route::get('/users-status-stream', function () {

    return response()->stream(function () {

        while (true) {

            $users = \App\Models\User::all()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'online' => $u->last_activity >= now()->subMinutes(2)
                ];
            });

            echo "data: " . json_encode($users) . "\n\n";

            ob_flush();
            flush();

            sleep(5);
        }

    }, 200, [
        "Content-Type" => "text/event-stream",
        "Cache-Control" => "no-cache",
        "Connection" => "keep-alive",
    ]);
});

Route::delete('/logs/delete-all', [UserLogController::class, 'destroyAll']);
