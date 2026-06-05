<?php

declare(strict_types=1);

namespace Database\Seeders;

class ConceptosDeducibilidadSeeder extends Seeder
{
    public function run(): void
    {
        $conceptos = [
            [
                'clave' => 'DEDUCIBLE',
                'nombre' => 'Deducible',
                'descripcion' => 'Importe totalmente deducible fiscalmente',
            ],
            [
                'clave' => 'NO_DEDUCIBLE',
                'nombre' => 'No deducible',
                'descripcion' => 'Importe sin deducibilidad fiscal',
            ],
            [
                'clave' => 'PARCIAL_DEDUCIBLE',
                'nombre' => 'Parcialmente deducible',
                'descripcion' => 'Importe con deducibilidad parcial',
            ],
        ];

        $statement = $this->db->prepare(
            'INSERT INTO conceptos_deducibilidad (clave, nombre, descripcion, activo)
             VALUES (:clave, :nombre, :descripcion, 1)'
        );

        foreach ($conceptos as $concepto) {
            if ($this->exists(
                'SELECT 1 FROM conceptos_deducibilidad WHERE clave = :clave LIMIT 1',
                ['clave' => $concepto['clave']]
            )) {
                $this->skipped++;
                continue;
            }

            $statement->execute($concepto);
            $this->inserted++;
        }
    }
}
