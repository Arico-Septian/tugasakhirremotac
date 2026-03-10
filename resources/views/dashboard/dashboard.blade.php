<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Centralized AC Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ===== GLOBAL ===== */

body{
font-family: ui-sans-serif,system-ui;
}

/* ===== SIDEBAR ===== */

.sidebar{
transition:all .3s ease;
}

.sidebar.close{
width:80px;
}

.sidebar.close .menu-text{
display:none;
}

.sidebar.close h2 span{
display:none;
}

.sidebar.close ul li a{
justify-content:center;
}

/* ===== CONTENT SHIFT ===== */

.main-content{
margin-left:260px;
transition:all .3s ease;
}

.sidebar.close + .main-content{
margin-left:100px;
}

/* ===== CARD STYLE ===== */

.stat-card{
background:white;
border-radius:18px;
padding:24px;
box-shadow:0 8px 20px rgba(0,0,0,0.05);
transition:all .25s ease;
}

.stat-card:hover{
transform:translateY(-4px);
box-shadow:0 15px 35px rgba(0,0,0,0.08);
}

/* ===== ROOM CARD ===== */

.room-card{
background:white;
border-radius:20px;
padding:24px;
box-shadow:0 8px 20px rgba(0,0,0,0.05);
transition:all .25s ease;
}

.room-card:hover{
transform:translateY(-6px);
box-shadow:0 20px 40px rgba(0,0,0,0.08);
}

/* ===== ICON BOX ===== */

.icon-box{
width:40px;
height:40px;
border-radius:10px;
display:flex;
align-items:center;
justify-content:center;
background:#f3f4f6;
}

/* ===== MOBILE ===== */

@media(max-width:900px){

.main-content{
margin-left:0;
}

.sidebar{
transform:translateX(-100%);
position:fixed;
}

.sidebar.open{
transform:translateX(0);
}

}

</style>

</head>

<body class="bg-gray-50">


<!-- SIDEBAR -->

<div id="sidebar"
class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r z-50">

<div class="flex justify-between items-center pb-5 mb-8 border-b">

<h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">

<i class="fa-solid fa-layer-group"></i>
<span class="menu-text">AC System</span>

</h2>

<button onclick="toggleSidebar()" class="text-gray-500 hover:text-blue-500">
<i class="fa-solid fa-bars"></i>
</button>

</div>

<ul class="space-y-3">

<li>

<a href="/dashboard"
class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold hover:bg-blue-100">

<i class="fa-solid fa-chart-pie"></i>
<span class="menu-text">Dashboard</span>

</a>

</li>

<li>

<a href="/rooms"
class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">

<i class="fa-solid fa-server"></i>
<span class="menu-text">Manage Rooms</span>

</a>

</li>

</ul>

</div>



<!-- MAIN -->

<div class="main-content min-h-screen flex flex-col">


<!-- HEADER -->

<header class="sticky top-0 bg-white border-b px-8 py-5 flex justify-between items-center">

<div class="flex items-center gap-4">

<button class="lg:hidden text-xl" onclick="toggleSidebar()">
<i class="fa-solid fa-bars"></i>
</button>

<h1 class="text-2xl font-bold text-gray-800">
Centralized AC Management
</h1>

</div>


<div class="flex items-center gap-6">

<span id="systemStatus" class="text-green-500 text-sm font-semibold">
● System Online
</span>


<div class="relative">

<button onclick="toggleProfile()"
class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-bold">

A

</button>

<div id="profileMenu"
class="hidden absolute right-0 mt-3 w-40 bg-white shadow rounded-xl p-2">

<a href="/logout"
class="flex items-center gap-2 text-red-500 hover:bg-red-50 p-2 rounded">

<i class="fa-solid fa-right-from-bracket"></i>
Logout

</a>

</div>

</div>

</div>

</header>



<!-- CONTENT -->

<div class="p-8">


<!-- STATISTICS -->

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">


<div class="stat-card">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500 text-sm">Total Rooms</p>
<h2 class="text-3xl font-bold">{{$rooms->count()}}</h2>

</div>

<div class="icon-box text-blue-500">
<i class="fa-solid fa-server"></i>
</div>

</div>

</div>



<div class="stat-card">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500 text-sm">Total AC Units</p>
<h2 class="text-3xl font-bold">{{$totalAc}}</h2>

</div>

<div class="icon-box text-indigo-500">
<i class="fa-solid fa-snowflake"></i>
</div>

</div>

</div>



<div class="stat-card">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500 text-sm">Active AC Units</p>
<h2 class="text-3xl font-bold">{{$activeAc}}</h2>

</div>

<div class="icon-box text-green-500">
<i class="fa-solid fa-wind"></i>
</div>

</div>

</div>



<div class="stat-card">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500 text-sm">Total Users</p>
<h2 class="text-3xl font-bold">{{$users}}</h2>

</div>

<div class="icon-box text-purple-500">
<i class="fa-solid fa-users"></i>
</div>

</div>

</div>



<div class="stat-card">

<div class="flex justify-between items-center">

<div>

<p class="text-gray-500 text-sm">Users Online</p>
<h2 class="text-3xl font-bold">1</h2>

</div>

<div class="icon-box text-orange-500">
<i class="fa-solid fa-user-check"></i>
</div>

</div>

</div>

</div>



<!-- SERVER ROOMS -->

<h2 class="text-2xl font-bold mb-6 text-gray-800">
Server Rooms
</h2>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

@foreach($rooms as $room)

<div class="room-card">

<div class="flex justify-between mb-3">

<h3 class="font-semibold text-lg">{{$room->name}}</h3>

<i class="fa-solid fa-server text-gray-400"></i>

</div>


<p class="text-gray-500 text-sm mb-4">
Total : {{$room->acUnits->count()}} units
</p>


<div class="bg-green-50 text-green-700 p-3 rounded-lg mb-2 flex justify-between text-sm">

<span>Active Units</span>

<span class="font-semibold">
{{ $room->acUnits->where('status.power','ON')->count() }}
</span>

</div>


<div class="bg-gray-100 text-gray-600 p-3 rounded-lg mb-4 flex justify-between text-sm">

<span>Inactive Units</span>

<span class="font-semibold">
{{ $room->acUnits->where('status.power','OFF')->count() }}
</span>

</div>


<a href="/rooms/{{$room->id}}/status">

<button
class="w-full py-2 rounded-lg bg-gray-900 text-white hover:bg-black transition">

View Details

</button>

</a>

</div>

@endforeach

</div>

</div>

</div>



<script>

function toggleSidebar(){

let sidebar=document.getElementById("sidebar")

sidebar.classList.toggle("close")
sidebar.classList.toggle("open")

}

function toggleProfile(){

document.getElementById("profileMenu").classList.toggle("hidden")

}

/* ===============================
   SYSTEM STATUS DASHBOARD
================================ */

function updateStatusOnline(){

let status=document.getElementById("systemStatus")

status.innerHTML="● System Online"
status.className="text-green-500 text-sm font-semibold"

}

function updateStatusOffline(){

let status=document.getElementById("systemStatus")

status.innerHTML="● System Offline"
status.className="text-red-500 text-sm font-semibold"

}

/* cek koneksi browser */

window.addEventListener("online",updateStatusOnline)
window.addEventListener("offline",updateStatusOffline)

/* cek koneksi server */

function checkServer(){

if(!navigator.onLine){

updateStatusOffline()
return

}

fetch('/system-check',{cache:"no-store"})
.then(res=>{

if(res.ok){

updateStatusOnline()

}else{

updateStatusOffline()

}

})
.catch(()=>{

updateStatusOffline()

})

}

/* cek setiap 5 detik */

setInterval(checkServer,5000)

checkServer()

</script>

</body>
</html>
