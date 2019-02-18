<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
	</head>
	<body>
		<table class='voucher'>
			<tr>
				<td><img src="img/tshwane_logo.gif"></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td class='heading' style="font-size:1.67em; font-weight:bold;">Token(s) for meter: {{ $meter }}{{ __TESTING__ ? ' - TEST' : '' }}</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>		
					<p>
						<b>Amount tendered:</b><br>
						R{{ currency($amount) }}
					</p>					
					@foreach($tokens as $n => $token)
					<p>
						<b>TOKEN {{ ($n + 1) }}</b><br/>
						<font class='voucher'>{{ $token }}</font><br/>
						<b>Units:</b><br/>
						{{ round($units[$n], 2) }}<br/>
						<b>Value:</b><br/>
						R{{ currency($values[$n]) }}<br/>
					</p>
					@endforeach
					<p>
						<b>Purchase amount:</b><br/>
						R{{ currency($purchase_amount) }}
					</p>
					<p>
						<b>VAT:</b><br/>
						R{{ currency($vat) }}
					</p>
					@if($sms)
					<p>
						<b>SMS cost(incl):</b><br/>
						R{{ currency($sms) }}
					</p>
					@endif
					<p>
						<b>Transaction ID:</b><br/>
						{{ $trxid }}
					</p>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><img src="img/signature.jpg"></td>
			</tr>
		</table>
	</body>
</html>
