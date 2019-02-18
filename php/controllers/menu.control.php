<?php

/** 
 * Cellpower - Menu Page
 *  
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 * 
 */

do {
	
if(!$session->has('user_id'))
{
	$data = array('message' => 'Invalid or expired session.');
	$view = 'error.tpl';
	break;	
}
	
$data = array('session' => $session->all(), 'flash' => $flash);

$view = 'menu.tpl';

} while(0);