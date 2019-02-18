<?php

/**
 * SNIPPET / INCLUDE File
 *
 * Cellpower POST Login + GET Session from Menu Page
 *
 * Used in both vend.control and login.control
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 03 Jun 2014
 *
 * @depends \OneFile\Curl
 * @depends \OneFile\File
 * @depends \OneFile\Session
 * @depends simple_html_dom
 * @depends phpext
  */

if(__TESTING__)
{
    $html = file_get_contents(__WEB_PATH__ . '/res/menu.html');
}
else
{
    //Issued after submitting the Login form
    //Returns the Cellpower Session found in the Menu response page

    $params = array(
        'action'    => 'home', // or 'login'
        'logmsisdn' => $account,
        'logpin'    => $config->get("accounts.$account")
    );

    $log->test("Login POST: action=home, logmsisdn={$params['logmsisdn']}, logpin={$params['logpin']}");

    $html = \OneFile\Curl::create()->httpPost($config->get('cellpower-url'), $params);
}

do {

    $has_error = (stripos($html, 'from the menu') === false);

    if($has_error)
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

        $error_html = $doc->find('body div',0)->innertext;

        $file = new \OneFile\File(__LOGS__ , 'menu-error_' . (isset($meterno) ? $meterno.'_' : '') . date('His') . '.html');

        $file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);

        break;
    }

    //$file = new \OneFile\File(__LOGS__ , 'menu_' . (isset($meterno) ? "$meterno_" : '') . date('His') . '.html');
    //$file->addGroupPath('cellpower-html', 'Y/M/dMY/H')->write($html);

    $matches = array();

    //Make sure we did not let the session expire!
    if ( ! preg_match('/name="session.*value="(.*)"/', $html, $matches))
    {
        $error_html = '<p>No Session Found After Login!</p>';

        $has_error = true;

        break;
    }

    $cellpower_session = trim(array_get($matches, 1));

    $session->put('cellpower_session', $cellpower_session);

} while (0);
