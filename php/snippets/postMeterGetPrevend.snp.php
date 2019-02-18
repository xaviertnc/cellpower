<?php

/**
 * SNIPPET / INCLUDE File
 * 
 * Cellpower POST SelectMeter + GET Prevend Information
 * 
 * Used in both vend.control and meterselect.control
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 03 Jun 2014
 * 
 * @depends \OneFile\Curl
 * @depends \OneFile\File
 * @depends \OneFile\Session
 * @depends simple_html_dom
 * @depends phpext
 */

if (__TESTING__)
{
	$html = file_get_contents(__WEB_PATH__ . '/res/prevend.html');
}
else
{
	//Issued after submitting the Select-Meter form!
	//Returns Meter Prevend Information
		
	$params = array(
		'action' => 'check1',
		'meter' => $meterno,
		'pin' => $config->get("accounts.$account"),
		'session' => $cellpower_session
	);

	$log->test("Meter Select POST: action=check1, session={$params['session']}, meter={$params['meter']}, pin={$params['pin']}");

	$html = OneFile\Curl::create()->httpPost($config->get('cellpower-url'), $params);
}


do {

	$has_error = (stripos($html, 'meter check') === false);

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
		
		$file = new \OneFile\File(__LOGS__ , 'prevend-error_' . $meterno . date('_His') . '.html');
		
		$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);
		
		break;
	}


	$file = new \OneFile\File(__LOGS__ , 'prevend_' . $meterno . date('_His') . '.html');
	$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);

	$matches = array();

	preg_match('/arrears[\s\S]*?<td>(.*?)<\/td>/i', $html, $matches['arrears']);
	preg_match('/min v[\s\S]*?<td>(.*?)<\/td>/i', $html, $matches['min']);
	preg_match('/max v[\s\S]*?<td>(.*?)<\/td>/i', $html, $matches['max']);
	preg_match('/custom[\s\S]*?<td>(.*?)<\/td>/i', $html, $matches['customer_name']);
	preg_match('/last pur[\s\S]*?<td>(.*?)<\/td>/i', $html, $matches['last_purchase']);
	preg_match('/levy[^\d]*([0-9\.]+)/i', $html, $matches['sms_cost']);
	preg_match('/balan[^\d]*([0-9\.-]+)/i', $html, $matches['pre_balance']);
	preg_match('/transa[^\d]*([0-9]+)/i', $html, $matches['pre_trxid']);

	$match_values = array();

	foreach ($matches as $id => $match)
	{
		$value = array_get($match, 1);
		$match_values[$id] = $value;
	}

	//Add new pre-vend info
	$session->put('min', array_get($match_values, 'min', ''));
	$session->put('max', array_get($match_values, 'max', ''));
	$session->put('arrears', array_get($match_values, 'arrears', 0));
	$session->put('customer_name', array_get($match_values, 'customer_name', ''));
	$session->put('last_purchase', array_get($match_values, 'last_purchase', 999));
	$session->put('sms_cost', array_get($match_values, 'sms_cost', 0));
	$session->put('pre_balance', array_get($match_values, 'pre_balance', 'Unknown'));
	$session->put('pre_trxid', array_get($match_values, 'pre_trxid', ''));

} while (0);
