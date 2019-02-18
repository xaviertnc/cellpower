<?php

/**
 * Purchase Pages - Page 4 - Submit Controller (Blue System Version)
 * Submit all collected transaction data to the Mr Prepaid system
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 01 Jun 2014
 *
 */


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

include __LIB__ . '/onefile/mysql/database.php';
include __LIB__ . '/onefile/mysql/querybuilder.php';
include __LIB__ . '/phpmailer/PHPMailerAutoload.php';

$log->test('Embedded Submit Controller Says: HELLO.  Session = ' . $session->get('cellpower_session', 'not set'));

do {

$db = new \OneFile\MySql\Database($config->get('database'));


//FIND & GET METER FROM DB
$meterno = $session->get('meter', 'not set');
$dev_params = array(0 => $meterno);
$devices = $db->exec_prepared('SELECT * FROM devices WHERE deviceno = ?', $dev_params);

if(!$devices)
{
    /* @var $flash OneFile\Flash */
    $message = 'Database Error! Failed to access devices table.';
    $flash->set('danger', $message);
    $log->error($message);
    break;
}

$device = $devices->fetch(PDO::FETCH_OBJ);

//TEST IF METER WAS FOUND
if(!$device)
{
    $message = 'Do NOT close this page!<br>';
    $message .= 'No device with number <b>' . $meterno . '</b> exists on the Mr Prepaid system.<br>';
    $message .= 'First add the device to the system then click on the submit button again.';
    $flash->set('danger', $message);
    $log->warning($message);
    break;
}

//TEST IF TRANSACTION ALREADY SUBMITTED
//GET TRANSACTION WITH ID = trxid
$trxid = $session->get('trxid');
$transactions = $db->exec_prepared('SELECT * FROM transactions WHERE transref = ?', array(0 => $trxid));
if(!$transactions)
{
    /* @var $flash OneFile\Flash */
    $message = 'Database Error! Failed to access transactions table.';
    $flash->set('danger', $message);
    $log->error($message);
    break;
}

//THROW ERROR IF TRANSACTION WAS FOUND. i.e. ALREADY EXISTS
$transaction = $transactions->fetch(PDO::FETCH_OBJ);
if($transaction)
{
    /* @var $flash OneFile\Flash */
    $message = 'Transaction #' . $trxid . ' already submitted on ' . $transaction->created_at;
    $flash->set('danger', $message);
    $log->warning($message);
    $log->warning('$_SESSION: ' . print_r($_SESSION, true));
    break;
}

$log->test('V1_b Vend Submit Transaction # ' . $trxid . ' For Meter ' . $meterno . ' - Start');

$db->beginTransaction();


//INSERT NEW TRANSACTION
$amount = $session->get('amount');
$trx_query = \OneFile\MySql\QueryBuilder::create()->insertInto('transactions',
    array(
        'device_id'        => $device->id,
        'transref'        => $trxid,
        'type_id'        => 1,
        'status_id'        => 4,
        'amount'        => $amount,
        'note'            => 'Via Cellpower: ' . $session->get('user_name'),
        'created_by'    => $session->get('user_id')
    ));

$trx_result = $db->exec_prepared($trx_query, $trx_query->getParams());

if(!$trx_result)
{
    $db->rollBack();
    $message = 'Insert Transaction Failed...  I.e. System update failed!';
    $flash->set('danger', $message);
    $log->error($message);
    break;
}


//INSERT NEW PAYMENT
$transaction_id = $db->lastInsertId();

$trx_params = array($transaction_id, $amount);
$pmt_result = $db->exec_prepared('INSERT INTO payments (transaction_id, amount) VALUES (?,?)', $trx_params);

if(!$pmt_result)
{
    $db->rollBack();
    $message = 'Insert Payment Failed...  I.e. System update failed!';
    $flash->set('danger', $message);
    $log->error($message);
    break;
}


//INSERT NEW VOUCHER
$voucher_query = \OneFile\MySql\QueryBuilder::create()->insertInto('vouchers',
    array(
        'transaction_id'    => $transaction_id,
        'serial'            => $trxid,
        'ref'                => 'Cellpower',
        'product_cost'        => $amount,
        'product_units'        => total_units($session->get('units')),
        'tax'                => $session->get('vat'),
        'token_numbers'        => tokens_string($session->get('tokens'))
    ));

$voucher_result = $db->exec_prepared($voucher_query, $voucher_query->getParams());

if(!$voucher_result)
{
    $db->rollBack();
    $message = 'Insert Voucher Failed...  I.e. System update failed!';
    $flash->set('danger', $message);
    $log->error($message);
    break;
}

$db->commit();

$log->test('V1_b Vend Submit Transaction - Successfull');

$mail_sent = false;


//SEND EMAIL NOTIFICATION
$mailer_sent = false;

$log->test('device->email = ' . $device->email);

if($session->get('email_notify', false) and $device->email)
{
    $log->test('V1_b Vend Submit Transaction - Send Email - Start');

    $mailer = new \PHPMailer();

    $mailer->SMTPDebug = 0;
    $mailer->isSMTP();                                // telling the class to use SMTP
    $mailer->SMTPAuth   = true;                       // enable SMTP authentication
    $mailer->Port       = $config->get('email.port'); // set the SMTP port
    $mailer->Host       = $config->get('email.smtp'); // SMTP server
    $mailer->Username   = $config->get('email.user'); // SMTP account username
    $mailer->Password   = $config->get('email.pass'); // SMTP account password

    $to = __TESTING__ ? 'neels@tnc-it.co.za' : $device->email;

    $mailer->setFrom('voucher@mrprepaid.co.za', 'Mr Prepaid Vouchers');
    $mailer->addReplyTo('voucher@mrprepaid.co.za', 'Mr Prepaid Vouchers');

    $mailer->addAddress($to);
    $mailer->addBCC('voucher@mrprepaid.co.za');
    //$mailer->addBCC('neels@tnc-it.co.za', 'Neels Moller');

    $mailer->Subject = 'Mr Prepaid - R' . currency($session->get('amount', 0)) . ' Prepaid Voucher (' . $device->deviceno . ')';

    if (__TESTING__)
    {
        $mailer->Subject .= ' - TEST';
    }

    $mailer->msgHTML($template->render('phpmail.tpl', $session->all(), true), __WEB_PATH__);

    //send the message, check for errors
    if ( ! $mailer->send())
    {
        $message = 'System Updated OK - Email to "' . $to . '" FAILED to send! Error: ' . $mailer->ErrorInfo;
        $log->warning($message);
        $flash->set('danger', $message);
        break;
    }
    else
    {
        $message = 'System Updated OK - Email to "' . $to . '" sent OK';
        $flash->set('success', $message);
        break;
    }
}
else
{
    $message = 'System Updated OK - No Email Notification';
    $flash->set('success', $message);
}

} while(0);

$log->test($message);
$log->test('');
