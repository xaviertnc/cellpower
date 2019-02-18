<?php

/** 
 * Cellpower - Abort Controller
 * Remove any meter data collected up to this point without restarting the entire session.
 *  
 * @author C. Moller <xavier.tnc@gmail.com> - 01 Jun 2014
 * 
 */

//Save critical stuff before clearing the session
$user = $session->get('user');
$user_id = $session->get('user_id');
$account = $session->get('account');
$cellpower_session = $session->get('cellpower_session');

//We need to clear to forget all the values of the previous meter!
$session->clear();

//Restore critical values
$session->put('user', $user);
$session->put('user_id', $user_id);
$session->put('account', $account);
$session->put('cellpower_session', $cellpower_session);
	
redirect('?r=menu');