<?php

namespace Ipedis\Demo\HttpSignature\Router;

use Symfony\Component\HttpFoundation\Request;

class Router
{
    /**
     * Holds the registered routes
     *
     * @var array $routes
     */
    private $routes = [];

    /**
     * Register a new route
     *
     * @param $action string
     * @param callable $callback Called when current URL matches provided action
     */
    public function addRoute($action, $callback)
    {
        $action = trim($action, '/');
        $this->routes[$action] = $callback;
    }

    /**
     * Dispatch the router
     *
     * @param $action string
     * @throws \Exception
     */
    public function dispatch($action)
    {
        $action = trim($action, '/');

        if (!isset($this->routes[$action])) {
            throw new \Exception('Route could not be found');
        }

        $callback = $this->routes[$action];

        $class = $callback[0];
        $method = $callback[1];

        $request = Request::createFromGlobals();

        call_user_func([new $class, $method], $request);
    }
}
