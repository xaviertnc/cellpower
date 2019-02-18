<?php

/**
 * Mr Prepaid Original/Old App Version - Vend Home Controller
 *
 * Shows vend page + Runs vend process on submit
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 03 Jun 2014
 *
 */

function cellno_valid($cellno)
{
    return (is_numeric($cellno) and strlen($cellno) == 10);
}


do {

    $log->test('Vend Home Controller Says: HELLO');

    //----------------------------
    //--- Process GET Request ---
    //----------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        $log->test('Vend Home: REQUEST = GET');

        if (__TESTING__)
        {
            $session->put('user', 'Generic User');
            $session->put('user_id', '999');
            $session->put('meter', '14025902736');
            $session->put('customer_cellnumbers', array('0826941555'));
            $session->put('email', 'info@webchamp.co.za');
        }

        $session->put('accounts', array_keys($config->get('accounts')));

        $data = array('session' => $session->all(), 'php_session' => $session->id());
        $view = 'vendhome.tpl';
        break;
    }


    //----------------------------
    //--- Process POST Request ---
    //----------------------------

    $log->test('Vend Home / Login: REQUEST = POST.  Session = ' . $session->get('cellpower_session', 'not set'));

    if (__TESTING__)
    {
        include __SNIPPETS__ . '/completedTrxSessionStub.snp.php';
        redirect('?r=voucher&v=1');
        exit;
    }

    include __LIB__ . '/onefile/curl.php';
    include __LIB__ . '/simplehtmldom/simple_html_dom.php';



    $amount = array_get($_POST, 'amount');
    $account = array_get($_POST, 'account');
    $meterno = $session->get('meter');
    $sms_notify = array_get($_POST, 'sms_notify', false);
    $email_notify = array_get($_POST, 'email_notify', false);
    $customer_cell = array_get($_POST, 'cellno', null);
    if ($customer_cell)
    {
        $customer_cell = ($sms_notify and cellno_valid($customer_cell)) ? $customer_cell : null;
    }

    $session->put('amount', $amount);
    $session->put('account', $account);
    $session->put('customer_cell', $customer_cell);
    if (!$email_notify) $session->put('email', 'voucher@mrprepaid.co.za');


    // LOGIN
    // Send login request + Collect session value from returned menu page

    include __SNIPPETS__ . '/postLoginGetMenu.snp.php';

    if ($has_error)
    {
        $data = array('message' => 'ERROR', 'html' => $error_html);
        $view = 'error.tpl';
        break;
    }

    // GET PRE-VEND INFO + VALIDATE
    // Send meter select request + Collect prevend info from prevend response

    include __SNIPPETS__ . '/postMeterGetPrevend.snp.php';

    if ($has_error)
    {
        $data = array('message' => 'ERROR', 'html' => $error_html);
        $view = 'error.tpl';
        break;
    }


    //*** START - VALIDATE ***

    $min = $session->get('min');

    if (is_numeric($min) and $amount < $min)
    {
        $flash->set('danger', 'Warning! The amount requested is LOWER than allowed minimum.');
        redirect('?r=purchase&v=1');
        exit;
    }

    $max = $session->get('max');

    if (is_numeric($max) and $amount > $max)
    {
        $flash->set('danger', 'Warning! The amount requested exceeds the allowed maximum.');
        redirect('?r=purchase&v=1');
        exit;
    }

    $arrears = $session->get('arrears');

    if ($arrears)
    {
        $flash->set('danger', 'Warning! This meter has an ARREARS amount.');
        redirect('?r=purchase&v=1');
        exit;
    }

    $pre_balance = $session->get('pre_balance');


    if ($pre_balance !== 'Unknown' and $amount > $pre_balance)
    {
        $flash->set('danger', 'Warning! Available balance insufficient.');
        redirect('?r=purchase&v=1');
        exit;
    }

    $days_since_last_purchase = $session->get('last_purchase');

    if ( ! $days_since_last_purchase or $days_since_last_purchase <= 3)
    {
        $flash->set('danger', 'Warning! Days since last purchase is less than 3 days.');
        redirect('?r=purchase&v=1');
        exit;
    }

    // *** END VALIDATE ***


    // SUBMIT PURCHASE REQUEST + CONFIRM
    // Send purchase request + Collect purchase summary from confirmation page response

    include __SNIPPETS__ . '/postBuyGetConfirm.snp.php';

    if ($has_error)
    {
        $data = array('message' => 'ERROR', 'html' => $error_html);
        $view = 'error.tpl';
        break;
    }


    //
    // GOTO CONFIRM PAGE
    //
    redirect('?r=confirm&v=1');

} while (0);
