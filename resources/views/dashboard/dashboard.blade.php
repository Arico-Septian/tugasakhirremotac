<!DOCTYPE html>
<html>
<head>

<title>AC Dashboard</title>

<style>

body{
font-family:Arial;
background:#f5f6fa;
padding:40px;
}

.room{
margin-bottom:40px;
}

.ac-grid{
display:flex;
gap:20px;
flex-wrap:wrap;
}

.ac-card{

background:white;
border-radius:10px;
padding:20px;
width:260px;
box-shadow:0 3px 10px rgba(0,0,0,0.1);

}

button{

padding:8px 12px;
border:none;
border-radius:5px;
cursor:pointer;

}

.on{background:green;color:white;}
.off{background:red;color:white;}

.mode{background:#3498db;color:white;}

</style>

</head>

<body>

<h1>Centralized AC Dashboard</h1>

<br>

<a href="/rooms">
<button style="padding:10px 15px;background:#2ecc71;color:white;border:none;border-radius:5px;cursor:pointer;">
Add / Manage Room
</button>
</a>

<br><br>

@foreach($rooms as $room)

<div class="room">

<h2>{{$room->name}}</h2>

<a href="/rooms/{{$room->id}}/ac">
<button style="padding:6px 10px;background:#3498db;color:white;border:none;border-radius:5px;cursor:pointer;">
Manage AC
</button>
</a>

<br><br>

<div class="ac-grid">

@foreach($room->acUnits as $ac)

<div class="ac-card">

<h3>{{$ac->name}}</h3>

<p>
Status :
<b>{{$ac->status->power ?? 'OFF'}}</b>
</p>

<p>
Temp :
{{$ac->status->set_temperature ?? 24}} °C
</p>

<p>
Mode :
{{$ac->status->mode ?? 'AUTO'}}
</p>

<br>

<!-- POWER -->

<a href="/ac/{{$ac->id}}/on">
<button class="on">ON</button>
</a>

<a href="/ac/{{$ac->id}}/off">
<button class="off">OFF</button>
</a>

<br><br>

<!-- TEMP -->

Temperature

<br>

<a href="/ac/{{$ac->id}}/temp/20">20</a>

<a href="/ac/{{$ac->id}}/temp/22">22</a>

<a href="/ac/{{$ac->id}}/temp/24">24</a>

<a href="/ac/{{$ac->id}}/temp/26">26</a>

<br><br>

<!-- MODE -->

Mode

<br>

<a href="/ac/{{$ac->id}}/mode/cool">
<button class="mode">Cool</button>
</a>

<a href="/ac/{{$ac->id}}/mode/fan">
<button class="mode">Fan</button>
</a>

<a href="/ac/{{$ac->id}}/mode/auto">
<button class="mode">Auto</button>
</a>

<a href="/ac/{{$ac->id}}/mode/heat">
<button class="mode">Heat</button>
</a>

</div>

@endforeach

</div>

</div>

@endforeach

</body>
</html>
