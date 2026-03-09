<h1>AC Units Control</h1>

@foreach($rooms as $room)

<h2>{{ $room->name }}</h2>

@foreach($room->acUnits as $ac)

<div>

AC {{ $ac->ac_number }}

<a href="/ac-on/{{ $room->name }}/{{ $ac->ac_number }}">ON</a>

<a href="/ac-off/{{ $room->name }}/{{ $ac->ac_number }}">OFF</a>

</div>

@endforeach

@endforeach
