<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AC Units Control</title>

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
<a href="/dashboard" class="flex items-center gap-2 hover:text-blue-600">
<i class="fa-solid fa-chart-pie"></i>
Dashboard
</a>
</li>

<li>
<a href="/rooms" class="flex items-center gap-2 hover:text-blue-600">
<i class="fa-solid fa-server"></i>
Manage Rooms
</a>
</li>

<li>
<a href="/dashboard/ac-control"
class="flex items-center gap-2 text-blue-600 font-semibold">
<i class="fa-solid fa-snowflake"></i>
AC Units Control
</a>
</li>

<li>
<a href="/logout" class="flex items-center gap-2 text-red-500">
<i class="fa-solid fa-right-from-bracket"></i>
Logout
</a>
</li>

</ul>

</div>

<!-- MAIN CONTENT -->

<div class="flex-1 p-10">

<h1 class="text-3xl font-bold mb-10 text-gray-800">
AC Units Control Panel
</h1>


@foreach($rooms as $room)

<h2 class="text-xl font-bold mb-6 text-blue-600">
{{$room->name}}
</h2>

<div class="grid grid-cols-4 gap-6 mb-10">

@foreach($room->acUnits as $ac)

<div class="bg-white shadow-lg rounded-2xl p-6 hover:shadow-xl transition">

<div class="flex justify-between items-center mb-3">

<h3 class="font-bold text-lg">
AC {{$ac->ac_number}}
</h3>

<i class="fa-solid fa-snowflake text-blue-500 text-xl"></i>

</div>

<!-- STATUS -->

@if($ac->status && $ac->status->power == 'ON')

<span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg text-sm">
● ON
</span>

@else

<span class="bg-gray-200 text-gray-600 px-3 py-1 rounded-lg text-sm">
● OFF
</span>

@endif


<!-- BUTTONS -->

<div class="flex gap-3 mt-5">

<a href="/ac-on/{{$room->name}}/{{$ac->ac_number}}"
class="flex-1 text-center bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">

ON

</a>

<a href="/ac-off/{{$room->name}}/{{$ac->ac_number}}"
class="flex-1 text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600">

OFF

</a>

</div>

</div>

@endforeach

</div>

@endforeach


</div>

</div>

</body>
</html>
