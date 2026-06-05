<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    /** @var array<string, array<string, callable|array{0: class-string, 1: string}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
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

        $this->invoke($this->routes[$method][$path]);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
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
