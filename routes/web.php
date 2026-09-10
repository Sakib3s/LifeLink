<?php

declare(strict_types=1);

/**
 * Route table. $router is an App\Core\Router instance provided by the front
 * controller. Handlers are [Controller::class, 'method']. Middleware are
 * class-strings ("Class" or "Class:arg1,arg2") run before the action.
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PageController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

/** @var App\Core\Router $router */

// ── Public marketing pages ──────────────────────────────────────
$router->get('/', [PageController::class, 'landing']);
$router->get('/how-it-works', [PageController::class, 'howItWorks']);

// ── Guest auth ──────────────────────────────────────────────────
$router->group(['middleware' => [GuestMiddleware::class]], function (App\Core\Router $r): void {
    $r->get('/login', [AuthController::class, 'showLogin']);
    $r->get('/register', [AuthController::class, 'showRegister']);

    $r->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
    $r->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
});

// ── Authenticated ───────────────────────────────────────────────
$router->group(['middleware' => [AuthMiddleware::class, CsrfMiddleware::class]], function (App\Core\Router $r): void {
    $r->post('/logout', [AuthController::class, 'logout']);

    $r->get('/dashboard', [DashboardController::class, 'index']);
    $r->get('/admin', [DashboardController::class, 'index'], [RoleMiddleware::class . ':admin']);
});
