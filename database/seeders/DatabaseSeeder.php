<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;
use RuntimeException;
use Throwable;

class DatabaseSeeder
{
    private PDO $db;

    /** @var array<string, array{inserted: int, skipped: int}> */
    private array $summary = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        $seeders = [
            new RolesSeeder($this->db),
            new EstatusGastoSeeder($this->db),
            new CategoriasGastoSeeder($this->db),
            new ConceptosDeducibilidadSeeder($this->db),
            new AdminUserSeeder($this->db),
        ];

        $this->db->beginTransaction();

        try {
            foreach ($seeders as $seeder) {
                $seeder->run();

                $this->summary[$seeder->getName()] = [
                    'inserted' => $seeder->getInserted(),
                    'skipped' => $seeder->getSkipped(),
                ];
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw new RuntimeException(
                'Error al ejecutar seeders: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    /** @return array<string, array{inserted: int, skipped: int}> */
    public function getSummary(): array
    {
        return $this->summary;
    }
}
