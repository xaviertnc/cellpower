@extends('layout.tpl')

@section('main-content')

<h2>Select Meter</h2>

<hr>

<br>

<form id="check" title="Check" method="post">
	<div class="form-group row">
		<label class="control-label col-md-2" for="meter">Meter Number</label>
		<div class="col-md-3">
			  <input type="text" class="form-control" id="meter" name="meter" size="11" autocomplete="off">
		</div>
	</div>

	<br>

	<div class="btn-group">
		<button class="btn btn-primary" type="submit" value="Go">Go</button>
	</div>
</form>

<br>

<a class="btn btn-danger" href="?r=menu">Cancel</a><br>

<br>

<p>Please enter the meter number (11 digits starting with 070), and select 'Go'</p>

@stop

@section('scripts')
$('#check').isHappy({
	fields: {
		'#meter': {
			required: true,
			message: 'Meter Number is Required!',
			clean: function(val) { return val.replace(/[^0-9]+/g, ''); }
		}
	},
	classes: {
		message: 'text-danger'
	}
});
@stop
