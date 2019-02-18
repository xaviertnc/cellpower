@extends('layout.tpl')

@section('styles')
<style>
	printclass { display: none; }
	printclass1 { display: inline; }
	@media print { .printclass { display: block; } .printclass1 { display: none; } }
</style>
@stop


@section('main-content')

<h2>Token(s) for meter: <b>{{ array_get($session, 'meter', 'Undefined') }}{{ __TESTING__ ? ' - TEST' : '' }}</b></h2>

<hr>

<p>
	<b>Amount tendered:</b><br>
	R{{ currency(array_get($session, 'amount', 0)) }}
</p>

{{ array_get($session, 'voucher_text', 'Undefined') }}

<hr>

<form class="btn-toolbar">
	<a class="btn btn-info pull-right" href="?r=abort"{{ $cancel_script }}>@if($cancel_script) Close @else Home @endif</a>
	<a class="btn btn-primary pull-right" onclick="window.print();" style="margin-right:7px;">Print</a>
</form>

<br>

@stop