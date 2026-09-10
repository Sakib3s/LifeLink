<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Requires an authenticated user holding one of the given roles.
 * Registered as "RoleMiddleware:donor,admin".
 *
 * Controllers still call requireRole() as a second, co-located guard —
 * this middleware is defence in depth, not the only check.
 */
final class RoleMiddleware
{
    /** @var array<int, string> */
    private array $roles;

    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }

    public function handle(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            if ($request->wantsJson()) {
                Response::json(['message' => 'Authentication required.'], 401);
            }
            Session::set('_intended', $request->path());
            Session::flash('warning', 'Please sign in to continue.');
            Response::redirect('/login');
        }

        if (!in_array($user['role'], $this->roles, true)) {
            throw new HttpException(403);
        }
    }
}
