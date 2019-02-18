<?php

/** 
 * Purchase Pages - Page 4 - Submit Controller (Blue System Version)
 * Submit all collected transaction data to the Mr Prepaid system
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 01 Jun 2014
 *
 */

do {

if(!$session->has('user'))
{
	$data = array('message' => 'Invalid or expired session.');
	$view = 'error.tpl';
	break;	
}

include __LIB__ . '/onefile/mysql/database.php';
include __LIB__ . '/onefile/mysql/querybuilder.php';

function tokens_string($tokens_array = null)
{
	if(!$tokens_array) return '';
	return implode(",\n", $tokens_array);
}

function total_units($units_array = null)
{
	if(!$units_array) return 0;
	
	$total_units = 0;
	foreach($units_array as $units)
	{
		$total_units += floatval($units);
	}
	
	return round($total_units, 2);
}


$db = new \OneFile\MySql\Database($config->get('database'));


//FIND & GET DEVICE
$meter = $session->get('meter', 'not set');
$dev_params = array(0 => $meter);
$devices = $db->exec_prepared('SELECT * FROM devices WHERE deviceno = ?', $dev_params);

if(!$devices)
{
	/* @var $flash OneFile\Flash */
	$flash->set('danger', 'Database Error! Failed to access devices table.');
	redirect('?r=voucher&v=1');
}

$device = $devices->fetch(PDO::FETCH_OBJ);

if(!$device)
{
	$message = 'Do NOT close this page!<br>';
	$message .= 'No device with number <b>' . $meter . '</b> exists on the Mr Prepaid system.<br>';
	$message .= 'First add the device to the system then click on the submit button again.';
	$flash->set('danger', $message);
	redirect('?r=voucher&v=1');
}

//TEST IF TRANSACTION ALREADY SUBMITTED
$trxid = $session->get('trxid');
$transactions = $db->exec_prepared('SELECT * FROM transactions WHERE transref = ?', array(0 => $trxid));
if(!$transactions)
{
	/* @var $flash OneFile\Flash */
	$flash->set('danger', 'Database Error! Failed to access transactions table.');
	redirect('?r=voucher&v=1');
}

$transaction = $transactions->fetch(PDO::FETCH_NUM);
if($transaction)
{
	/* @var $flash OneFile\Flash */
	$flash->set('danger', 'Transaction #' . $trxid . ' already submitted!');
	redirect('?r=voucher&v=1');
}


$db->beginTransaction();


//INSERT NEW TRANSACTION
$amount = $session->get('amount');
$trx_query = \OneFile\MySql\QueryBuilder::create()->insertInto('transactions',
	array(
		'device_id'		=> $device->id,
		'transref'		=> $trxid,
		'type_id'		=> 1,
		'status_id'		=> 4,
		'amount'		=> $amount,
		'note'			=> 'Via Cellpower: ' . $session->get('user'),
		'created_by'	=> $session->get('user_id')
	));

$trx_result = $db->exec_prepared($trx_query, $trx_query->getParams());

if(!$trx_result)
{
	$db->rollBack();
	$flash->set('danger', 'Insert Transaction Failed...  I.e. System update failed!');
	redirect('?r=voucher&v=1');
}


//INSERT NEW PAYMENT
$transaction_id = $db->lastInsertId();

$trx_params = array($transaction_id, $amount);
$pmt_result = $db->exec_prepared('INSERT INTO payments (transaction_id, amount) VALUES (?,?)', $trx_params);

if(!$pmt_result)
{
	$db->rollBack();
	$flash->set('danger', 'Insert Payment Failed...  I.e. System update failed!');
	redirect('?r=voucher&v=1');
}


//INSERT NEW VOUCHER
$voucher_query = \OneFile\MySql\QueryBuilder::create()->insertInto('vouchers',
	array(
		'transaction_id'	=> $transaction_id,
		'ref'				=> $trxid,
		'product_cost'		=> $amount,
		'product_units'		=> total_units($session->get('units')),
		'tax'				=> $session->get('vat'),
		'token_numbers'		=> tokens_string($session->get('tokens'))
	));

$voucher_result = $db->exec_prepared($voucher_query, $voucher_query->getParams());

if(!$voucher_result)
{
	$db->rollBack();
	$flash->set('danger', 'Insert Voucher Failed...  I.e. System update failed!');
	redirect('?r=voucher&v=1');
}


$db->commit();


$mail_sent = false;


//SEND EMAIL NOTIFICATION
if($device->email and $device->email_notify)
{
	include __LIB__ . '/kmmailer/km_smtp_class.php';

	$mailer = new KM_Mailer($config->get('email.smtp'),	$config->get('email.port'),	$config->get('email.user'),	$config->get('email.pass'));

	$header_image = __WEB_PATH__ . '/img/tshwane_logo.gif';
	$footer_image = __WEB_PATH__ . '/img/signature.jpg';

	$mailer->addInlineImage($header_image);
	$mailer->addInlineImage($footer_image);

	$session->put('header_image', $mailer->renderInlineImage($header_image));
	$session->put('footer_image', $mailer->renderInlineImage($footer_image));

	if($mailer->isLogin)
	{
		$to = $device->email;
		$from = 'voucher@mrprepaid.co.za';
		$subject = 'Mr Prepaid Token Voucher For Meter: ' . $meter->id;
		$body = $template->render('email.tpl', $session->all(), true);

		// $mail->send(from, to, subject, body, headers = optional)
		if($mailer->send($from, $to, $subject, $body))
		{
			$mail_sent = true;
		}
		else
		{
			$flash->set('danger', 'Failed to send email');
		}
	}
	else
	{
		$flash->set('danger', 'Login to mail server failed<br>');
	}
}


//SUCCESS! RETURN TO MENU
$session->put('update_message', 'System Updated' . ($mail_sent?' and Email Sent':'') . ' Successfully!');

redirect('?r=success');

} while(0);
