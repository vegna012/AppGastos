<?php

declare(strict_types=1);

namespace Database\Seeders;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso completo a configuración y administración del sistema',
            ],
            [
                'nombre' => 'Aprobador',
                'descripcion' => 'Aprueba o rechaza gastos de su área asignada',
            ],
            [
                'nombre' => 'Capturista',
                'descripcion' => 'Registra y gestiona gastos de su área',
            ],
        ];

        $statement = $this->db->prepare(
            'INSERT INTO roles (nombre, descripcion, activo)
             VALUES (:nombre, :descripcion, 1)'
        );

        foreach ($roles as $role) {
            if ($this->exists(
                'SELECT 1 FROM roles WHERE nombre = :nombre AND eliminado_en IS NULL LIMIT 1',
                ['nombre' => $role['nombre']]
            )) {
                $this->skipped++;
                continue;
            }

            $statement->execute($role);
            $this->inserted++;
        }
    }
}
