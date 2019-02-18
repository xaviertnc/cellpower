<?php

/**
 * Mr Prepaid Original - Vend Bridge Controller
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 03 Jul 2014
 *
 */

$log->test('Vend Bridge Controller Says: HELLO');

$device_id = array_get($_GET, 'd');
$user_id = array_get($_GET, 'u');
$account = array_get($_GET, 'a');
$sms_notify = array_get($_GET, 'sms_notify');
$email_notify = array_get($_GET, 'email_notify');

$error_message = 'This session of Cellpower did not successfully link to the Mr Prepaid system!<br>'
    . 'Close this window and try again or report the problem.';

$log->test('DEVICE ID = ' . $device_id);
$log->test('USER ID = ' . $user_id);
$log->test('CELLPOWER ACC = ' . $account);


do {

if ( ! $device_id)
{
    $data = array('message' => $error_message);
    $view = 'error.tpl';
    break;
}

//Start NEW

$session->clear();

include_once __LIB__ . '/onefile/mysql/database.php';

$db = new \OneFile\MySql\Database($config->get('database'));

$users = $db->exec_prepared('SELECT * FROM users WHERE id=?', array(0 => $user_id));
$user = $users->fetch(PDO::FETCH_ASSOC);

if ( ! $user)
{
    $data = array('message' => 'Invalid User ID');
    $view = 'error.tpl';
    break;
}

$devices = $db->exec_prepared('SELECT * FROM devices WHERE id=?', array(0 => $device_id));

$device = $devices->fetch(PDO::FETCH_ASSOC);

if ( ! $device)
{
    $data = array('message' => $error_message);
    $view = 'error.tpl';
    break;
}

//$log->test('DEVICE = ' . print_r($device, true));

$deviceno =  array_get($device, 'deviceno');

$cellnumbers = array();

$cellno = array_get($device, 'cellno2');
if ($cellno)
{
    $cellnumbers[] = $cellno;
}

$cellno = array_get($device, 'cellno1');
if ($cellno)
{
    $cellnumbers[] = $cellno;
}

//if ( ! $cellnumbers)
//{
//    $data = array('message' => 'No CELL NUMBER(s) found for device no: ' . $deviceno);
//    $view = 'error.tpl';
//    break;
//}

$email = array_get($device, 'email');

$session->put('user_id', $user_id);
$session->put('user_name', ucfirst($user['user_name']));
$session->put('meter', $deviceno);
$session->put('account', $account);
$session->put('customer_cell', $cellnumbers ? $cellnumbers[0] : '*no cell*');
$session->put('customer_cellnumbers', $cellnumbers);
$session->put('sms_notify', $sms_notify);
$session->put('email_notify', $email_notify);
$session->put('email', $email?:'*no email address*');

$log->test('USER NAME = ' . $session->get('user_name'));
$log->test('DEVICE NUMBER = ' . $deviceno);
$log->test('CUSTOMER CELL NUMBERS = ' . print_r($cellnumbers, true));
$log->test('CUSTOMER EMAIL = ' . $session->get('email'));

//var_dump($_SESSION);
//echo "Session ID = " . $session->id();
//die();

redirect('?r=vend/vendhome');

} while(0);


redirect('?r=vend/vendhome');
