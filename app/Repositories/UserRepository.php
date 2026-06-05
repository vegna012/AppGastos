<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class UserRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function listUsers(): array
    {
        $statement = $this->db->query(
            'SELECT u.id_usuario, u.nombre, u.correo, u.activo,
                    r.nombre AS rol_nombre,
                    a.nombre AS area_nombre
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             INNER JOIN areas a ON a.id_area = u.id_area
             WHERE u.eliminado_en IS NULL
             ORDER BY u.nombre ASC'
        );

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function getRoles(): array
    {
        $statement = $this->db->query(
            'SELECT id_rol, nombre
             FROM roles
             WHERE activo = 1
               AND eliminado_en IS NULL
             ORDER BY nombre ASC'
        );

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function getAreas(): array
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

    public function emailExists(string $email): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM usuarios
             WHERE correo = :correo
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['correo' => $email]);

        return $statement->fetchColumn() !== false;
    }

    public function roleExists(int $roleId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM roles
             WHERE id_rol = :id_rol
               AND activo = 1
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id_rol' => $roleId]);

        return $statement->fetchColumn() !== false;
    }

    public function areaExists(int $areaId): bool
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

    public function createUser(
        string $nombre,
        string $correo,
        string $passwordHash,
        int $roleId,
        int $areaId,
        ?int $createdBy = null
    ): int {
        $statement = $this->db->prepare(
            'INSERT INTO usuarios (id_rol, id_area, nombre, correo, password_hash, activo, creado_por)
             VALUES (:id_rol, :id_area, :nombre, :correo, :password_hash, 1, :creado_por)'
        );

        $statement->execute([
            'id_rol' => $roleId,
            'id_area' => $areaId,
            'nombre' => $nombre,
            'correo' => $correo,
            'password_hash' => $passwordHash,
            'creado_por' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function toggleStatus(int $userId): bool
    {
        $statement = $this->db->prepare(
            'UPDATE usuarios
             SET activo = NOT activo,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_usuario = :id_usuario
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_usuario' => $userId,
            'actualizado_por' => $_SESSION['user_id'] ?? null,
        ]);

        return $statement->rowCount() > 0;
    }
}
