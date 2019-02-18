<?php

/**
 * Purchase Pages - Page 2
 * View Meter Info + Enter Purchase Amount
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 *
 */

do {
	
	$log->test('Purchase Controller says HELLO.');

	if ( ! $session->has('user_id'))
	{
		$data = array('message' => 'Invalid or expired session.');
		$view = 'error.tpl';
		break;
	}

	$method = $_SERVER['REQUEST_METHOD'];
	
	if (isset($_POST['must_confirm_days']) and empty($_POST['days_confirmed']))
	{
        $flash->set('danger', 'You need to check the checkbox if you want to continue when the last purchase is less than 3 days ago.');
        redirect('?r=purchase&v=1');
        exit;
	}
	
	//----------------------------
	//--- Process GET Request ---
	//----------------------------
	if ($method === 'GET')
	{
		$log->test('Purchase REQUEST = GET');
		
		$version = array_get($_GET, 'v');

		if ($version == 1)
		{
			$cancel_script = ' onclick="window.close(); return false;"';
		}
		else
		{
			$cancel_script = '';
		}

		$data = array('session' => $session->all(), 'flash' => $flash, 'cancel_script' => $cancel_script);
		$view = 'purchase.tpl';
		break;
	}

	$log->test('Purchase REQUEST = POST');
	
	//----------------------------
	//--- Process POST Request ---
	//----------------------------
	include __LIB__ . '/onefile/curl.php';
	include __LIB__ . '/simplehtmldom/simple_html_dom.php';

	$account = $session->get('account');
	$meterno = $session->get('meter');
	$cellpower_session = $session->get('cellpower_session');

	$amount = array_get($_POST, 'amount');
	$customer_cell = array_get($_POST, 'customer-cell');

	$session->put('amount', $amount);
	$session->put('customer_cell', $customer_cell);

	//Send purchase request + Collect purchase confirmation info from response
	include __SNIPPETS__ . '/postBuyGetConfirm.snp.php';

	if ($has_error)
	{
		$data = array('message' => 'ERROR', 'html' => $error_html);
		$view = 'error.tpl';
		$log->error('Purchase Controller POST Failed! Cellpower Error.');
		$log->test('Purchase Controller POST Failed! Cellpower Error.');		
		break;
	}

	if (array_get($_POST, 'v'))
	{
		redirect('?r=confirm&v=1');
	}
	else
	{
		redirect('?r=confirm');
	}
	
} while (0);
