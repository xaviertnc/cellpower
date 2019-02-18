<?php

function detect_environment()
{
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;

	// local: Use a local version of the production database (Will need to merge changes
	// to the actual actual production database later. (For off-site emergency transactions)
	if ($host == 'localhost' or strpos($host, '.local' )) { return 'local'; }

	// development: Use a copy of the production database, but don't care about the data. No need
	// to merge changes later! Just replace with a newer copy from time to time.
	if (strpos($host, '.dev' )) { return 'dev'; }

	// test: Database only contains test meters and an additional user to test the "front-end"
	if (strpos($host, '.comp')) { return 'comp'; }

	// compliance: Database only contains compliance meters and an additional user to verify the "front-end"
	if (strpos($host, '.test')) { return 'test'; }

	// Default to "production"
	return 'prod';
}


define('__VER__'         , '2.1');
define('__ENV__'         , detect_environment());

define('__WEB_PATH__'    , __DIR__);
define('__APP_PATH__'    , __DIR__      . '/php');

define('__MODELS__'      , __APP_PATH__ . '/models');
define('__VIEWS__'       , __WEB_PATH__ . '/views');
define('__CONTROLLERS__' , __APP_PATH__ . '/controllers');
define('__SNIPPETS__'    , __APP_PATH__ . '/snippets');

define('__STORAGE__'     , __WEB_PATH__ . '/storage');
define('__COMPILED__'    , __STORAGE__  . '/viewscache');

define('__LIB__'         , __APP_PATH__ . '/vendor');

define('__TIMEZONE__'   , 'Africa/Johannesburg');


switch(__ENV__)
{
    case 'dev':
        set_time_limit(10);
        define('__TESTING__' , false);
        define('__LOGS__'    , __STORAGE__ . '/logs');
        break;

    default:
        set_time_limit(180);
        define('__TESTING__' , false);
        define('__LOGS__'    , __STORAGE__ . '/logs');
}


//INCLUDE CORE LIBS
include __LIB__ . '/onefile/log.php';
include __LIB__ . '/onefile/file.php';
include __LIB__ . '/onefile/flash.php';
include __LIB__ . '/onefile/config.php';
include __LIB__ . '/onefile/phpext.php';
include __LIB__ . '/onefile/session.php';
include __LIB__ . '/onefile/template.php';
include __LIB__ . '/onefile/basicrouter.php';


//INITIALIZE GLOBALS AND SERVICES i.e. BOOTSTRAP

date_default_timezone_set(__TIMEZONE__);

$log = new \OneFile\Log(__LOGS__, true, 'test');

$log->setFilename('testing/' . $log->getDate() . '.log', 'test');

$template = new \OneFile\Template(__VIEWS__ , __COMPILED__);

$session = new \OneFile\Session('__CELLP__');

$router = new OneFile\BasicRouter('login');

$config = new \OneFile\Config();

$flash = new \OneFile\Flash();

$view = 'login.tpl';

$data = array();


//ROUTE REQUEST
$route = array_get($_GET, 'r', 'vend/vendhome');

$log->test('');

$log->test('Before Controller: Requested ROUTE = ' . $route);

include $router->getControllerFilename(__CONTROLLERS__ . '/%s.control.php', $route);

$log->test('AFTER Controller: View = ' . $view);

//RENDER VIEW
$template->render($view, $data);
