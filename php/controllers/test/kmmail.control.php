<?php

/** 
 * Test Sending Emails
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 04 June 2014
 *
 */

do {

$trxid = 3456823;
	
$data = array(
	
	'meter' => '0706789012 (Test)',
	
	'tokens' => array(
		'123 456 789 000',
		'111 222 333 444',
	),
	
	'units' => array(
		20,
		34.6
	),
	
	'values' => array(
		340,
		588.90
	),
	
	'amount' => 928.90,
	
	'vat' => 104.50,
	
	'trxid' => $trxid,	
);

//DUMP TO SRCREEN IF &scr
if(array_has($_GET, 'scr'))
{
	$email_template = new OneFile\Template('views', 'compiled');
	$email_template->render('email.tpl', $data);
	die();
}

//SEND EMAIL NOTIFICATION
include __LIB__ . '/kmmailer/km_smtp_class.php';


$mailer = new KM_Mailer($config->get('email.smtp'),	$config->get('email.port'),	$config->get('email.user'),	$config->get('email.pass'));

$header_image = __WEB_PATH__ . '/img/tshwane_logo.gif';
$footer_image = __WEB_PATH__ . '/img/signature.jpg';

$mailer->addInlineImage($header_image);
$mailer->addInlineImage($footer_image);

$data['header_image'] = $mailer->renderInlineImage($header_image);
$data['footer_image'] = $mailer->renderInlineImage($footer_image);


if($mailer->isLogin)
{
	$to = 'riette@mrprepaid.co.za';
//	$to = 'neels@webchamp.co.za';
	$from = $config->get('email.from', 'voucher@mrprepaid.co.za');
	$subject = 'Mr Prepaid Token Voucher #' . $data['meter'];
	$body = $template->render('email.tpl', $data, true);

	// $mail->send(from, to, subject, body, headers = optional)
	if($mailer->send($from, $to, $subject, $body))
	{
		$mail_sent = true;
	}
	else
	{
		$flash->set('danger', 'Failed to send email');
		break;
	}
}
else
{
	$flash->set('danger', 'Login to mail server failed<br>');
	break;
}


$flash->set('success', 'Email Send Successful!');


} while(0);


redirect('?');