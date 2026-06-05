<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

abstract class Seeder
{
    protected PDO $db;

    protected int $inserted = 0;

    protected int $skipped = 0;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    abstract public function run(): void;

    public function getInserted(): int
    {
        return $this->inserted;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getName(): string
    {
        $class = static::class;

        return substr($class, strrpos($class, '\\') + 1);
    }

    protected function exists(string $sql, array $params = []): bool
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }
}
