<?php

declare(strict_types=1);

namespace Maksa\Core;

use Maksa\Core\Exceptions\ApiException;

/**
 * Tiny regex router. Routes use `{name}` placeholders, e.g. /campaigns/{slug}.
 * Handlers are [ControllerClass, 'method'] and receive the Request.
 */
final class Router
{
    /** @var array<int,array{method:string,regex:string,vars:list<string>,handler:array{0:class-string,1:string},middleware:list<callable>}> */
    private array $routes = [];

    /** @param list<callable> $middleware */
    public function add(string $method, string $pattern, array $handler, array $middleware = []): void
    {
        $vars = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_]+)\}/', static function ($m) use (&$vars) {
            $vars[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method'     => strtoupper($method),
            'regex'      => '#^' . $regex . '$#',
            'vars'       => $vars,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $p, array $h, array $m = []): void    { $this->add('GET', $p, $h, $m); }
    public function post(string $p, array $h, array $m = []): void   { $this->add('POST', $p, $h, $m); }
    public function patch(string $p, array $h, array $m = []): void  { $this->add('PATCH', $p, $h, $m); }
    public function delete(string $p, array $h, array $m = []): void { $this->add('DELETE', $p, $h, $m); }

    public function dispatch(Request $request): void
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $request->method) {
                continue;
            }

            array_shift($matches);
            $params = [];
            foreach ($route['vars'] as $i => $name) {
                $params[$name] = urldecode($matches[$i] ?? '');
            }
            $request->params = $params;

            // Middleware may mutate the request (e.g. attach the authenticated user).
            foreach ($route['middleware'] as $mw) {
                $mw($request);
            }

            [$class, $method] = $route['handler'];
            (new $class())->{$method}($request);
            return;
        }

        if ($pathMatched) {
            throw new ApiException(405, 'method_not_allowed', 'این متد برای این مسیر مجاز نیست.');
        }
        throw ApiException::notFound('مسیر مورد نظر یافت نشد.', 'route_not_found');
    }
}
