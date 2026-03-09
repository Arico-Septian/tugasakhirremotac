<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title>Centralized AC Management</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="flex h-screen">

<!-- SIDEBAR -->

<div class="w-64 bg-white shadow-lg">

<div class="p-6 text-xl font-bold text-blue-600">
Centralized AC
</div>

<nav class="mt-6">

<a class="flex px-6 py-3 bg-blue-100 text-blue-600 font-medium">
Dashboard
</a>

<a class="flex px-6 py-3 hover:bg-gray-100">
AC Units Control
</a>

<a class="flex px-6 py-3 hover:bg-gray-100">
Temperature Server
</a>

<a class="flex px-6 py-3 hover:bg-gray-100">
User Management
</a>

<a class="flex px-6 py-3 hover:bg-gray-100">
Settings
</a>

</nav>

</div>


<!-- MAIN CONTENT -->

<div class="flex-1 flex flex-col">

<!-- TOPBAR -->

<div class="bg-white shadow p-4 flex justify-between items-center">

<h1 class="text-xl font-semibold">
Centralized AC Management System
</h1>

<div class="flex items-center space-x-4">

<span class="text-green-500 font-medium">
● Online
</span>

<div class="flex items-center space-x-2">

<div class="w-10 h-10 bg-blue-500 rounded-full"></div>

<div>
<p class="font-medium">Admin User</p>
<p class="text-sm text-gray-500">Administrator</p>
</div>

</div>

</div>

</div>


<!-- CONTENT -->

<div class="p-6">


<!-- STATISTICS -->

<div class="grid grid-cols-4 gap-6 mb-8">

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500">Total Rooms</p>
<p class="text-3xl font-bold">18</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500">Active AC Units</p>
<p class="text-3xl font-bold">12</p>
<p class="text-green-500 text-sm">+3 from last hour</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500">Total Users</p>
<p class="text-3xl font-bold">24</p>
<p class="text-green-500 text-sm">+3 new users</p>
</div>

<div class="bg-white p-5 rounded-xl shadow">
<p class="text-gray-500">Users Online</p>
<p class="text-3xl font-bold">8</p>
<p class="text-green-500 text-sm">2 admins online</p>
</div>

</div>



<!-- SERVER ROOMS -->

<h2 class="text-xl font-semibold mb-2">
Server Rooms
</h2>

<p class="text-gray-500 mb-6">
View AC status by server room
</p>


<div class="grid grid-cols-3 gap-6">

<!-- ROOM A -->

<div class="bg-white rounded-xl shadow p-5">

<h3 class="text-lg font-semibold mb-1">
Server Room A
</h3>

<p class="text-gray-500 mb-4">
Total: 3 units
</p>

<div class="bg-green-100 text-green-700 p-3 rounded mb-3 flex justify-between">
<span>Active Units</span>
<span>2</span>
</div>

<div class="bg-gray-100 p-3 rounded mb-4 flex justify-between">
<span>Inactive Units</span>
<span>1</span>
</div>

<button onclick="openRoom()"
class="w-full bg-blue-600 text-white py-2 rounded-lg">
View Details
</button>

</div>



<!-- ROOM B -->

<div class="bg-white rounded-xl shadow p-5">

<h3 class="text-lg font-semibold mb-1">
Server Room B
</h3>

<p class="text-gray-500 mb-4">
Total: 3 units
</p>

<div class="bg-green-100 text-green-700 p-3 rounded mb-3 flex justify-between">
<span>Active Units</span>
<span>3</span>
</div>

<div class="bg-gray-100 p-3 rounded mb-4 flex justify-between">
<span>Inactive Units</span>
<span>0</span>
</div>

<button onclick="openRoom()"
class="w-full bg-blue-600 text-white py-2 rounded-lg">
View Details
</button>

</div>



<!-- ROOM C -->

<div class="bg-white rounded-xl shadow p-5">

<h3 class="text-lg font-semibold mb-1">
Server Room C
</h3>

<p class="text-gray-500 mb-4">
Total: 3 units
</p>

<div class="bg-green-100 text-green-700 p-3 rounded mb-3 flex justify-between">
<span>Active Units</span>
<span>1</span>
</div>

<div class="bg-gray-100 p-3 rounded mb-4 flex justify-between">
<span>Inactive Units</span>
<span>2</span>
</div>

<button onclick="openRoom()"
class="w-full bg-blue-600 text-white py-2 rounded-lg">
View Details
</button>

</div>

</div>

</div>

</div>

</div>



<!-- MODAL ROOM -->

<div id="roomModal"
class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center">

<div class="bg-white w-4/5 rounded-2xl shadow-xl overflow-hidden">

<!-- HEADER -->

<div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-6 flex justify-between items-center">

<div>
<h2 class="text-xl font-semibold">Server Room A</h2>
<p class="text-sm">● 2 Active &nbsp;&nbsp; ● 1 Inactive</p>
</div>

<button onclick="closeRoom()" class="text-2xl">
×
</button>

</div>


<!-- AC UNITS -->

<div class="p-6 grid grid-cols-3 gap-6">


<!-- AC 1 -->

<div class="border rounded-xl p-4 bg-green-50">

<h3 class="font-semibold mb-2">AC Unit 01</h3>

<p class="text-green-600 mb-4">ONLINE</p>

<div class="bg-gray-100 p-2 rounded mb-2 flex justify-between">
<span>Status</span>
<span class="text-green-600">ON</span>
</div>

<div class="bg-blue-100 p-2 rounded mb-2 flex justify-between">
<span>Temperature</span>
<span>22°C</span>
</div>

<div class="bg-purple-100 p-2 rounded mb-2 flex justify-between">
<span>Mode</span>
<span>Cool</span>
</div>

<div class="bg-orange-100 p-2 rounded flex justify-between">
<span>Timer</span>
<span>Off</span>
</div>

</div>


<!-- AC 2 -->

<div class="border rounded-xl p-4 bg-green-50">

<h3 class="font-semibold mb-2">AC Unit 02</h3>

<p class="text-green-600 mb-4">ONLINE</p>

<div class="bg-gray-100 p-2 rounded mb-2 flex justify-between">
<span>Status</span>
<span class="text-green-600">ON</span>
</div>

<div class="bg-blue-100 p-2 rounded mb-2 flex justify-between">
<span>Temperature</span>
<span>23°C</span>
</div>

<div class="bg-purple-100 p-2 rounded mb-2 flex justify-between">
<span>Mode</span>
<span>Cool</span>
</div>

<div class="bg-orange-100 p-2 rounded flex justify-between">
<span>Timer</span>
<span>Off</span>
</div>

</div>


<!-- AC 3 -->

<div class="border rounded-xl p-4 bg-gray-100">

<h3 class="font-semibold mb-2">AC Unit 03</h3>

<p class="text-gray-500 mb-4">OFFLINE</p>

<div class="bg-gray-200 p-2 rounded mb-2 flex justify-between">
<span>Status</span>
<span>OFF</span>
</div>

<div class="bg-blue-100 p-2 rounded mb-2 flex justify-between">
<span>Temperature</span>
<span>26°C</span>
</div>

<div class="bg-purple-100 p-2 rounded mb-2 flex justify-between">
<span>Mode</span>
<span>Cool</span>
</div>

<div class="bg-orange-100 p-2 rounded flex justify-between">
<span>Timer</span>
<span>Off</span>
</div>

</div>

</div>

</div>

</div>



<!-- SCRIPT -->

<script>

function openRoom(){

document.getElementById("roomModal").classList.remove("hidden")
document.getElementById("roomModal").classList.add("flex")

}

function closeRoom(){

document.getElementById("roomModal").classList.remove("flex")
document.getElementById("roomModal").classList.add("hidden")

}

</script>


</body>

</html>
