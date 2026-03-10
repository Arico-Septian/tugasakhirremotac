<!DOCTYPE html>
<html>
<head>

<title>Manage Rooms</title>

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
<a href="/dashboard"
class="flex items-center gap-2 text-blue-600 bg-blue-100 p-2 rounded-lg">

Dashboard

</a>
</li>

<li class="font-semibold text-gray-700">
Manage Rooms
</li>

<li>
<a href="/ac-units" class="text-gray-600">
AC Units Control
</a>
</li>

<li>
<a href="/logout" class="text-red-500">
Logout
</a>
</li>

</ul>

</div>


<!-- MAIN CONTENT -->

<div class="flex-1 p-10">

<div class="flex justify-between items-center mb-10">

<h1 class="text-3xl font-bold text-gray-800">
Room Management
</h1>

<button onclick="openModal()"
class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2 rounded-lg shadow-lg hover:scale-105 transition">

+ Add Room

</button>

</div>


<!-- ROOM CARDS -->

<div class="grid grid-cols-3 gap-8">

@foreach($rooms as $room)

<div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">

<div class="flex justify-between mb-3">

<h2 class="text-lg font-bold">
{{$room->name}}
</h2>

<div class="text-blue-500 text-xl">
🖥️
</div>

</div>

<p class="text-gray-500 mb-4">
Total : {{$room->acUnits->count()}} units
</p>

<div class="bg-green-100 text-green-600 p-2 rounded-lg mb-3 flex justify-between">

<span>Active Units</span>

<span>
{{$room->acUnits->where('status','ON')->count()}}
</span>

</div>

<div class="bg-gray-100 text-gray-600 p-2 rounded-lg mb-5 flex justify-between">

<span>Inactive Units</span>

<span>
{{$room->acUnits->where('status','OFF')->count()}}
</span>

</div>

<a href="/rooms/{{$room->id}}/ac"
class="block text-center bg-gradient-to-r from-blue-500 to-purple-500 text-white py-2 rounded-lg">

View Details

</a>

<form action="/rooms/{{$room->id}}" method="POST" class="w-full">

@csrf
@method('DELETE')

<button
onclick="return confirm('Are you sure delete this room?')"
class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">

Delete

</button>

</form>

</div>

@endforeach

</div>

</div>

</div>


<!-- MODAL ADD ROOM -->

<div id="modal"
class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

<div class="bg-white p-8 rounded-2xl w-96 shadow-xl">

<h2 class="text-xl font-bold mb-5">
Add New Room
</h2>

<form method="POST" action="/rooms">

@csrf

<input type="text"
name="name"
placeholder="Room Name"
class="border p-3 w-full mb-4 rounded-lg">

<button class="bg-blue-600 text-white w-full py-2 rounded-lg">
Create Room
</button>

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
