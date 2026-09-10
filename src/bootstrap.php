<?php

declare(strict_types=1);

/**
 * Application bootstrap: environment, error handling, timezone, session.
 * Included once by public/index.php (and by the PHPUnit bootstrap) before
 * any routing happens.
 */

use App\Core\Config;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// ── Environment ─────────────────────────────────────────────────
if (is_file(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

Config::load();

// ── Errors ──────────────────────────────────────────────────────
$debug = (bool) Config::get('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

// ── Session ─────────────────────────────────────────────────────
if (PHP_SAPI !== 'cli') {
    Session::start();
}
