<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Ensure Composer autoload is included

use App\core\Router;

$router = new Router();

// Register routes
$router->add('GET', '/home', 'App\Controller\Front\HomeController@index'); // Use forward slash and correct case

$router->dispatch();