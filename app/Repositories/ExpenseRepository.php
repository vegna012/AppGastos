<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class ExpenseRepository extends Repository
{
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

    /** @return list<array<string, mixed>> */
    public function getActiveCostCenters(): array
    {
        $statement = $this->db->query(
            'SELECT cc.id_centro_costo, cc.codigo, cc.nombre, cc.id_area,
                    a.nombre AS area_nombre
             FROM centros_costos cc
             INNER JOIN areas a ON a.id_area = cc.id_area
             WHERE cc.activo = 1
               AND cc.eliminado_en IS NULL
               AND a.eliminado_en IS NULL
             ORDER BY a.nombre ASC, cc.codigo ASC'
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

    public function activeCostCenterBelongsToArea(int $costCenterId, int $areaId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM centros_costos
             WHERE id_centro_costo = :id_centro_costo
               AND id_area = :id_area
               AND activo = 1
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute([
            'id_centro_costo' => $costCenterId,
            'id_area' => $areaId,
        ]);

        return $statement->fetchColumn() !== false;
    }

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

    public function getDraftStatusId(): ?int
    {
        return $this->getStatusIdByKey('BORRADOR');
    }

    public function validateDraft(int $expenseId): bool
    {
        return $this->isDraft($expenseId);
    }

    public function folioExists(string $folio): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM gastos_cabecera
             WHERE folio = :folio
             LIMIT 1'
        );
        $statement->execute(['folio' => $folio]);

        return $statement->fetchColumn() !== false;
    }

    public function generateFolio(int $userId): string
    {
        do {
            $folio = sprintf(
                'G-%s-%d-%s',
                date('YmdHis'),
                $userId,
                substr(bin2hex(random_bytes(3)), 0, 5)
            );
        } while ($this->folioExists($folio));

        return $folio;
    }

    public function createExpense(
        string $folio,
        int $userId,
        int $areaId,
        int $costCenterId,
        int $statusId,
        string $expenseDate,
        ?string $observations
    ): int {
        $statement = $this->db->prepare(
            'INSERT INTO gastos_cabecera (
                folio, id_usuario, id_area, id_centro_costo, id_estatus_gasto,
                fecha_gasto, concepto_general, observaciones, creado_por
             ) VALUES (
                :folio, :id_usuario, :id_area, :id_centro_costo, :id_estatus_gasto,
                :fecha_gasto, :concepto_general, :observaciones, :creado_por
             )'
        );

        $statement->execute([
            'folio' => $folio,
            'id_usuario' => $userId,
            'id_area' => $areaId,
            'id_centro_costo' => $costCenterId,
            'id_estatus_gasto' => $statusId,
            'fecha_gasto' => $expenseDate,
            'concepto_general' => 'Gasto en captura',
            'observaciones' => $observations !== '' ? $observations : null,
            'creado_por' => $userId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function getExpenseById(int $expenseId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT g.id_gasto_cabecera, g.id_usuario, g.id_area, g.id_centro_costo,
                    g.fecha_gasto, g.observaciones, g.id_estatus_gasto,
                    eg.clave AS estatus_clave
             FROM gastos_cabecera g
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE g.id_gasto_cabecera = :id_gasto_cabecera
               AND g.eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id_gasto_cabecera' => $expenseId]);
        $expense = $statement->fetch();

        return $expense !== false ? $expense : null;
    }

    public function isOwner(int $expenseId, int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM gastos_cabecera
             WHERE id_gasto_cabecera = :id_gasto_cabecera
               AND id_usuario = :id_usuario
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute([
            'id_gasto_cabecera' => $expenseId,
            'id_usuario' => $userId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function isDraft(int $expenseId): bool
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
            'clave' => 'BORRADOR',
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function updateExpense(
        int $expenseId,
        int $userId,
        int $draftStatusId,
        int $areaId,
        int $costCenterId,
        string $expenseDate,
        ?string $observations
    ): bool {
        $statement = $this->db->prepare(
            'UPDATE gastos_cabecera
             SET id_area = :id_area,
                 id_centro_costo = :id_centro_costo,
                 fecha_gasto = :fecha_gasto,
                 observaciones = :observaciones,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_gasto_cabecera = :id_gasto_cabecera
               AND id_usuario = :id_usuario
               AND id_estatus_gasto = :id_estatus_gasto
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_area' => $areaId,
            'id_centro_costo' => $costCenterId,
            'fecha_gasto' => $expenseDate,
            'observaciones' => $observations !== '' ? $observations : null,
            'actualizado_por' => $userId,
            'id_gasto_cabecera' => $expenseId,
            'id_usuario' => $userId,
            'id_estatus_gasto' => $draftStatusId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function sendExpense(
        int $expenseId,
        int $userId,
        int $draftStatusId,
        int $sentStatusId
    ): bool {
        $statement = $this->db->prepare(
            'UPDATE gastos_cabecera
             SET id_estatus_gasto = :id_estatus_gasto,
                 fecha_envio_aprobacion = CURRENT_TIMESTAMP,
                 actualizado_en = CURRENT_TIMESTAMP,
                 actualizado_por = :actualizado_por
             WHERE id_gasto_cabecera = :id_gasto_cabecera
               AND id_usuario = :id_usuario
               AND id_estatus_gasto = :id_estatus_borrador
               AND eliminado_en IS NULL'
        );

        $statement->execute([
            'id_estatus_gasto' => $sentStatusId,
            'actualizado_por' => $userId,
            'id_gasto_cabecera' => $expenseId,
            'id_usuario' => $userId,
            'id_estatus_borrador' => $draftStatusId,
        ]);

        return $statement->rowCount() > 0;
    }

    /** @return list<array<string, mixed>> */
    public function listExpensesByUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT g.id_gasto_cabecera, g.fecha_gasto, g.creado_en,
                    a.nombre AS area_nombre,
                    cc.codigo AS centro_codigo,
                    cc.nombre AS centro_nombre,
                    eg.nombre AS estatus_nombre,
                    eg.clave AS estatus_clave
             FROM gastos_cabecera g
             INNER JOIN areas a ON a.id_area = g.id_area
             INNER JOIN centros_costos cc ON cc.id_centro_costo = g.id_centro_costo
             INNER JOIN estatus_gasto eg ON eg.id_estatus_gasto = g.id_estatus_gasto
             WHERE g.id_usuario = :id_usuario
               AND g.eliminado_en IS NULL
             ORDER BY g.creado_en DESC'
        );
        $statement->execute(['id_usuario' => $userId]);

        return $statement->fetchAll();
    }
}
