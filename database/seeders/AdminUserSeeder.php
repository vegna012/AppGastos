<?php

declare(strict_types=1);

namespace Database\Seeders;

use RuntimeException;

class AdminUserSeeder extends Seeder
{
    private const ADMIN_CORREO = 'admin@gastos.local';

    private const ADMIN_NOMBRE = 'Administrador Sistema';

    private const ADMIN_PASSWORD = 'Admin123*';

    private const AREA_NOMBRE = 'Administración';

    public function run(): void
    {
        if ($this->exists(
            'SELECT 1 FROM usuarios WHERE correo = :correo LIMIT 1',
            ['correo' => self::ADMIN_CORREO]
        )) {
            $this->skipped++;
            return;
        }

        $roleId = $this->findRoleId('Administrador');

        if ($roleId === null) {
            throw new RuntimeException('No se encontró el rol Administrador. Ejecute RolesSeeder primero.');
        }

        $areaId = $this->ensureArea();

        $statement = $this->db->prepare(
            'INSERT INTO usuarios (id_rol, id_area, nombre, correo, password_hash, activo)
             VALUES (:id_rol, :id_area, :nombre, :correo, :password_hash, 1)'
        );

        $statement->execute([
            'id_rol' => $roleId,
            'id_area' => $areaId,
            'nombre' => self::ADMIN_NOMBRE,
            'correo' => self::ADMIN_CORREO,
            'password_hash' => password_hash(self::ADMIN_PASSWORD, PASSWORD_DEFAULT),
        ]);

        $this->inserted++;
    }

    private function findRoleId(string $nombre): ?int
    {
        $statement = $this->db->prepare(
            'SELECT id_rol FROM roles WHERE nombre = :nombre AND eliminado_en IS NULL LIMIT 1'
        );
        $statement->execute(['nombre' => $nombre]);
        $id = $statement->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    private function ensureArea(): int
    {
        $statement = $this->db->prepare(
            'SELECT id_area FROM areas WHERE nombre = :nombre AND eliminado_en IS NULL LIMIT 1'
        );
        $statement->execute(['nombre' => self::AREA_NOMBRE]);
        $id = $statement->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insert = $this->db->prepare(
            'INSERT INTO areas (nombre, descripcion, activo)
             VALUES (:nombre, :descripcion, 1)'
        );
        $insert->execute([
            'nombre' => self::AREA_NOMBRE,
            'descripcion' => 'Área general para administración del sistema',
        ]);

        return (int) $this->db->lastInsertId();
    }
}
