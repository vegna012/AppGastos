<?php

declare(strict_types=1);

namespace Database\Seeders;

class EstatusGastoSeeder extends Seeder
{
    public function run(): void
    {
        $estatus = [
            [
                'clave' => 'BORRADOR',
                'nombre' => 'Borrador',
                'descripcion' => 'Gasto en captura, editable por el capturista',
                'orden_flujo' => 1,
            ],
            [
                'clave' => 'ENVIADO',
                'nombre' => 'Enviado',
                'descripcion' => 'Gasto enviado a aprobación del jefe de área',
                'orden_flujo' => 2,
            ],
            [
                'clave' => 'APROBADO',
                'nombre' => 'Aprobado',
                'descripcion' => 'Gasto autorizado por el aprobador',
                'orden_flujo' => 3,
            ],
            [
                'clave' => 'RECHAZADO',
                'nombre' => 'Rechazado',
                'descripcion' => 'Gasto rechazado, pendiente de corrección',
                'orden_flujo' => 4,
            ],
            [
                'clave' => 'CORRECCION',
                'nombre' => 'Corrección',
                'descripcion' => 'Gasto en corrección tras rechazo',
                'orden_flujo' => 5,
            ],
        ];

        $statement = $this->db->prepare(
            'INSERT INTO estatus_gasto (clave, nombre, descripcion, orden_flujo, activo)
             VALUES (:clave, :nombre, :descripcion, :orden_flujo, 1)'
        );

        foreach ($estatus as $item) {
            if ($this->exists(
                'SELECT 1 FROM estatus_gasto WHERE clave = :clave LIMIT 1',
                ['clave' => $item['clave']]
            )) {
                $this->skipped++;
                continue;
            }

            $statement->execute($item);
            $this->inserted++;
        }
    }
}
