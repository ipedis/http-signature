<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$router->addRoute('/get', [GetAction::class, 'run']);
$router->addRoute('/expired', [ExpiredGetAction::class, 'run']);
$router->addRoute('/post', [PostAction::class, 'run']);
$router->addRoute('/invalid-post', [InvalidPostAction::class, 'run']);
$router->addRoute('/delete', [DeleteAction::class, 'run']);

/**
 * Dispatch current action
 */
$router->dispatch($_SERVER['REQUEST_URI']);
