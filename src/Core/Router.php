<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

/**
 * Minimal front-controller router.
 *
 * Routes are registered as (method, path pattern, handler, middleware[]).
 * Path patterns support "{name}" placeholders which are passed to the
 * controller action as ordered arguments. Middleware are class names whose
 * handle(Request): void either returns (pass) or terminates the request.
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, params:array<int,string>, handler:mixed, middleware:array<int,string>}> */
    private array $routes = [];

    /** @var array{prefix:string, middleware:array<int,string>} */
    private array $context = ['prefix' => '', 'middleware' => []];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    /**
     * Register a batch of routes under a shared prefix and/or middleware set.
     *
     * @param array{prefix?:string, middleware?:array<int,string>} $attributes
     */
    public function group(array $attributes, callable $routes): void
    {
        $previous = $this->context;

        $this->context = [
            'prefix'     => $previous['prefix'] . ($attributes['prefix'] ?? ''),
            'middleware' => array_merge($previous['middleware'], $attributes['middleware'] ?? []),
        ];

        $routes($this);

        $this->context = $previous;
    }

    private function add(string $method, string $path, mixed $handler, array $middleware): void
    {
        $full = $this->context['prefix'] . $path;
        $full = '/' . trim($full, '/');
        if ($full === '/') {
            $full = '/';
        }

        $params = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            $full,
        );

        $this->routes[] = [
            'method'     => $method,
            'regex'      => '#^' . $regex . '$#',
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => array_merge($this->context['middleware'], $middleware),
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path   = $request->path();
        $pathMatchedButMethodNot = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $pathMatchedButMethodNot = true;
                continue;
            }

            array_shift($matches);
            $args = array_map('rawurldecode', $matches);

            foreach ($route['middleware'] as $middlewareSpec) {
                // Spec is "Class" or "Class:arg1,arg2" (args passed to the constructor).
                [$class, $argString] = array_pad(explode(':', $middlewareSpec, 2), 2, null);
                $args = $argString === null ? [] : explode(',', $argString);
                (new $class(...$args))->handle($request);
            }

            $this->invoke($route['handler'], $args, $request);
            return;
        }

        throw new HttpException($pathMatchedButMethodNot ? 405 : 404);
    }

    /**
     * @param array<int, string> $args
     */
    private function invoke(mixed $handler, array $args, Request $request): void
    {
        if ($handler instanceof \Closure) {
            $handler($request, ...$args);
            return;
        }

        [$class, $action] = $handler;
        /** @var Controller $controller */
        $controller = new $class($request);
        $controller->{$action}(...$args);
    }
}
