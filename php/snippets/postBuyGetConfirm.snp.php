<?php

/**
 * SNIPPET / INCLUDE File
 * 
 * Cellpower POST Purchase Form + GET Purchase Confirmation Page Summary
 * 
 * Used in both vend.control and purchase.control
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 03 Jun 2014
 * 
 * @depends \OneFile\Curl
 * @depends \OneFile\File
 * @depends \OneFile\Session
 * @depends simple_html_dom NB!
 * @depends phpext 
 */

if (__TESTING__)
{
	$html = file_get_contents(__WEB_PATH__ . '/res/confirm.html');
}
else
{
	//Issued after submitting the Purchase form
	//Returns purchase information summary

	$params = array(
		'action' => 'purchase1',
		'meter' => $meterno,
		'amount1' => $amount,
		'amount2' => $amount,
		'pin' => $config->get("accounts.$account"),
		'customer' => $customer_cell,
		'session' => $cellpower_session
	);
	
	$log->test("Purchase POST: action=purchase1, amount=$amount, cell={$params['customer']}, meter={$params['meter']}, pin={$params['pin']}");

	$html = OneFile\Curl::create()->httpPost($config->get('cellpower-url'), $params);
}


do {

	$has_error = (stripos($html, 'you have requested') === false);
	
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
		
		$file = new \OneFile\File(__LOGS__ , 'confirm-error_' . $meterno . date('_His') . '.html');
		
		$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);
		
		break;
	}
	

	//$file = new \OneFile\File(__LOGS__ , 'confirm_' . $meterno . date('_His') . '.html');
	//$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);
	
	$doc = str_get_html($html);
	
	$summary = $doc->find('p', 1)->innertext;
	
	$session->put('summary', $summary);

} while (0);
