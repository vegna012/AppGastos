<?php

declare(strict_types=1);

namespace App\Core;

class RouteContext
{
    /** @var array<string, string> */
    private static array $params = [];

    /** @param array<string, string> $params */
    public static function setParams(array $params): void
    {
        self::$params = $params;
    }

    public static function param(string $name, ?string $default = null): ?string
    {
        return self::$params[$name] ?? $default;
    }
}
