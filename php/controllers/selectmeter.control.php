<?php

/** 
 * Purchase Pages - Page 1
 * Select Meter
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 *
 */

do {

//----------------------------
//--- Process GET Request ---
//----------------------------
if(!$session->has('user_id'))
{
	$data = array('message' => 'Invalid or expired session.');
	$view = 'error.tpl';
	break;	
}
	
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$data = array('session' => $session->all());
	$view = 'selectmeter.tpl';
	break;
}	

//----------------------------
//--- Process POST Request ---
//----------------------------
include __LIB__ . '/onefile/curl.php';
include __LIB__ . '/simplehtmldom/simple_html_dom.php';

//Save critical stuff before clearing the session
$user = $session->get('user');
$user_id = $session->get('user_id');
$account = $session->get('account');
$meterno = array_get($_POST, 'meter');

//Used when sending request and restore session after clear!
$cellpower_session = $session->get('cellpower_session');

//We need to clear to forget all the values of the previous meter!
//Clear MUST be before loading the prevend page since we set some session values
//which will be lost if we clear after.

$session->clear();

//Send meter select request + Collect prevend info from prevend response
include __SNIPPETS__ . '/postMeterGetPrevend.snp.php';

if($has_error)
{
	$data = array('message' => 'ERROR', 'html' => $error_html);
	$view = 'error.tpl';
	break;	
}

//Restore critical values after clearing the session
$session->put('user', $user);
$session->put('user_id', $user_id);
$session->put('account', $account);
$session->put('cellpower_session', $cellpower_session);
$session->put('meter', $meterno);

redirect('?r=purchase');

} while(0);
