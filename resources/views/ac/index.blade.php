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

<a href="/ac/{{$ac->id}}/on">Turn ON</a>

<a href="/ac/{{$ac->id}}/off">Turn OFF</a>

</td>

</tr>

@endforeach

</table>
