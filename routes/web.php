<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login','auth.login');
Route::view('/register','auth.register');

use App\Http\Controllers\AuthController;


// halaman login
Route::get('/login',function(){
return view('auth.login');
});

// proses login
Route::post('/login',[AuthController::class,'login']);


// halaman register
Route::get('/register',function(){
return view('auth.register');
});

// proses register
Route::post('/register',[AuthController::class,'register']);


// logout
Route::get('/logout',[AuthController::class,'logout']);


// dashboard
Route::get('/dashboard',function(){

return view('dashboard.dashboard');

})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard.dashboard');
});

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

Route::get('/add-ac/{room}/{id}/{brand}', function ($room,$id,$brand){

    $mqtt = new \App\Services\MqttService();

    $topic = "room/$room/ac/add";

    $payload = json_encode([
        "id"=>$id,
        "brand"=>$brand
    ]);

    $mqtt->publish($topic,$payload);

    return "AC berhasil ditambahkan";
});

Route::get('/remove-ac/{room}/{id}', function ($room,$id){

    $mqtt = new \App\Services\MqttService();

    $topic = "room/$room/ac/remove";

    $payload = json_encode([
        "id"=>$id
    ]);

    $mqtt->publish($topic,$payload);

    return "AC dihapus";
});

Route::get('/ac-control/{room}/{id}/{mode}/{temp}', function ($room,$id,$mode,$temp){

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "power" => "ON",
        "mode"  => $mode,
        "temp"  => $temp
    ]);

    $mqtt->publish($topic,$payload);

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

Route::get('/ac-off/{room}/{id}', function ($room,$id){

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "power" => "OFF"
    ]);

    $mqtt->publish($topic,$payload);

    return "AC dimatikan";
});

Route::get('/ac-mode/{room}/{id}/{mode}', function ($room,$id,$mode){

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "mode" => $mode
    ]);

    $mqtt->publish($topic,$payload);

    return "Mode AC diubah";
});

Route::get('/ac-temp/{room}/{id}/{temp}', function ($room,$id,$temp){

    $mqtt = new MqttService();

    $topic = "room/$room/ac/$id/control";

    $payload = json_encode([
        "temp" => $temp
    ]);

    $mqtt->publish($topic,$payload);

    return "Temperatur diubah";
});
