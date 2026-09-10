<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * For login/register pages — an already-authenticated user is bounced to
 * their dashboard.
 */
final class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }
    }
}
