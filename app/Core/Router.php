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

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        $route = $this->routes[$method][$path];
        $this->runMiddleware($route['middleware']);
        $this->invoke($route['handler']);
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

    /** @param list<class-string> $middleware */
    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $class) {
            if (!class_exists($class)) {
                throw new RuntimeException("Middleware no encontrado: {$class}");
            }

            $instance = new $class();

            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException("Middleware sin método handle: {$class}");
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
