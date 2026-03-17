<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login');
Route::view('/register', 'auth.register');


use App\Http\Controllers\AuthController;
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


// proses register
Route::post('/register', [AuthController::class, 'register']);


// logout
Route::get('/logout', [AuthController::class, 'logout']);


// Setting room manual
use App\Services\MqttService;

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

    $mqtt = new \App\Services\MqttService();

    $topic = "device/esp32_01/config";

    $payload = json_encode([
        "room" => $room
    ]);

    $mqtt->publish($topic, $payload);

    return "Room berhasil diset";
});

Route::get('/add-ac/{room}/{id}/{brand}', function ($room, $id, $brand) {

    $mqtt = new \App\Services\MqttService();

    $topic = "room/$room/ac/add";

    $payload = json_encode([
        "id" => $id,
        "brand" => $brand
    ]);

    $mqtt->publish($topic, $payload);

    return "AC berhasil ditambahkan";
});

Route::get('/remove-ac/{room}/{id}', function ($room, $id) {

    $mqtt = new \App\Services\MqttService();

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
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);
});

use App\Http\Controllers\AcUnitController;

Route::get('/dashboard/ac-control', [AcUnitController::class, 'index']);


use App\Http\Controllers\RoomController;

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms/add', [RoomController::class, 'store']);

Route::get('/rooms/{id}/ac', [AcUnitController::class, 'index']);
Route::post('/rooms/{id}/ac', [AcUnitController::class, 'store']);


use App\Http\Controllers\AcControlController;

Route::get('/ac/{id}/on', [AcControlController::class, 'powerOn']);
Route::get('/ac/{id}/off', [AcControlController::class, 'powerOff']);
Route::get('/ac/{id}/temp/{value}', [AcControlController::class, 'setTemp']);
Route::get('/ac/{id}/mode/{mode}', [AcControlController::class, 'setMode']);
Route::post('/ac/{id}/toggle', [AcControlController::class, 'togglePower']);


Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::get('/rooms/{id}/ac', [AcUnitController::class, 'index']);
Route::post('/rooms/{id}/ac', [AcUnitController::class, 'store']);

Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
Route::delete('/ac/{id}', [AcUnitController::class, 'destroy']);

Route::get('/rooms/{id}/status', [RoomController::class, 'status']);

use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::post('/ac/{id}/schedule', [AcControlController::class, 'setSchedule']);

Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile']);
