<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\RoleRepository;

class DashboardController extends Controller
{
    public function index(): void
    {
        $roleId = (int) ($_SESSION['role_id'] ?? 0);
        $roleRepository = new RoleRepository();

        $this->render('dashboard/index', [
            'userName' => $_SESSION['user_name'] ?? '',
            'canAccessApprovals' => $roleId > 0 && $roleRepository->canAccessApprovals($roleId),
            'accessError' => $_SESSION['access_error'] ?? null,
        ]);

        unset($_SESSION['access_error']);
    }
}
