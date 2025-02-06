<?php

namespace App\core;

class Router
{

    private array $routes = [];

    public function add(string $method, string $path, string $handler)
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            
            if (strpos($handler, '@') === false) {
                http_response_code(500);
                echo "Invalid handler format. Use 'Controller@method'.";
                return;
            }

            [$controller, $method] = explode('@', $handler);

            if (!class_exists($controller)) {
                http_response_code(500);
                echo "Controller class '$controller' not found.";
                return;
            }

            // Check if the method method exists
            if (!method_exists($controller, $method)) {
                http_response_code(500);
                echo "Method '$method' not found in '$controller'.";
                return;
            }
                $controllerInstance = new $controller;
                $controllerInstance->$method();
            
                // call_user_func(callback: [$controllerInstance, $method]);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }


    }
}