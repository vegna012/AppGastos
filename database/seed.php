<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => BASE_PATH . '/app/',
        'Database\\Seeders\\' => BASE_PATH . '/database/seeders/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH . '/config/env.php';

loadEnv(BASE_PATH . '/.env');

use App\Core\Database;
use Database\Seeders\DatabaseSeeder;

echo "Ejecutando seeders..." . PHP_EOL;

try {
    $seeder = new DatabaseSeeder(Database::getConnection());
    $seeder->run();

    echo PHP_EOL . "Resumen:" . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;

    $totalInserted = 0;
    $totalSkipped = 0;

    foreach ($seeder->getSummary() as $name => $counts) {
        echo sprintf(
            "%-35s insertados: %d | omitidos: %d",
            $name,
            $counts['inserted'],
            $counts['skipped']
        ) . PHP_EOL;

        $totalInserted += $counts['inserted'];
        $totalSkipped += $counts['skipped'];
    }

    echo str_repeat('-', 50) . PHP_EOL;
    echo sprintf('Total insertados: %d | Total omitidos: %d', $totalInserted, $totalSkipped) . PHP_EOL;
    echo PHP_EOL . 'Seeders completados correctamente.' . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
