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

    public static function render(string $view, array $data = [], array $options = []): void
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

        $layout = $options['layout'] ?? 'layouts/main';

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === false) {
            echo $content;

            return;
        }

        $layoutFile = self::$viewsPath
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, (string) $layout)
            . '.php';

        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout no encontrado: {$layout}");
        }

        require $layoutFile;
    }
}
