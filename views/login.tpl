@extends('layout.tpl')

@section('main-content')

<h2>Cellpower Login</h2>

<hr>

<form id="login" title="Login" method="post">
	<div class="form-group">
		<label class="control-label" for="account">Cellpower Account</label>
		<select class="form-control" name="account" id="account">
		  <option value="">- Select Account -</option>
		  @foreach($accounts as $number)
		  <option value="{{ $number }}">{{ $number }}</option>
		  @endforeach
		</select>
	</div>
	<div class="form-group">
		<label class="control-label" for="user">Mr Prepaid User</label>
		<select class="form-control" name="user" id="user">
		  <option value="">- Select User -</option>
		  @foreach($users as $id=>$name)
		  <option value="{{ $id }}">{{ $name }}</option>
		  @endforeach
		</select>
	</div>
	<div class="form-group">
		<label class="control-label" for="password">Mr Prepaid Password</label>
		<input type="password" class="form-control" id="password" name="password">
	</div>		  
	<br>
	<button class="btn btn-primary" type="submit">Login</button>
{*	<a href="?r=test/sendmail" class="btn btn-info pull-right" type="submit">Send Test Mail</a>*}
{*	<a href="?r=test/arrayget" class="btn btn-info pull-right" type="submit">Test array_get()</a> *}
</form>

<br>

@stop

@section('scripts')
$('#login').isHappy({ 
	fields: { 
		'#account': { 
			required: true,
			message: 'Please select an account to use',
			test: function(val) { return (val > 0); }
		},
		'#user': { 
			required: true,
			message: 'Username is required',
			test: function(val) { return (val > 0); }
		},
		'#password': {
			required: true,
			message: 'Password is required!'
		}
	},
	classes: {
		message: 'text-danger'
	}
});
@stop