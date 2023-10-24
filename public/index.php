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
$router->add('api/monthlyLimit/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Settings', 'action' => 'monthlyLimit']);
    
$router->dispatch($_SERVER['QUERY_STRING']);
