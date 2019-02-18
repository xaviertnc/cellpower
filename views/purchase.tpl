@extends('layout.tpl')

@section('main-content')

<h2>Pre-vend Info</h2>

<hr>

@if(isset($session['arrears']))
<div class="row"><div class="col-xs-3"><b>ARREARS:</b></div><div class="col-xs-9">{{ $session['arrears'] }}</div></div>
@endif

@if(isset($session['min']))
<div class="row"><div class="col-xs-3"><b>Min:</b></div><div class="col-xs-9">{{ $session['min'] }}</div></div>
@endif

<div class="row"><div class="col-xs-3"><b>Max:</b></div><div class="col-xs-9">{{ $session['max'] }}</div></div>
<div class="row"><div class="col-xs-3"><b>Customer:</b></div><div class="col-xs-9">{{ $session['customer_name'] }}</div></div>
<div class="row"><div class="col-xs-3"><b>Meter:</b></div><div class="col-xs-9">{{ $session['meter'] }}</div></div>
<div class="row"><div class="col-xs-3"><b>TransID:</b></div><div class="col-xs-9">{{ $session['pre_trxid'] }}</div></div>
<br>
@if( empty($session['last_purchase']) or $session['last_purchase'] <= 3)
<div class="row">
	<div class="col-xs-3"><b>Last:</b></div><div class="col-xs-9">{{ $session['last_purchase'] }} days ago</div>
</div>
<div class="row" style="background-color:red; color:yellow">
	<input type="hidden" name="must_confirm_days" value="true">
	<input type="checkbox" name="days_confirmed"> Yes, I know the last purchase was less than 3 day ago! Please continue anyway.
</div>
@else
<div class="row"><div class="col-xs-3"><b>Last:</b></div><div class="col-xs-9">{{ $session['last_purchase'] }} days ago</div></div>
@endif
<hr>

<h2>Cellpower Buy Electricity</h2>

<hr>

<form id="purchase" title="Go" method="post">

	@if(array_get($_GET, 'v')) <input type="hidden" name="v" value="1"> @endif

	<div class="form-group row">
		<label class="control-label col-md-3" for="amount">Amount</label>
		<div class="col-md-3" id="amount-field">
			<div class="input-group">
				<span class="input-group-addon in">R</span>
				<input type="text" class="form-control" id="amount" name="amount" value="{{ array_get($session, 'amount') }}" autocomplete="off">
			</div>
		</div>
	</div>

	<div class="form-group row">
		<label class="control-label col-md-3" for="customer-cell">SMS Cell (Optional)</label>
		<div class="col-md-3">
			<input type="text" class="form-control col-md-1" id="customer-cell" name="customer-cell" value="{{ array_get($session, 'customer_cell') }}">
		</div>
	</div>

	<div class="btn-group">
		<button class="btn btn-primary" type="submit" value="Go">Buy</button>
	</div>
</form>

<br>

<p>You will be charged a levy of <b>R{{ $session['sms_cost'] }}</b> for a customer SMS.</p>

<div class="well" style="font-size:1.2em"><b>Balance:</b> R{{ $session['pre_balance'] }}</div>

<a class="btn btn-danger" href="?r=abort"{{ $cancel_script }}>Cancel</a><br>

<br>

@stop

@section('scripts')
$('#purchase').isHappy({
	fields: {
		'#amount': {
			required: true,
			clean: function(val) { return val.replace(/[^0-9\.]+/g, ''); }
		},
		'#customer-cell': {
			clean: function(val) { return val.replace(/[^0-9]+/g, ''); }
		}
	},
	unHappy: function() {
		var isUnhappy = $('#amount_unhappy');
		if(isUnhappy.length)
		{
			$('#amount-field').append('<span class="text-danger">Amount is Required!</span>');
		}
	},
	classes: {
		message: 'text-danger'
	}
})
.click(function(){
	$('.text-danger').remove();
});

@stop
