<?php

declare(strict_types=1);

namespace Database\Seeders;

class CategoriasGastoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Viáticos', 'descripcion' => 'Gastos de viaje y desplazamiento'],
            ['nombre' => 'Transporte', 'descripcion' => 'Taxi, combustible, peajes y movilidad'],
            ['nombre' => 'Alimentos', 'descripcion' => 'Comidas y consumos durante actividades laborales'],
            ['nombre' => 'Hospedaje', 'descripcion' => 'Hoteles y alojamiento'],
            ['nombre' => 'Materiales de oficina', 'descripcion' => 'Papelería, insumos y consumibles'],
            ['nombre' => 'Servicios profesionales', 'descripcion' => 'Consultoría, asesoría y servicios externos'],
            ['nombre' => 'Mantenimiento', 'descripcion' => 'Reparación y mantenimiento de activos'],
            ['nombre' => 'Comunicaciones', 'descripcion' => 'Telefonía, internet y servicios de comunicación'],
            ['nombre' => 'Capacitación', 'descripcion' => 'Cursos, talleres y formación del personal'],
            ['nombre' => 'Otros', 'descripcion' => 'Gastos operativos no clasificados en otra categoría'],
        ];

        $statement = $this->db->prepare(
            'INSERT INTO categorias_gasto (nombre, descripcion, activo)
             VALUES (:nombre, :descripcion, 1)'
        );

        foreach ($categorias as $categoria) {
            if ($this->exists(
                'SELECT 1 FROM categorias_gasto WHERE nombre = :nombre AND eliminado_en IS NULL LIMIT 1',
                ['nombre' => $categoria['nombre']]
            )) {
                $this->skipped++;
                continue;
            }

            $statement->execute($categoria);
            $this->inserted++;
        }
    }
}
