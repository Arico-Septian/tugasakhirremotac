<!DOCTYPE html>
<html>
<head>

<title>AC Status</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="bg-gradient-to-br from-blue-100 via-white to-purple-100 min-h-screen">

<div class="flex">

<!-- SIDEBAR -->

<div class="w-64 bg-white shadow-xl h-screen p-6 border-r">

<h2 class="text-2xl font-bold text-blue-600 mb-10 flex items-center gap-2">

<i class="fa-solid fa-layer-group"></i>

Centralized AC

</h2>

<ul class="space-y-5">

<li>

<a href="/dashboard"
class="flex items-center gap-2 hover:text-blue-600">

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

<li class="flex items-center gap-2 text-blue-600 font-semibold">

<i class="fa-solid fa-snowflake"></i>

AC Units Control

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

<!-- MAIN CONTENT -->

<div class="flex-1 p-10">

<!-- HEADER -->

<div class="flex justify-between items-center mb-10">

<h1 class="text-3xl font-bold text-gray-800">

AC Status - {{$room->name}}

</h1>

<div class="flex items-center gap-4">

<span class="text-green-500 font-semibold">

● System Online

</span>

<div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full flex items-center justify-center font-bold">

A

</div>

</div>

</div>

<!-- AC GRID -->

<div class="grid grid-cols-3 gap-8">

@foreach($acs as $ac)

<div class="bg-white/80 backdrop-blur-xl shadow-xl rounded-3xl p-6 hover:shadow-2xl hover:scale-[1.02] transition border">

<div class="flex justify-between items-center mb-4">

<h2 class="text-xl font-bold">

AC {{$ac->ac_number}}

</h2>

<i class="fa-solid fa-snowflake text-blue-500 text-xl"></i>

</div>

<p class="text-gray-500 mb-4">

Brand : {{$ac->brand}}

</p>

@if($ac->status)

<div class="bg-green-100 text-green-700 p-3 rounded-xl mb-3 flex justify-between">

<span class="flex items-center gap-2">

<i class="fa-solid fa-power-off"></i>

Power

</span>

<span class="font-bold">

{{$ac->status->power}}

</span>

</div>


<div class="bg-blue-100 text-blue-700 p-3 rounded-xl mb-3 flex justify-between">

<span class="flex items-center gap-2">

<i class="fa-solid fa-temperature-half"></i>

Temperature

</span>

<span class="font-bold">

{{$ac->status->set_temperature}}°C

</span>

</div>


<div class="bg-purple-100 text-purple-700 p-3 rounded-xl flex justify-between">

<span class="flex items-center gap-2">

<i class="fa-solid fa-fan"></i>

Mode

</span>

<span class="font-bold">

{{$ac->status->mode}}

</span>

</div>

@else

<div class="bg-gray-100 text-gray-500 p-3 rounded-xl text-center">

No Status Data

</div>

@endif

</div>

@endforeach

</div>

</div>

</div>

</body>
</html>
