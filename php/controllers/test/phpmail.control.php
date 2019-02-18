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
	$template->render('phpmail.tpl', $data);
	die();
}

//SEND EMAIL NOTIFICATION
require __LIB__ . '/phpmailer/PHPMailerAutoload.php';


$mail = new \PHPMailer();

$mail->SMTPDebug = 0;
$mail->isSMTP();                                // telling the class to use SMTP
$mail->SMTPAuth   = true;                       // enable SMTP authentication
$mail->Port       = $config->get('email.port'); // set the SMTP port
$mail->Host       = $config->get('email.smtp'); // SMTP server
$mail->Username   = $config->get('email.user'); // SMTP account username
$mail->Password   = $config->get('email.pass'); // SMTP account password

$mail->setFrom('voucher@mrprepaid.co.za', 'Mr Prepaid Vouchers');
$mail->addReplyTo('voucher@mrprepaid.co.za', 'Mr Prepaid Vouchers');

$mail->addAddress('info@mrprepaid.co.za', 'Michelle');
$mail->addBCC('neels@tnc-it.co.za', 'Neels Moller');

$mail->Subject = 'Mr Prepaid (TEST) Token Voucher For Meter: ' . $data['meter'];

$mail->msgHTML($template->render('phpmail.tpl', $data, true), __WEB_PATH__);

//send the message, check for errors
if ( ! $mail->send())
{
	$message = "PHPMailer Error: " . $mail->ErrorInfo;
	echo $message . '<br>';
	$log->warning($message);
	$log->test($message);
	$flash->set('danger', $message);
}
else
{
	$message = 'PHPMailer - Message Sent OK!';
	echo $message . '<br>';
	$log->test($message);
	$flash->set('success', $message);
}


} while(0);


//redirect('?');

echo "<br><br>";

die('<END>');