<!DOCTYPE html>
<html>
<head>

<title>AC Status</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gradient-to-br from-blue-50 to-purple-100 min-h-screen">

<div class="p-10">

<h1 class="text-3xl font-bold mb-10">

AC Status - {{$room->name}}

</h1>


<div class="grid grid-cols-3 gap-8">

@foreach($acs as $ac)

<div class="bg-white p-6 rounded-2xl shadow-lg">

<div class="flex justify-between mb-4">

<h2 class="text-lg font-bold">

AC {{$ac->ac_number}}

</h2>

❄️

</div>

<p class="text-gray-500 mb-3">

Brand : {{$ac->brand}}

</p>

@if($ac->status)

<div class="bg-green-100 text-green-700 p-2 rounded-lg mb-2 flex justify-between">

<span>Power</span>

<span>{{$ac->status->power}}</span>

</div>

<div class="bg-blue-100 text-blue-700 p-2 rounded-lg mb-2 flex justify-between">

<span>Temperature</span>

<span>{{$ac->status->set_temperature}}°C</span>

</div>

<div class="bg-purple-100 text-purple-700 p-2 rounded-lg flex justify-between">

<span>Mode</span>

<span>{{$ac->status->mode}}</span>

</div>

@else

<div class="bg-gray-100 text-gray-500 p-2 rounded-lg">

No Status Data

</div>

@endif

</div>

@endforeach

</div>

</div>

</body>
</html>
