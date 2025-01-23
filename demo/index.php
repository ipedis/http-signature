<?php

use Ipedis\Demo\HttpSignature\Action\DeleteAction;
use Ipedis\Demo\HttpSignature\Action\ExpiredGetAction;
use Ipedis\Demo\HttpSignature\Action\GetAction;
use Ipedis\Demo\HttpSignature\Action\InvalidPostAction;
use Ipedis\Demo\HttpSignature\Action\PostAction;
use Ipedis\Demo\HttpSignature\Router\Router;

require __DIR__.'/../vendor/autoload.php';

$router = new Router();

/**
 * Add Routes
 */
$router->addRoute('/get', [(new GetAction()), 'run']);
$router->addRoute('/expired', [(new ExpiredGetAction()), 'run']);
$router->addRoute('/post', [(new PostAction()), 'run']);
$router->addRoute('/invalid-post', [(new InvalidPostAction()), 'run']);
$router->addRoute('/delete', [(new DeleteAction()), 'run']);

/**
 * Dispatch current action
 */
$router->dispatch($_SERVER['REQUEST_URI']);
