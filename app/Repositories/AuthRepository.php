<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

class AuthRepository extends Repository
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id_usuario, nombre, correo, password_hash, id_rol, activo
             FROM usuarios
             WHERE correo = :correo
               AND eliminado_en IS NULL
             LIMIT 1'
        );

        $statement->execute(['correo' => $email]);
        $user = $statement->fetch();

        return $user !== false ? $user : null;
    }
}
