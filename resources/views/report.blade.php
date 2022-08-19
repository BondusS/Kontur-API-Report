<!DOCTYPE html>
<html>
@foreach(explode("|", $text) as $tx)
	{{$tx}}<br>
@endforeach
</html>