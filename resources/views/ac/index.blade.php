<!DOCTYPE html>
<html>
<head>

<title>AC Units</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gradient-to-br from-blue-50 to-purple-100 min-h-screen">

<div class="flex">

<!-- SIDEBAR -->

<div class="w-64 bg-white h-screen shadow-lg p-6">

<h2 class="text-xl font-bold mb-8 text-blue-600">
Centralized AC
</h2>

<ul class="space-y-5">

<li>
<a href="/dashboard" class="text-gray-600">Dashboard</a>
</li>

<li>
<a href="/rooms" class="text-gray-600">Manage Rooms</a>
</li>

<li class="font-semibold text-blue-600">
AC Units Control
</li>

<li>
<a href="/logout" class="text-red-500">Logout</a>
</li>

</ul>

</div>


<!-- MAIN CONTENT -->

<div class="flex-1 p-10">

<div class="flex justify-between items-center mb-10">

<h1 class="text-3xl font-bold text-gray-800">
AC Units - {{$room->name}}
</h1>

<button onclick="openModal()"
class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2 rounded-lg shadow-lg hover:scale-105 transition">

+ Add AC

</button>

</div>


<!-- AC GRID -->

<div class="grid grid-cols-3 gap-8">

@foreach($acs as $ac)

<div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">

<div class="flex justify-between mb-3">

<h2 class="text-lg font-bold">
AC {{$ac->ac_number}}
</h2>

<div class="text-blue-500 text-xl">
❄️
</div>

</div>

<p class="text-gray-500 mb-3">
Brand : {{$ac->brand}}
</p>

<!-- STATUS -->

<div class="mb-4">

@if($ac->status && $ac->status->power == 'ON')

<span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg text-sm">
● ON
</span>

@else

<span class="bg-gray-200 text-gray-600 px-3 py-1 rounded-lg text-sm">
● OFF
</span>

@endif

</div>


<!-- POWER -->

<div class="flex gap-2 mb-3">

<a href="/ac/{{$ac->id}}/on"
class="bg-green-500 text-white px-3 py-1 rounded">

ON

</a>

<a href="/ac/{{$ac->id}}/off"
class="bg-red-500 text-white px-3 py-1 rounded">

OFF

</a>

</div>


<!-- TEMPERATURE -->

<div class="flex gap-2 mb-3">

<a href="/ac/{{$ac->id}}/temp/22"
class="bg-blue-500 text-white px-3 py-1 rounded">
22°
</a>

<a href="/ac/{{$ac->id}}/temp/24"
class="bg-blue-500 text-white px-3 py-1 rounded">
24°
</a>

<a href="/ac/{{$ac->id}}/temp/26"
class="bg-blue-500 text-white px-3 py-1 rounded">
26°
</a>

</div>


<!-- MODE -->

<div class="flex gap-2">

<a href="/ac/{{$ac->id}}/mode/cool"
class="bg-purple-500 text-white px-3 py-1 rounded">
Cool
</a>

<a href="/ac/{{$ac->id}}/mode/fan"
class="bg-purple-500 text-white px-3 py-1 rounded">
Fan
</a>

<a href="/ac/{{$ac->id}}/mode/auto"
class="bg-purple-500 text-white px-3 py-1 rounded">
Auto
</a>

</div>

<div class="mt-4">

<form action="/ac/{{$ac->id}}" method="POST">

@csrf
@method('DELETE')

<button
onclick="return confirm('Delete this AC?')"
class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">

Delete AC

</button>

</form>

</div>

</div>

@endforeach

</div>

</div>

</div>


<!-- MODAL ADD AC -->

<div id="modal"
class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

<div class="bg-white p-8 rounded-2xl w-96 shadow-xl">

<h2 class="text-xl font-bold mb-5">
Add New AC
</h2>

<form method="POST" action="/rooms/{{$room->id}}/ac">

@csrf

<input
type="number"
name="ac_number"
placeholder="AC Number"
class="border p-3 w-full mb-3 rounded-lg">

<input
type="text"
name="name"
placeholder="AC Name"
class="border p-3 w-full mb-3 rounded-lg">

<input
type="text"
name="brand"
placeholder="Brand"
class="border p-3 w-full mb-3 rounded-lg">

<button
class="bg-blue-600 text-white w-full py-2 rounded-lg">

Create AC

</button>

</form>

</form>

</div>

</div>


<script>

function openModal(){
document.getElementById('modal').classList.remove('hidden')
}

</script>

</body>
</html>
