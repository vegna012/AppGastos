<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    private static string $viewsPath = '';

    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public static function render(string $view, array $data = []): void
    {
        if (self::$viewsPath === '') {
            throw new RuntimeException('La ruta de vistas no está configurada.');
        }

        $viewFile = self::$viewsPath
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $view)
            . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}
