<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class ApprovalRepository extends Repository
{
    public function getStatusIdByKey(string $key): ?int
    {
        $statement = $this->db->prepare(
            'SELECT id_estatus_gasto FROM estatus_gasto
             WHERE clave = :clave
               AND activo = 1
             LIMIT 1'
        );
        $statement->execute(['clave' => $key]);
        $id = $statement->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function validateSent(int $expenseId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM gastos_cabecera g
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE g.id_gasto_cabecera = :id_gasto_cabecera
               AND g.eliminado_en IS NULL
               AND eg.clave = :clave
               AND eg.activo = 1
             LIMIT 1'
        );
        $statement->execute([
            'id_gasto_cabecera' => $expenseId,
            'clave' => 'ENVIADO',
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function approveExpense(
        int $expenseId,
        int $approverId,
        int $sentStatusId,
        int $approvedStatusId
    ): bool {
        $statement = $this->db->prepare(
            'UPDATE gastos_cabecera
             SET id_estatus_gasto = :id_estatus_gasto,
                 fecha_aprobacion = CURRENT_TIMESTAMP,
                 aprobado_por = :aprobado_por,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_gasto_cabecera = :id_gasto_cabecera
               AND id_estatus_gasto = :id_estatus_enviado
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_estatus_gasto' => $approvedStatusId,
            'aprobado_por' => $approverId,
            'actualizado_por' => $approverId,
            'id_gasto_cabecera' => $expenseId,
            'id_estatus_enviado' => $sentStatusId,
        ]);

        return $statement->rowCount() > 0;
    }

    /** @return list<array<string, mixed>> */
    public function listSentExpenses(): array
    {
        $statement = $this->db->prepare(
            'SELECT g.id_gasto_cabecera, g.folio, g.fecha_gasto, g.fecha_envio_aprobacion,
                    u.nombre AS solicitante_nombre,
                    a.nombre AS area_nombre,
                    cc.codigo AS centro_codigo,
                    cc.nombre AS centro_nombre,
                    eg.nombre AS estatus_nombre
             FROM gastos_cabecera g
             INNER JOIN usuarios u ON u.id_usuario = g.id_usuario
             INNER JOIN areas a ON a.id_area = g.id_area
             INNER JOIN centros_costos cc ON cc.id_centro_costo = g.id_centro_costo
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE eg.clave = :clave
               AND eg.activo = 1
               AND g.eliminado_en IS NULL
             ORDER BY g.fecha_envio_aprobacion DESC'
        );
        $statement->execute(['clave' => 'ENVIADO']);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function getSentExpenseById(int $expenseId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT g.id_gasto_cabecera, g.folio, g.fecha_gasto, g.observaciones,
                    g.creado_en, g.fecha_envio_aprobacion,
                    u.nombre AS solicitante_nombre,
                    a.nombre AS area_nombre,
                    cc.codigo AS centro_codigo,
                    cc.nombre AS centro_nombre,
                    eg.nombre AS estatus_nombre,
                    eg.clave AS estatus_clave
             FROM gastos_cabecera g
             INNER JOIN usuarios u ON u.id_usuario = g.id_usuario
             INNER JOIN areas a ON a.id_area = g.id_area
             INNER JOIN centros_costos cc ON cc.id_centro_costo = g.id_centro_costo
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE g.id_gasto_cabecera = :id_gasto_cabecera
               AND eg.clave = :clave
               AND eg.activo = 1
               AND g.eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute([
            'id_gasto_cabecera' => $expenseId,
            'clave' => 'ENVIADO',
        ]);
        $expense = $statement->fetch();

        return $expense !== false ? $expense : null;
    }
}
