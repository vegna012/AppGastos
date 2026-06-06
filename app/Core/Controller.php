<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\RoleRepository;

class Controller
{
    protected function render(string $view, array $data = [], array $layoutOptions = []): void
    {
        if (!array_key_exists('canAccessApprovals', $data) && isset($_SESSION['user_id'])) {
            $roleId = (int) ($_SESSION['role_id'] ?? 0);
            $data['canAccessApprovals'] = $roleId > 0
                && (new RoleRepository())->canAccessApprovals($roleId);
        }

        View::render($view, $data, $layoutOptions);
    }

    protected function redirect(string $url, int $statusCode = 302): never
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}
