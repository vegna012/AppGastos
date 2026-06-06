<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    /** @var array<string, array<string, array{handler: callable|array{0: class-string, 1: string}, middleware: list<class-string>}> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?? '/');

        $route = $this->findRoute($method, $path);

        if ($route === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        $this->runMiddleware($route['middleware']);
        $this->invoke($route['handler']);
    }

    /** @return array{handler: callable|array{0: class-string, 1: string}, middleware: list<class-string>}|null */
    private function findRoute(string $method, string $path): ?array
    {
        if (isset($this->routes[$method][$path])) {
            RouteContext::setParams([]);
            return $this->routes[$method][$path];
        }

        foreach ($this->routes[$method] as $pattern => $route) {
            $params = $this->matchPattern($pattern, $path);

            if ($params !== null) {
                RouteContext::setParams($params);
                return $route;
            }
        }

        return null;
    }

    /** @return array<string, string>|null */
    private function matchPattern(string $pattern, string $path): ?array
    {
        if (!str_contains($pattern, '{')) {
            return null;
        }

        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

  /**
   * @param callable|array{0: class-string, 1: string} $handler
   * @param list<class-string> $middleware
   */
    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[$method][$this->normalizePath($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /** @param list<class-string|array{0: class-string, 1: list<mixed>}> $middleware */
    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $entry) {
            if (is_string($entry)) {
                $class = $entry;
                $instance = new $class();
            } elseif (is_array($entry) && isset($entry[0]) && is_string($entry[0])) {
                $class = $entry[0];
                $params = $entry[1] ?? [];

                if (!is_array($params)) {
                    throw new RuntimeException('Parámetros de middleware inválidos.');
                }

                $instance = new $class(...$params);
            } else {
                throw new RuntimeException('Middleware inválido.');
            }

            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException('Middleware sin método handle: ' . $instance::class);
            }

            $instance->handle();
        }
    }

    private function invoke(callable|array $handler): void
    {
        if (is_array($handler)) {
            [$class, $action] = $handler;

            if (!class_exists($class)) {
                throw new RuntimeException("Controlador no encontrado: {$class}");
            }

            $controller = new $class();

            if (!method_exists($controller, $action)) {
                throw new RuntimeException("Acción no encontrada: {$class}::{$action}");
            }

            $controller->{$action}();
            return;
        }

        $handler();
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
