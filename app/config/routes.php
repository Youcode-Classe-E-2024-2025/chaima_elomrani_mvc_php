<?php
require_once __DIR__."/../core/Router.php";
// $router->add('HTTP_METHOD', '/path', 'ControllerName@methodName');
$router = new App\core\Router();
$router->add('GET', '/home', 'HomeController@index');
