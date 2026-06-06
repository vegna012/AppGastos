<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class BudgetRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function listBudgets(): array
    {
        $statement = $this->db->query(
            'SELECT p.id_presupuesto, p.anio, p.mes, p.monto_presupuestado, p.creado_en,
                    a.nombre AS area_nombre
             FROM presupuestos p
             INNER JOIN areas a ON a.id_area = p.id_area
             ORDER BY p.anio DESC, p.mes DESC'
        );

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function getActiveAreas(): array
    {
        $statement = $this->db->query(
            'SELECT id_area, nombre
             FROM areas
             WHERE activo = 1
               AND eliminado_en IS NULL
             ORDER BY nombre ASC'
        );

        return $statement->fetchAll();
    }

    public function activeAreaExists(int $areaId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM areas
             WHERE id_area = :id_area
               AND activo = 1
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id_area' => $areaId]);

        return $statement->fetchColumn() !== false;
    }

    public function existsForAreaPeriod(int $areaId, int $year, int $month): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM presupuestos
             WHERE id_area = :id_area
               AND anio = :anio
               AND mes = :mes
             LIMIT 1'
        );
        $statement->execute([
            'id_area' => $areaId,
            'anio' => $year,
            'mes' => $month,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function createBudget(int $areaId, int $year, int $month, float $amount, ?int $createdBy = null): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO presupuestos (id_area, anio, mes, monto_presupuestado, creado_por)
             VALUES (:id_area, :anio, :mes, :monto_presupuestado, :creado_por)'
        );

        $statement->execute([
            'id_area' => $areaId,
            'anio' => $year,
            'mes' => $month,
            'monto_presupuestado' => $amount,
            'creado_por' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
