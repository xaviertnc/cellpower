<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
	</head>
	<body>
		<table class='voucher'>
			<tr>
				<td>{{ $header_image }}</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td class='heading' style="font-size:1.67em; font-weight:bold;">Token(s) for meter: {{ $meter }}</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
					@foreach($tokens as $n => $token)
					<p>
						<b>TOKEN {{ ($n + 1) }}</b><br/>
						<font class='voucher'>{{ $token }}</font><br/>
						<b>Units:</b><br/>
						{{ $units[$n] }}<br/>
						<b>Value:</b><br/>
						R{{ $values[$n] }}<br/>
					</p>
					@endforeach
					<p>
						<b>Purchase amount:</b><br/>
						R{{ $amount }}
					</p>
					<p>
						<b>VAT:</b><br/>
						R{{ $vat }}
					</p>
					<p>
						<b>Transaction ID:</b><br/>
						{{ $trxid }}
					</p>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>{{ $footer_image }}</td>
			</tr>
		</table>
	</body>
</html>