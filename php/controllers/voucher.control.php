<?php

/** 
 * Purchase Pages - Page 4
 * Show token(s) voucher + Submit to System or Print
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 *
 */


/**
 * Replace number_format with shorted function and correct parameter defaults.
 * 
 */
function currency($number, $decimals = 2, $dec_point = '.', $thousands_sep = '')
{
	return number_format($number, $decimals, $dec_point, $thousands_sep);
}


do {
	
$log->test('Voucher Controller says HELLO.');

if(!$session->has('user_id'))
{
	$data = array('message' => 'Invalid or expired session.');
	$view = 'error.tpl';
	$message = 'Voucher Controller: Invalid or expired session! Show Error Page.';
	$log->error($message);
	$log->test($message);	
	break;
}


include __CONTROLLERS__ . '/submit.control.php';
//include __CONTROLLERS__ . '/vend/submit.control.php';


$version = array_get($_GET, 'v');

if($version == 1)
	$cancel_script = ' onclick="window.close(); return false;"';
else
	$cancel_script = '';

$data = array('session' => $session->all(), 'flash' => $flash, 'cancel_script' => $cancel_script);

$view = 'voucher.tpl';

} while(0);


//We are not going to redirect, so lets activate/consume any new flash messages now!
$flash->mergeNewItems();
