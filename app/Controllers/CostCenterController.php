<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\CostCenterRepository;

class CostCenterController extends Controller
{
    private CostCenterRepository $costCenterRepository;

    public function __construct()
    {
        $this->costCenterRepository = new CostCenterRepository();
    }

    public function index(): void
    {
        $this->render('cost_centers/index', [
            'costCenters' => $this->costCenterRepository->listCostCenters(),
            'success' => $_SESSION['cost_center_success'] ?? null,
            'error' => $_SESSION['cost_center_error'] ?? null,
        ]);

        unset($_SESSION['cost_center_success'], $_SESSION['cost_center_error']);
    }

    public function create(): void
    {
        $this->render('cost_centers/create', [
            'areas' => $this->costCenterRepository->getActiveAreas(),
            'errors' => $_SESSION['cost_center_form_errors'] ?? [],
            'old' => $_SESSION['cost_center_form_old'] ?? [],
        ]);

        unset($_SESSION['cost_center_form_errors'], $_SESSION['cost_center_form_old']);
    }

    public function store(): void
    {
        $areaId = (int) ($_POST['id_area'] ?? 0);
        $codigo = trim((string) ($_POST['codigo'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $descripcion = $descripcion !== '' ? $descripcion : null;

        $errors = [];

        if ($areaId <= 0) {
            $errors[] = 'El área es requerida.';
        } elseif (!$this->costCenterRepository->activeAreaExists($areaId)) {
            $errors[] = 'El área seleccionada no es válida.';
        }

        if ($codigo === '') {
            $errors[] = 'El código es requerido.';
        } elseif ($areaId > 0 && $this->costCenterRepository->codeExistsInArea($areaId, $codigo)) {
            $errors[] = 'El código ya está registrado para el área seleccionada.';
        }

        if ($nombre === '') {
            $errors[] = 'El nombre es requerido.';
        }

        if ($errors !== []) {
            $_SESSION['cost_center_form_errors'] = $errors;
            $_SESSION['cost_center_form_old'] = [
                'id_area' => $areaId > 0 ? (string) $areaId : '',
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $descripcion ?? '',
            ];
            $this->redirect('/centros-costos/crear');
        }

        $this->costCenterRepository->createCostCenter(
            $areaId,
            $codigo,
            $nombre,
            $descripcion,
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
        );

        $_SESSION['cost_center_success'] = 'Centro de costo creado correctamente.';
        $this->redirect('/centros-costos');
    }

    public function toggleStatus(): void
    {
        $costCenterId = (int) (RouteContext::param('id') ?? 0);

        if ($costCenterId <= 0 || !$this->costCenterRepository->toggleStatus($costCenterId)) {
            $_SESSION['cost_center_error'] = 'No se pudo cambiar el estado del centro de costo.';
        } else {
            $_SESSION['cost_center_success'] = 'Estado del centro de costo actualizado.';
        }

        $this->redirect('/centros-costos');
    }
}
