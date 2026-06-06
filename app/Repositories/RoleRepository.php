<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class RoleRepository extends Repository
{
    /** @return list<string> */
    public static function approvalRoleNames(): array
    {
        return ['ADMINISTRADOR', 'APROBADOR'];
    }

    public function getActiveRoleNameById(int $roleId): ?string
    {
        $statement = $this->db->prepare(
            'SELECT nombre FROM roles
             WHERE id_rol = :id_rol
               AND activo = 1
               AND eliminado_en IS NULL
             LIMIT 1'
        );
        $statement->execute(['id_rol' => $roleId]);
        $name = $statement->fetchColumn();

        return $name !== false ? (string) $name : null;
    }

    /** @param list<string> $allowedRoleNames */
    public function hasAnyRoleById(int $roleId, array $allowedRoleNames): bool
    {
        $roleName = $this->getActiveRoleNameById($roleId);

        if ($roleName === null) {
            return false;
        }

        $normalizedRole = mb_strtoupper($roleName);
        $normalizedAllowed = array_map(
            static fn (string $name): string => mb_strtoupper($name),
            $allowedRoleNames
        );

        return in_array($normalizedRole, $normalizedAllowed, true);
    }

    public function canAccessApprovals(int $roleId): bool
    {
        return $this->hasAnyRoleById($roleId, self::approvalRoleNames());
    }
}
