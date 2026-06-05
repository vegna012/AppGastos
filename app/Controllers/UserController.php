<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function index(): void
    {
        $this->render('users/index', [
            'users' => $this->userRepository->listUsers(),
            'success' => $_SESSION['user_success'] ?? null,
            'error' => $_SESSION['user_error'] ?? null,
        ]);

        unset($_SESSION['user_success'], $_SESSION['user_error']);
    }

    public function create(): void
    {
        $this->render('users/create', [
            'roles' => $this->userRepository->getRoles(),
            'areas' => $this->userRepository->getAreas(),
            'errors' => $_SESSION['user_form_errors'] ?? [],
            'old' => $_SESSION['user_form_old'] ?? [],
        ]);

        unset($_SESSION['user_form_errors'], $_SESSION['user_form_old']);
    }

    public function store(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $correo = trim((string) ($_POST['correo'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $roleId = (int) ($_POST['id_rol'] ?? 0);
        $areaId = (int) ($_POST['id_area'] ?? 0);

        $errors = [];

        if ($nombre === '') {
            $errors[] = 'El nombre es requerido.';
        }

        if ($correo === '') {
            $errors[] = 'El correo es requerido.';
        } elseif ($this->userRepository->emailExists($correo)) {
            $errors[] = 'El correo ya está registrado.';
        }

        if ($password === '') {
            $errors[] = 'La contraseña es requerida.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if ($roleId <= 0 || !$this->userRepository->roleExists($roleId)) {
            $errors[] = 'El rol seleccionado no es válido.';
        }

        if ($areaId <= 0 || !$this->userRepository->areaExists($areaId)) {
            $errors[] = 'El área seleccionada no es válida.';
        }

        if ($errors !== []) {
            $_SESSION['user_form_errors'] = $errors;
            $_SESSION['user_form_old'] = [
                'nombre' => $nombre,
                'correo' => $correo,
                'id_rol' => $roleId > 0 ? (string) $roleId : '',
                'id_area' => $areaId > 0 ? (string) $areaId : '',
            ];
            $this->redirect('/usuarios/crear');
        }

        $this->userRepository->createUser(
            $nombre,
            $correo,
            password_hash($password, PASSWORD_DEFAULT),
            $roleId,
            $areaId,
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
        );

        $_SESSION['user_success'] = 'Usuario creado correctamente.';
        $this->redirect('/usuarios');
    }

    public function toggleStatus(): void
    {
        $userId = (int) (RouteContext::param('id') ?? 0);

        if ($userId <= 0 || !$this->userRepository->toggleStatus($userId)) {
            $_SESSION['user_error'] = 'No se pudo cambiar el estado del usuario.';
        } else {
            $_SESSION['user_success'] = 'Estado del usuario actualizado.';
        }

        $this->redirect('/usuarios');
    }
}
