<?php
/** 
 * The Session Contents of a Completed Transaction Just Before Calling the Voucher Controller.
 * 
 * For Testing Purposes.
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 05 Aug 2014
 *
 */

//Replace the php session with the stub values below...

/* @var $session \OneFile\Session */
$session->replace( array(
	'user' => 'Generic User',
	'user_id' => '999',
	'meter' => '07058390175',
	'customer_cell' => '0826941555',
	'email' => 'lynnetm@tnc-it.za',
	'account' => '0718730764',
	'amount' => '1000',
	'cellpower_session' => 'WQNKDhuF',
	'min' => 'R0.17',
	'max' => 'no limit',
	'arrears' => '',
	'customer_name' => 'MOKWENA L M',
	'last_purchase' => '13',
	'sms_cost' => '0.20',
	'pre_balance' => '324306.76',
	'pre_trxid' => '36366436',
	'summary' =>  'You have requested a purchase of <b>R1000 </b>for meter <b>07058390175</b>. <br/> This will be broken down as follows:<br/> <b>Electricity:</b><br/>R1000.00<br/><b>Total:</b><br/>R1000.00<br/><b>VAT:</b><br/>R0.00<br/>',
	'tokens' => array('1199-8521-1049-1187-1242'),
	'units' => array('671.90'),
	'values' => array('999.80'),
	'purchase_amount' => '999.80',
	'vat' => '122.78',
	'sms' => '0.20',
	'profit' => '43.85',
	'balance' => '323350.61',
	'trxid' => '36366441',
	'voucher_text' => "<p><b>TOKEN 1</b><br/><font class='voucher'>1199-8521-1049-1187-1242</font><br/>Units:<br/>671.90<br/>Value:<br/>R999.80<br/></p><p><b>Purchase amount:</b><br/>R999.80</p><p><b>VAT:</b><br/>R122.78</p><p><b>SMS cost(incl):</b><br/>R0.20</p><div style='background-color:#C0C0C0; padding:2px;' class='printclass1'><b>Profit: </b>R43.85</div><div style='background-color:#C0C0C0; padding:2px;' class='printclass1'><b>Balance: </b>R323350.61</div><p><b>Transaction ID:</b><br/>36366441</p>",
));
