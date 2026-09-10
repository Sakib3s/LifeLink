<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Exceptions\HttpException;
use App\Core\Request;

/**
 * Rejects any state-changing request (POST/PUT/PATCH/DELETE) whose CSRF token
 * is missing or wrong. Applied to every authenticated route group.
 */
final class CsrfMiddleware
{
    private const READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request): void
    {
        if (in_array($request->method(), self::READ_METHODS, true)) {
            return;
        }

        if (!Csrf::validate($request->csrfToken())) {
            throw new HttpException(419, 'Your session has expired. Please refresh the page and try again.');
        }
    }
}
