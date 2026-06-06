<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class CostCenterRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function listCostCenters(): array
    {
        $statement = $this->db->query(
            'SELECT cc.id_centro_costo, cc.id_area, cc.codigo, cc.nombre, cc.descripcion,
                    cc.activo, cc.creado_en, a.nombre AS area_nombre
             FROM centros_costos cc
             INNER JOIN areas a ON a.id_area = cc.id_area
             WHERE cc.eliminado_en IS NULL
             ORDER BY a.nombre ASC, cc.codigo ASC'
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

    public function codeExistsInArea(int $areaId, string $codigo): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM centros_costos
             WHERE id_area = :id_area
               AND codigo = :codigo
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute([
            'id_area' => $areaId,
            'codigo' => $codigo,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function createCostCenter(
        int $areaId,
        string $codigo,
        string $nombre,
        ?string $descripcion,
        ?int $createdBy = null
    ): int {
        $statement = $this->db->prepare(
            'INSERT INTO centros_costos (id_area, codigo, nombre, descripcion, activo, creado_por)
             VALUES (:id_area, :codigo, :nombre, :descripcion, 1, :creado_por)'
        );

        $statement->execute([
            'id_area' => $areaId,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'creado_por' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function toggleStatus(int $costCenterId): bool
    {
        $statement = $this->db->prepare(
            'UPDATE centros_costos
             SET activo = NOT activo,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_centro_costo = :id_centro_costo
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_centro_costo' => $costCenterId,
            'actualizado_por' => $_SESSION['user_id'] ?? null,
        ]);

        return $statement->rowCount() > 0;
    }
}
