<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

/**
 * Base controller. Provides request access plus response, validation and
 * authorisation helpers so concrete controllers stay thin.
 */
abstract class Controller
{
    public function __construct(protected Request $request)
    {
    }

    // ── Rendering ────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], int $status = 200): never
    {
        Response::html(View::render($view, $data), $status);
    }

    /**
     * @param array<string, mixed>|list<mixed> $data
     */
    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function redirect(string $to): never
    {
        Response::redirect($to);
    }

    /** Redirect back to the referring page (or a fallback). */
    protected function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $sameHost = $ref !== '' && str_contains($ref, $host);

        Response::redirect($sameHost ? $ref : $fallback);
    }

    // ── Validation ───────────────────────────────────────────────

    /**
     * Validate request input. On failure: flash errors + old input and
     * redirect back (HTML) or return a 422 JSON payload (AJAX).
     *
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     * @return array<string, mixed> validated data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($this->request->all(), $rules, $messages);

        if ($validator->passes()) {
            return $validator->validated();
        }

        if ($this->request->wantsJson()) {
            $this->json([
                'message' => 'The submitted data is invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        Session::set('_errors', $validator->errors());
        Session::set('_old', $this->safeOld());
        $this->back();
    }

    /** Old input minus sensitive fields. @return array<string, mixed> */
    private function safeOld(): array
    {
        $data = $this->request->all();
        unset($data['password'], $data['password_confirmation'], $data['_csrf'], $data['_method']);

        return $data;
    }

    // ── Authorisation ────────────────────────────────────────────

    /** @return array<string, mixed> the authenticated user row */
    protected function requireAuth(): array
    {
        $user = Auth::user();
        if ($user === null) {
            if ($this->request->wantsJson()) {
                $this->json(['message' => 'Authentication required.'], 401);
            }
            Session::flash('warning', 'Please sign in to continue.');
            Session::set('_intended', $this->request->path());
            $this->redirect('/login');
        }

        return $user;
    }

    /**
     * Ensure the caller is authenticated AND holds one of the given roles.
     * Server-side gate — never rely on hidden nav links alone.
     *
     * @param string ...$roles
     * @return array<string, mixed> the authenticated user row
     */
    protected function requireRole(string ...$roles): array
    {
        $user = $this->requireAuth();

        if (!in_array($user['role'], $roles, true)) {
            throw new HttpException(403);
        }

        return $user;
    }

    protected function verifyCsrf(): void
    {
        if (!Csrf::validate($this->request->csrfToken())) {
            throw new HttpException(419, 'CSRF token mismatch.');
        }
    }
}
