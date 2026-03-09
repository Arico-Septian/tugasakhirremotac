<h1>Room Management</h1>

<a href="/dashboard">Back Dashboard</a>

<br><br>

<h3>Add Room</h3>

<form method="POST" action="/rooms/add">

@csrf

<input type="text" name="name" placeholder="Room Name">

<button type="submit">Add Room</button>

</form>

<br><br>

<table border="1">

<tr>
<th>ID</th>
<th>Room Name</th>
<th>Action</th>
</tr>

@foreach($rooms as $room)

<tr>

<td>{{ $room->id }}</td>

<td>{{ $room->name }}</td>

<td>
<a href="/rooms/{{ $room->id }}/ac">Manage AC</a>
</td>

</tr>

@endforeach

</table>
