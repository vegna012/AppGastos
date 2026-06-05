<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class AreaRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function listAreas(): array
    {
        $statement = $this->db->query(
            'SELECT a.id_area, a.nombre, a.activo, a.creado_en,
                    u.nombre AS jefe_nombre
             FROM areas a
             LEFT JOIN usuarios u ON u.id_usuario = a.id_jefe_area
             WHERE a.eliminado_en IS NULL
             ORDER BY a.nombre ASC'
        );

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function getActiveUsers(): array
    {
        $statement = $this->db->query(
            'SELECT id_usuario, nombre
             FROM usuarios
             WHERE activo = 1
               AND eliminado_en IS NULL
             ORDER BY nombre ASC'
        );

        return $statement->fetchAll();
    }

    public function nameExists(string $nombre): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM areas
             WHERE nombre = :nombre
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['nombre' => $nombre]);

        return $statement->fetchColumn() !== false;
    }

    public function activeUserExists(int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM usuarios
             WHERE id_usuario = :id_usuario
               AND activo = 1
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id_usuario' => $userId]);

        return $statement->fetchColumn() !== false;
    }

    public function createArea(string $nombre, ?int $jefeAreaId, ?int $createdBy = null): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO areas (nombre, id_jefe_area, activo, creado_por)
             VALUES (:nombre, :id_jefe_area, 1, :creado_por)'
        );

        $statement->execute([
            'nombre' => $nombre,
            'id_jefe_area' => $jefeAreaId,
            'creado_por' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function toggleStatus(int $areaId): bool
    {
        $statement = $this->db->prepare(
            'UPDATE areas
             SET activo = NOT activo,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_area = :id_area
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_area' => $areaId,
            'actualizado_por' => $_SESSION['user_id'] ?? null,
        ]);

        return $statement->rowCount() > 0;
    }
}
