<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Sistema de Gestión de Gastos Empresariales';
    }
}
