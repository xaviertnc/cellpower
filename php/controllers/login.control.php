<?php

/** 
 * Login / Default Controller
 *  
 * @author C. Moller <xavier.tnc@gmail.com> - 28 May 2014
 * 
 */

do {

include __LIB__ . '/onefile/mysql/database.php';
include __LIB__ . '/cartalyst/Sha256Hasher.php';

$db = new \OneFile\MySql\Database($config->get('database'));


//----------------------------
//--- Process GET Request ---
//----------------------------

if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$results = $db->query('SELECT * FROM users WHERE activated=1 AND id>1');
	
	if(!$results) die('Error Fetching Users... Query Failed!');
	
	$users = array();
	
	while($user = $results->fetch(PDO::FETCH_OBJ))
	{
		$users[$user->id] = $user->first_name;
	}
	
	$data = array('accounts' => array_keys($config->get('accounts')), 'users' => $users, 'flash' => $flash);
	
	$view = 'login.tpl';
	
	break;
}


//----------------------------
//--- Process POST Request ---
//----------------------------
include __LIB__ . '/onefile/curl.php';
include __LIB__ . '/simplehtmldom/simple_html_dom.php';

$account = array_get($_POST, 'account');
$user_id = array_get($_POST, 'user');
$password = array_get($_POST, 'password');

$query = $db->prepare('SELECT id, password, first_name FROM users WHERE id=?');

$result = $query->execute(array(0 => $user_id));

if(!$result) die('Error Fetching User... Query Failed!');
	
$user = $query->fetch(PDO::FETCH_OBJ);

$hasher = new \Cartalyst\Sentry\Hashing\Sha256Hasher();

if(!$hasher->checkhash($password, $user->password))
{
	$data = array('message' => 'Authentication Failed!');
	$view = 'error.tpl';
	break;	
}

$session->clear();

$session->put('user', $user->first_name);
$session->put('user_id', $user->id);
$session->put('account', $account);

//Send login request + Collect session value from returned menu page
include __SNIPPETS__ . '/postLoginGetMenu.snp.php';

if($has_error)
{
	$data = array('message' => 'ERROR', 'html' => $error_html);
	$view = 'error.tpl';
	break;	
}

redirect('?r=menu');

} while(0);
