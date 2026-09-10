<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Requires an authenticated, active user. Unauthenticated visitors are sent
 * to /login (remembering where they were headed); AJAX callers get a 401.
 */
final class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        if ($request->wantsJson()) {
            Response::json(['message' => 'Authentication required.'], 401);
        }

        Session::set('_intended', $request->path());
        Session::flash('warning', 'Please sign in to continue.');
        Response::redirect('/login');
    }
}
