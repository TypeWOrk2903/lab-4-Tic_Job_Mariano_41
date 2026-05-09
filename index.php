<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use WebMovies\Controllers\HomeController;
use WebMovies\Support\Request;
use WebMovies\Support\Router;

$request = Request::fromGlobals();
$router  = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->dispatch($request);
