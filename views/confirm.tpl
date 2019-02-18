@extends('layout.tpl')

@section('main-content')

<h2>Confirm Purchase</h2>
<hr>

{{ array_get($session, 'summary', 'Undefined') }}

<br>
<br>

@if(isset($session['summary']))
<form method="post">
	<div class="btn-group">
		<button class="btn btn-primary" type="submit" value="Confirm">Confirm</button>
	</div>
</form>
@endif

<br>

<a class="btn btn-danger" href="?r=abort"{{ $cancel_script }}>Cancel</a><br>

<br>

@stop
