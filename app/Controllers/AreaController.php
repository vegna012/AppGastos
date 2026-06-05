<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\AreaRepository;

class AreaController extends Controller
{
    private AreaRepository $areaRepository;

    public function __construct()
    {
        $this->areaRepository = new AreaRepository();
    }

    public function index(): void
    {
        $this->render('areas/index', [
            'areas' => $this->areaRepository->listAreas(),
            'success' => $_SESSION['area_success'] ?? null,
            'error' => $_SESSION['area_error'] ?? null,
        ]);

        unset($_SESSION['area_success'], $_SESSION['area_error']);
    }

    public function create(): void
    {
        $this->render('areas/create', [
            'users' => $this->areaRepository->getActiveUsers(),
            'errors' => $_SESSION['area_form_errors'] ?? [],
            'old' => $_SESSION['area_form_old'] ?? [],
        ]);

        unset($_SESSION['area_form_errors'], $_SESSION['area_form_old']);
    }

    public function store(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $jefeAreaId = (int) ($_POST['id_jefe_area'] ?? 0);
        $jefeAreaId = $jefeAreaId > 0 ? $jefeAreaId : null;

        $errors = [];

        if ($nombre === '') {
            $errors[] = 'El nombre es requerido.';
        } elseif ($this->areaRepository->nameExists($nombre)) {
            $errors[] = 'El nombre ya está registrado.';
        }

        if ($jefeAreaId !== null && !$this->areaRepository->activeUserExists($jefeAreaId)) {
            $errors[] = 'El jefe de área seleccionado no es válido.';
        }

        if ($errors !== []) {
            $_SESSION['area_form_errors'] = $errors;
            $_SESSION['area_form_old'] = [
                'nombre' => $nombre,
                'id_jefe_area' => $jefeAreaId !== null ? (string) $jefeAreaId : '',
            ];
            $this->redirect('/areas/crear');
        }

        $this->areaRepository->createArea(
            $nombre,
            $jefeAreaId,
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
        );

        $_SESSION['area_success'] = 'Área creada correctamente.';
        $this->redirect('/areas');
    }

    public function toggleStatus(): void
    {
        $areaId = (int) (RouteContext::param('id') ?? 0);

        if ($areaId <= 0 || !$this->areaRepository->toggleStatus($areaId)) {
            $_SESSION['area_error'] = 'No se pudo cambiar el estado del área.';
        } else {
            $_SESSION['area_success'] = 'Estado del área actualizado.';
        }

        $this->redirect('/areas');
    }
}
