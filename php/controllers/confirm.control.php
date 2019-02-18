<?php

/**
 * Purchase Pages - Page 3
 * Confirm Purchase Request Details
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 *
 */

do {

	$log->test('Confirm Controller says HELLO.');
	
	if ( ! $session->has('user_id'))
	{
		$data = array('message' => 'Invalid or expired session.');
		$view = 'error.tpl';
		$message = 'Confirm Controller: Invalid or expired session! Show Error Page.';
		$log->error($message);
		$log->test($message);		
		break;
	}


	//----------------------------
	//--- Process GET Request ---
	//----------------------------
	if ($_SERVER['REQUEST_METHOD'] === 'GET')
	{
		$log->test('Confirm REQUEST = GET');
		
		$version = array_get($_GET, 'v');

		if ($version == 1)
		{
			$cancel_script = ' onclick="window.close(); return false;"';
		}
		else
		{
			$cancel_script = '';
		}

		$account = $session->get('account');

		$data = array(
			'session' => $session->all(),
			'pin' => $config->get("accounts.$account"),
			'cancel_script' => $cancel_script
		);

		$view = 'confirm.tpl';
		break;
	}


	$log->test('Confirm REQUEST = POST');

	//----------------------------
	//--- Process POST Request ---
	//----------------------------
	include __LIB__ . '/onefile/curl.php';
	include __LIB__ . '/simplehtmldom/simple_html_dom.php';

	//Save critical stuff before clearing the session
	$user = $session->get('user');
	$user_id = $session->get('user_id');
	$account = $session->get('account');
	$amount = $session->get('amount');
	$meterno = $session->get('meter');
	$customer_cell = $session->get('customer_cell');

	//Used when sending request and restore session after clear!
	$cellpower_session = $session->get('cellpower_session');

	//Cellpower POST Confirm Purchase + GET Token Voucher Information Page
	include __SNIPPETS__ . '/postConfirmGetVoucher.snp.php';

	if ($has_error)
	{
		$data = array('message' => 'ERROR', 'html' => $error_html);
		$view = 'error.tpl';
		$log->error('Confirm Controller: POST request + Get Token Voucher Info Failed! Cellpower Error.');
		$log->test('Confirm POST failed to return Voucher Token! Cellpower Error.');
		break;
	}

	if (array_get($_POST, 'v'))
	{
		redirect('?r=voucher&v=1');
	}
	else
	{
		redirect('?r=voucher');
	}
	
} while (0);
