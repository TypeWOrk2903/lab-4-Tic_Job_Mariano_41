<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use WebMovies\Controllers\Web\WebController;
use WebMovies\Controllers\Web\AuthController;
use WebMovies\Controllers\Admin\AdminController;
use WebMovies\Support\Request;
use WebMovies\Support\Router;

$request = Request::fromGlobals();
$router  = new Router();

// ── Área Pública ─────────────────────────────────────
$router->get('/',       [WebController::class, 'home']);
$router->get('/filme',  [WebController::class, 'movieDetail']);

// ── Autenticação ──────────────────────────────────────
$router->get('/register',  [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'registerSubmit']);
$router->get('/login',     [AuthController::class, 'loginForm']);
$router->post('/login',    [AuthController::class, 'loginSubmit']);
$router->get('/forget',    [AuthController::class, 'forgetForm']);
$router->post('/forget',   [AuthController::class, 'forgetSubmit']);
$router->get('/logout',    [AuthController::class, 'logOut']);

// ── Painel Admin ──────────────────────────────────────
$router->get('/admin',          [AdminController::class, 'dashboard']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings',[AdminController::class, 'saveSettings']);
$router->get('/admin/logout',   [AdminController::class, 'logOut']);

$router->dispatch($request);
