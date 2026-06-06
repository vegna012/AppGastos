<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class BudgetAvailabilityRepository extends Repository
{
    /** @return array{configured: bool, presupuesto: float, consumo: float, disponible: float}|null */
    public function getAvailability(int $areaId, int $year, int $month): ?array
    {
        $budget = $this->getBudgetAmount($areaId, $year, $month);

        if ($budget === null) {
            return [
                'configured' => false,
                'presupuesto' => 0.0,
                'consumo' => 0.0,
                'disponible' => 0.0,
            ];
        }

        $consumption = $this->getApprovedConsumption($areaId, $year, $month);

        return [
            'configured' => true,
            'presupuesto' => $budget,
            'consumo' => $consumption,
            'disponible' => $budget - $consumption,
        ];
    }

    private function getBudgetAmount(int $areaId, int $year, int $month): ?float
    {
        $statement = $this->db->prepare(
            'SELECT monto_presupuestado
             FROM presupuestos
             WHERE id_area = :id_area
               AND anio = :anio
               AND mes = :mes
               AND activo = 1
             LIMIT 1'
        );
        $statement->execute([
            'id_area' => $areaId,
            'anio' => $year,
            'mes' => $month,
        ]);
        $amount = $statement->fetchColumn();

        return $amount !== false ? (float) $amount : null;
    }

    private function getApprovedConsumption(int $areaId, int $year, int $month): float
    {
        $statement = $this->db->prepare(
            'SELECT COALESCE(SUM(g.total), 0)
             FROM gastos_cabecera g
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE g.id_area = :id_area
               AND YEAR(g.fecha_gasto) = :anio
               AND MONTH(g.fecha_gasto) = :mes
               AND eg.clave = :clave
               AND eg.activo = 1
               AND g.eliminado_en IS NULL'
        );
        $statement->execute([
            'id_area' => $areaId,
            'anio' => $year,
            'mes' => $month,
            'clave' => 'APROBADO',
        ]);

        return (float) $statement->fetchColumn();
    }
}
