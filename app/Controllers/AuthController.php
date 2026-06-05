<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuthRepository;

class AuthController extends Controller
{
    private AuthRepository $authRepository;

    public function __construct()
    {
        $this->authRepository = new AuthRepository();
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [
            'error' => $_SESSION['login_error'] ?? null,
        ]);

        unset($_SESSION['login_error']);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['correo'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->failLogin();
        }

        $user = $this->authRepository->findByEmail($email);

        if ($user === null) {
            $this->failLogin();
        }

        if (!(bool) $user['activo']) {
            $this->failLogin();
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->failLogin();
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['role_id'] = (int) $user['id_rol'];

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();

        $this->redirect('/login');
    }

    private function failLogin(): never
    {
        $_SESSION['login_error'] = 'Credenciales inválidas';
        $this->redirect('/login');
    }
}
