<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Sessions
 */
session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('balances/show/{type}', ['controller' => 'Balances', 'action' => 'Show']);
$router->add('getToken', ['controller' => 'Account', 'action' => 'getToken']);
$router->add('settings', ['controller' => 'Settings', 'action' => 'index']);
$router->add('api/limit/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'limit']);
$router->add('api/setLimit/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'setLimit']);
$router->add('api/monthlyExpenses/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Expenses', 'action' => 'monthlyExpenses']);
$router->add('settings/add/{type}', ['controller' => 'Settings', 'action' => 'Add']);
$router->add('api/settings/vaidate/{type}/{id:[\d]+}/{name:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'validateSettingName']);
$router->add('api/settings/remove/{type}', ['controller' => 'Settings', 'action' => 'verifyRemoval']);
$router->add('settings/remove/{type}/{name:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'remove']);
$router->add('settings/update/{type}/{id:[\d]+}/{name:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'update']);
try{
    $router->dispatch($_SERVER['QUERY_STRING']);
} catch (\Exception $e){
    http_response_code(500);
    echo null;
}
//$router->dispatch($_SERVER['QUERY_STRING']);
    
