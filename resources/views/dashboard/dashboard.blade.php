<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Centralized AC Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="bg-gradient-to-br from-blue-100 via-white to-purple-100 min-h-screen">

<div class="flex">

<!-- SIDEBAR -->

<div class="w-64 bg-white/70 backdrop-blur-xl shadow-xl h-screen p-6 border-r">

<h2 class="text-2xl font-bold text-blue-600 mb-10 flex items-center gap-2">

<i class="fa-solid fa-layer-group"></i>

Centralized AC

</h2>

<ul class="space-y-4">

<li>

<a href="/dashboard"
class="flex items-center gap-2 bg-blue-100 text-blue-600 px-4 py-3 rounded-lg font-semibold">

<i class="fa-solid fa-chart-pie"></i>

Dashboard

</a>

</li>

<li>

<a href="/rooms"
class="flex items-center gap-2 hover:text-blue-600">

<i class="fa-solid fa-server"></i>

Manage Rooms

</a>

</li>

<li>

<a href="/dashboard/ac-control"
class="flex items-center gap-2 hover:text-blue-600">

<i class="fa-solid fa-snowflake"></i>

AC Units Control

</a>

</li>

<li>

<a href="/logout"
class="flex items-center gap-2 text-red-500">

<i class="fa-solid fa-right-from-bracket"></i>

Logout

</a>

</li>

</ul>

</div>

<!-- MAIN -->

<div class="flex-1 p-10">

<!-- HEADER -->

<div class="flex justify-between items-center mb-10">

<h1 class="text-3xl font-bold text-gray-800">

Centralized AC Management System

</h1>

<div class="flex items-center gap-4">

<span class="text-green-500 font-semibold">

● Online

</span>

<div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full flex items-center justify-center font-bold">

A

</div>

</div>

</div>

<!-- STATISTICS -->

<div class="grid grid-cols-4 gap-6 mb-12">

<!-- ROOMS -->

<div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-6 hover:scale-105 transition">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500">Total Rooms</p>

<h2 class="text-4xl font-bold text-blue-600">

{{$rooms->count()}}

</h2>

</div>

<i class="fa-solid fa-server text-3xl text-blue-500"></i>

</div>

</div>

<!-- AC -->

<div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-6 hover:scale-105 transition">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500">Active AC Units</p>

<h2 class="text-4xl font-bold text-green-600">

{{$activeAc}}

</h2>

</div>

<i class="fa-solid fa-wind text-3xl text-green-500"></i>

</div>

</div>

<!-- USERS -->

<div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-6 hover:scale-105 transition">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500">Total Users</p>

<h2 class="text-4xl font-bold text-purple-600">

{{$users}}

</h2>

</div>

<i class="fa-solid fa-users text-3xl text-purple-500"></i>

</div>

</div>

<!-- ONLINE -->

<div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-xl p-6 hover:scale-105 transition">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500">Users Online</p>

<h2 class="text-4xl font-bold text-orange-500">

1

</h2>

</div>

<i class="fa-solid fa-user-check text-3xl text-orange-500"></i>

</div>

</div>

</div>

<!-- SERVER ROOMS -->

<h2 class="text-2xl font-bold mb-6 text-gray-800">

Server Rooms

</h2>

<div class="grid grid-cols-3 gap-8">

@foreach($rooms as $room)

<div class="bg-white/80 backdrop-blur-xl shadow-xl rounded-3xl p-6 hover:shadow-2xl hover:scale-[1.02] transition">

<div class="flex justify-between items-center mb-3">

<h3 class="text-xl font-bold">

{{$room->name}}

</h3>

<i class="fa-solid fa-server text-blue-500"></i>

</div>

<p class="text-gray-500 mb-4">

Total : {{$room->acUnits->count()}} units

</p>

<div class="bg-green-100 text-green-700 p-3 rounded-xl mb-2 flex justify-between">

<span>Active Units</span>

<span class="font-bold">

{{ $room->acUnits->where('status.power','ON')->count() }}

</span>

</div>

<div class="bg-gray-100 text-gray-600 p-3 rounded-xl mb-4 flex justify-between">

<span>Inactive Units</span>

<span class="font-bold">

{{ $room->acUnits->where('status.power','OFF')->count() }}

</span>

</div>

<a href="/rooms/{{$room->id}}/ac">

<button class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white py-2 rounded-xl hover:opacity-90">

View Details

</button>

</a>

</div>

@endforeach

</div>

</div>

</div>

</body>
</html>
