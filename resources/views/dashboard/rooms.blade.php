<!DOCTYPE html>
<html>
<head>

<title>Room Management</title>

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

<li class="flex items-center gap-2 text-blue-600 font-semibold">

<i class="fa-solid fa-server"></i>

Manage Rooms

</li>

<li>
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
Room Management
</h1>

<button onclick="openModal()"
class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2 rounded-xl shadow-lg hover:scale-105 transition">

+ Add Room

</button>

</div>


<!-- ROOM GRID -->

<div class="grid grid-cols-3 gap-8">

@foreach($rooms as $room)

<a href="/rooms/{{$room->id}}/ac">

<div class="bg-white/80 backdrop-blur-xl shadow-xl rounded-3xl p-6 hover:shadow-2xl hover:scale-[1.02] transition border">

<div class="flex justify-between items-center mb-3">

<h2 class="text-xl font-bold">

{{$room->name}}

</h2>

<i class="fa-solid fa-server text-blue-500 text-xl"></i>

</div>

<p class="text-gray-500 mb-4">

{{$room->acUnits->count()}} AC Units

</p>

<div class="bg-blue-100 text-blue-600 p-3 rounded-xl text-center font-semibold">

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

<button
class="bg-gradient-to-r from-blue-500 to-purple-500 text-white py-2 rounded-lg w-full">

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
