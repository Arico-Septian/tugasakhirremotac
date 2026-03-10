<!DOCTYPE html>
<html>
<head>

<title>Room Management</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="flex">

<!-- SIDEBAR -->

<div class="w-64 bg-white h-screen shadow-lg p-6">

<h2 class="text-xl font-bold mb-8 text-blue-600">
Centralized AC
</h2>

<ul class="space-y-4">

<li class="text-gray-600 hover:text-blue-600">
<a href="/dashboard">Dashboard</a>
</li>

<li class="font-semibold text-blue-600">
Manage Rooms
</li>

<li>
<a href="/logout" class="text-red-500">Logout</a>
</li>

</ul>

</div>


<!-- MAIN CONTENT -->

<div class="flex-1 p-10">

<div class="flex justify-between items-center mb-8">

<h1 class="text-2xl font-bold">
Room Management
</h1>

<button onclick="openModal()"
class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700">

+ Add Room

</button>

</div>


<!-- ROOM GRID -->

<div class="grid grid-cols-3 gap-6">

@foreach($rooms as $room)

<a href="/rooms/{{$room->id}}/ac">

<div class="bg-white p-6 rounded-xl shadow hover:shadow-xl transition">

<h2 class="text-lg font-bold mb-2">
{{$room->name}}
</h2>

<p class="text-gray-500 mb-4">
{{$room->acUnits->count()}} AC Units
</p>

<div class="bg-green-100 text-green-600 p-2 rounded">

View Details

</div>

</div>

</a>

@endforeach

</div>

</div>

</div>


<!-- MODAL ADD ROOM -->

<div id="modal"
class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

<div class="bg-white p-6 rounded-xl w-96">

<h2 class="text-xl font-bold mb-4">
Add Room
</h2>

<form method="POST" action="/rooms">

@csrf

<input type="text"
name="name"
placeholder="Room Name"
class="border p-2 w-full mb-4 rounded">

<button class="bg-blue-600 text-white px-4 py-2 rounded w-full">
Add Room
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
