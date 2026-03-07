<!DOCTYPE html>
<html>
<head>
<title>AC Control Dashboard</title>

<style>

body{
font-family: Arial;
background:#f4f6f9;
padding:40px;
}

.card{
background:white;
padding:20px;
border-radius:10px;
width:300px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

button{
padding:10px 15px;
border:none;
border-radius:6px;
cursor:pointer;
margin:5px;
}

.on{background:#28a745;color:white;}
.off{background:#dc3545;color:white;}
.mode{background:#007bff;color:white;}
.temp{background:#ffc107;}

</style>

</head>

<body>

<h2>AC Control Dashboard</h2>

<div class="card">

<h3>Room : server1</h3>
<h4>AC ID : 1</h4>

<!-- POWER -->
<h4>Power</h4>

<a href="/ac-on/server1/1">
<button class="on">ON</button>
</a>

<a href="/ac-off/server1/1">
<button class="off">OFF</button>
</a>


<!-- MODE -->
<h4>Mode</h4>

<a href="/ac-mode/server1/1/COOL">
<button class="mode">COOL</button>
</a>

<a href="/ac-mode/server1/1/FAN">
<button class="mode">FAN</button>
</a>

<a href="/ac-mode/server1/1/DRY">
<button class="mode">DRY</button>
</a>

<a href="/ac-mode/server1/1/AUTO">
<button class="mode">AUTO</button>
</a>


<!-- TEMPERATURE -->
<h4>Temperature</h4>

<a href="/ac-temp/server1/1/18"><button class="temp">18°</button></a>
<a href="/ac-temp/server1/1/20"><button class="temp">20°</button></a>
<a href="/ac-temp/server1/1/22"><button class="temp">22°</button></a>
<a href="/ac-temp/server1/1/24"><button class="temp">24°</button></a>
<a href="/ac-temp/server1/1/26"><button class="temp">26°</button></a>

<a href="/logout">Logout</a>
</div>

</body>
</html>
