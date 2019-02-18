<?php

/**
 * SNIPPET / INCLUDE File
 * 
 * Cellpower POST Confirm Purchase + GET Token Voucher Information Page
 * 
 * Used in both confirm.control
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 09 Jun 2014
 * 
 * @depends \OneFile\Curl
 * @depends \OneFile\File
 * @depends \OneFile\Session
 * @depends simple_html_dom NB!
 * @depends phpext 
 */

if (__TESTING__)
{
	$html = file_get_contents(__WEB_PATH__ . '/res/voucher.html');
}
else
{
	//Issued after submitting the Confirm Purchase form
	//Returns token voucher information page

	$params = array(
		'action' => 'purchase2',
		'meter' => $meterno,
		'amount' => $amount,
		'pin' => $config->get("accounts.$account"),
		'customer' => $customer_cell,
		'session' => $cellpower_session
	);
	
	$log->test("Confirm POST: action=purchase2, amount=$amount, cell={$params['customer']}, meter={$params['meter']}, pin={$params['pin']}");
	
	$html = OneFile\Curl::create()->httpPost($config->get('cellpower-url'), $params);
}

do {
	
	$has_error = (stripos($html, 'token response') === false);

	if ($has_error)
	{
		
		if( ! $html)
		{
			$error_html = 'Tshwane Cellpower Website returned an Empty or No response!';
			break;
		}
		
		$doc = str_get_html($html);
		
		foreach ($doc->find('input[type=submit]') as $e)
		{
			$e->outertext = '';
		}

		foreach ($doc->find('a') as $e)
		{
			$e->outertext = '';
		}
		
		$error_html = $doc->find('body div', 0)->innertext;
		
		if (stripos($error_html, 'login') !== false) { $error_html = 'Session Expired!'; }

		$file = new \OneFile\File(__LOGS__ , 'voucher-error_' . $meterno . '_' . date('His') . '.html');
		
		$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);
		
		break;
	}

	$file = new \OneFile\File(__LOGS__ , 'voucher_' . $meterno . '_' . date('His') . '.html');
	
	$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);
	
	$matches = array();

	$doc = str_get_html($html);

	$voucher_text = $doc->find('table', 0)->children(1)->children(0)->innertext;

	preg_match_all('/token[^\d]*([0-9]+)[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['tokens']);
	preg_match_all('/units:[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['units']);
	preg_match_all('/value:[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['values']);
	preg_match('/purchase[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['purchase']);
	preg_match('/vat:[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['vat']);
	preg_match('/sms[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['sms']);
	preg_match('/profit[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['profit']);
	preg_match('/balance[^\d]*([0-9\.-]+)/i', $voucher_text, $matches['balance']);
	preg_match('/transa[^\d]*([0-9]+)/i', $voucher_text, $matches['trxid']);

	$tokens = array();
	$token_matches = array_get($matches, 'tokens');
	foreach (array_get($token_matches, 2, array()) as $match)
	{
		$tokens[] = $match;
	}
	unset($matches['tokens']);

	$units = array();
	$unit_matches = array_get($matches, 'units');
	foreach (array_get($unit_matches, 1, array()) as $match)
	{
		$units[] = $match;
	}
	unset($matches['units']);

	$values = array();
	$value_matches = array_get($matches, 'values');
	foreach (array_get($value_matches, 1, array()) as $match)
	{
		$values[] = $match;
	}
	unset($matches['values']);

	$match_values = array();
	foreach ($matches as $id => $match)
	{
		$value = array_get($match, 1);
		$match_values[$id] = $value;
	}

	$session->put('tokens', $tokens, array());
	$session->put('units', $units, array());
	$session->put('values', $values, array());
	$session->put('purchase_amount', array_get($match_values, 'purchase', 0));
	$session->put('vat', array_get($match_values, 'vat', 0));
	$session->put('sms', array_get($match_values, 'sms', 0));
	$session->put('profit', array_get($match_values, 'profit', 0));
	$session->put('balance', array_get($match_values, 'balance', 0));
	$session->put('trxid', array_get($match_values, 'trxid', ''));
	$session->put('voucher_text', $voucher_text, 'Error... No voucher returned!');
	
} while (0);
