<h1>AC Units - {{$room->name}}</h1>

<a href="/rooms">Back</a>

<h3>Add AC</h3>

<form method="POST" action="/rooms/{{$room->id}}/ac">

@csrf

<input type="number" name="ac_number" placeholder="AC Number">

<input type="text" name="name" placeholder="AC Name">

<input type="text" name="brand" placeholder="Brand">

<button type="submit">Add AC</button>

</form>

<br>

<table border="1">

<tr>
<th>ID</th>
<th>Name</th>
<th>Brand</th>
<th>Control</th>
</tr>

@foreach($acs as $ac)

<tr>

<td>{{$ac->id}}</td>
<td>{{$ac->name}}</td>
<td>{{$ac->brand}}</td>

<td>

<!-- POWER -->
<a href="/ac/{{$ac->id}}/on">ON</a>

<a href="/ac/{{$ac->id}}/off">OFF</a>

<br><br>

<!-- TEMPERATURE -->
Temp:

<a href="/ac/{{$ac->id}}/temp/20">20</a>

<a href="/ac/{{$ac->id}}/temp/22">22</a>

<a href="/ac/{{$ac->id}}/temp/24">24</a>

<a href="/ac/{{$ac->id}}/temp/26">26</a>

<br><br>

<!-- MODE -->

Mode:

<a href="/ac/{{$ac->id}}/mode/cool">Cool</a>

<a href="/ac/{{$ac->id}}/mode/heat">Heat</a>

<a href="/ac/{{$ac->id}}/mode/fan">Fan</a>

<a href="/ac/{{$ac->id}}/mode/auto">Auto</a>

</td>

</tr>

@endforeach

</table>
