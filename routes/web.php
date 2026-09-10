<?php

declare(strict_types=1);

/**
 * Route table. $router is an App\Core\Router instance provided by the front
 * controller. Handlers are [Controller::class, 'method']. Middleware are
 * class-strings ("Class" or "Class:arg1,arg2") run before the action.
 */

use App\Controllers\AdminController;
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
});

// ── Admin console ───────────────────────────────────────────────
$router->group([
    'prefix'     => '/admin',
    'middleware' => [AuthMiddleware::class, CsrfMiddleware::class, RoleMiddleware::class . ':admin'],
], function (App\Core\Router $r): void {
    $r->get('', [AdminController::class, 'dashboard']);

    $r->get('/users', [AdminController::class, 'users']);
    $r->post('/users/{id}/suspend', [AdminController::class, 'suspendUser']);
    $r->post('/users/{id}/activate', [AdminController::class, 'activateUser']);

    $r->get('/verifications', [AdminController::class, 'verifications']);
    $r->post('/verifications/donor/{id}/approve', [AdminController::class, 'approveDonor']);
    $r->post('/verifications/donor/{id}/reject', [AdminController::class, 'rejectDonor']);
    $r->post('/verifications/bank/{id}/approve', [AdminController::class, 'approveBank']);
    $r->post('/verifications/bank/{id}/reject', [AdminController::class, 'rejectBank']);

    $r->get('/requests', [AdminController::class, 'requests']);

    $r->get('/settings', [AdminController::class, 'settings']);
    $r->post('/settings', [AdminController::class, 'updateSettings']);

    $r->get('/audit', [AdminController::class, 'audit']);

    $r->get('/reports', [AdminController::class, 'reports']);
    $r->get('/reports/export', [AdminController::class, 'exportReport']);
});
