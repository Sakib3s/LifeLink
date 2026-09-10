<?php

declare(strict_types=1);

/**
 * Route table. $router is a App\Core\Router instance provided by the front
 * controller. Handlers are [Controller::class, 'method']. Middleware are
 * class-strings resolved and run before the action.
 *
 * Route groups are added module by module as the app is built.
 */

use App\Controllers\PageController;

/** @var App\Core\Router $router */

// ── Public marketing pages ──────────────────────────────────────
$router->get('/', [PageController::class, 'landing']);
$router->get('/how-it-works', [PageController::class, 'howItWorks']);
