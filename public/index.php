<?php

declare(strict_types=1);

/**
 * LifeLink front controller — the only PHP entry point under the web root.
 * All requests are rewritten here by the web server.
 */

use App\Core\Config;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

// Locate the application root (the folder holding src/, views/, routes/,
// vendor/ and .env). Locally that is one level above public/. On shared
// hosting the app folder sits OUTSIDE the web root — e.g. ~/lifelink-app
// next to ~/public_html — so a few known layouts are tried in order.
$appRoot = null;
foreach ([
    dirname(__DIR__),                       // dev / standard: public/ inside the project
    dirname(__DIR__, 3) . '/lifelink-app',  // public_html/rtm/lifelink → ~/lifelink-app
    dirname(__DIR__, 2) . '/lifelink-app',  // public_html/lifelink     → ~/lifelink-app
    __DIR__ . '/../lifelink-app',           // sibling of the web folder
] as $candidate) {
    if (is_file($candidate . '/src/bootstrap.php')) {
        $appRoot = $candidate;
        break;
    }
}
if ($appRoot === null) {
    http_response_code(500);
    exit('LifeLink application files were not found. Check the deployment folder layout.');
}

require $appRoot . '/src/bootstrap.php';

$router = new Router();
require BASE_PATH . '/routes/web.php';

$request = new Request();

try {
    $router->dispatch($request);
} catch (HttpException $e) {
    render_error($request, $e->status, $e->getMessage());
} catch (\Throwable $e) {
    error_log((string) $e);

    if ((bool) Config::get('app.debug', false)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "500 Internal Server Error\n\n{$e}\n";
        exit;
    }

    render_error($request, 500, 'Something went wrong on our end.');
}

/**
 * Render a friendly error page (HTML) or a JSON envelope for API/AJAX callers.
 */
function render_error(Request $request, int $status, string $message): never
{
    http_response_code($status);

    if ($request->wantsJson()) {
        Response::json(['message' => $message ?: 'Error', 'status' => $status], $status);
    }

    $view = match ($status) {
        403 => 'public/403',
        404 => 'public/404',
        default => 'public/error',
    };

    try {
        Response::html(View::render($view, ['status' => $status, 'message' => $message]), $status);
    } catch (\Throwable) {
        Response::html("<h1>{$status}</h1><p>" . htmlspecialchars($message) . '</p>', $status);
    }
}
