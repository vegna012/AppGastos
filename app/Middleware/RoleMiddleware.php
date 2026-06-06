<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repositories\RoleRepository;

class RoleMiddleware
{
    /** @var list<string> */
    private array $allowedRoles;

    /** @param list<string> $allowedRoles */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $roleId = (int) ($_SESSION['role_id'] ?? 0);

        if ($roleId <= 0) {
            $this->denyAccess();
        }

        $roleRepository = new RoleRepository();

        if (!$roleRepository->hasAnyRoleById($roleId, $this->allowedRoles)) {
            $this->denyAccess();
        }
    }

    private function denyAccess(): never
    {
        $_SESSION['access_error'] = 'No tiene permisos para acceder a esta sección.';
        header('Location: /dashboard');
        exit;
    }
}
