<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BudgetRepository;

class BudgetController extends Controller
{
    private BudgetRepository $budgetRepository;

    public function __construct()
    {
        $this->budgetRepository = new BudgetRepository();
    }

    public function index(): void
    {
        $this->render('budgets/index', [
            'budgets' => $this->budgetRepository->listBudgets(),
            'success' => $_SESSION['budget_success'] ?? null,
            'error' => $_SESSION['budget_error'] ?? null,
        ]);

        unset($_SESSION['budget_success'], $_SESSION['budget_error']);
    }

    public function create(): void
    {
        $this->render('budgets/create', [
            'areas' => $this->budgetRepository->getActiveAreas(),
            'errors' => $_SESSION['budget_form_errors'] ?? [],
            'old' => $_SESSION['budget_form_old'] ?? [],
        ]);

        unset($_SESSION['budget_form_errors'], $_SESSION['budget_form_old']);
    }

    public function store(): void
    {
        $areaId = (int) ($_POST['id_area'] ?? 0);
        $yearRaw = trim((string) ($_POST['anio'] ?? ''));
        $year = 0;
        $month = (int) ($_POST['mes'] ?? 0);
        $amountRaw = trim((string) ($_POST['monto'] ?? ''));

        $errors = [];

        if ($areaId <= 0) {
            $errors[] = 'El área es requerida.';
        } elseif (!$this->budgetRepository->activeAreaExists($areaId)) {
            $errors[] = 'El área seleccionada no es válida.';
        }

        if ($yearRaw === '' || !ctype_digit($yearRaw)) {
            $errors[] = 'El año es requerido y debe ser válido.';
        } else {
            $year = (int) $yearRaw;

            if ($year < 2000 || $year > 2100) {
                $errors[] = 'El año debe ser válido.';
            }
        }

        if ($month < 1 || $month > 12) {
            $errors[] = 'El mes debe estar entre 1 y 12.';
        }

        $amount = filter_var($amountRaw, FILTER_VALIDATE_FLOAT);

        if ($amount === false || $amount <= 0) {
            $errors[] = 'El monto debe ser mayor a cero.';
        }

        if ($errors === [] && $this->budgetRepository->existsForAreaPeriod($areaId, $year, $month)) {
            $errors[] = 'Ya existe un presupuesto para el área y periodo seleccionado.';
        }

        if ($errors !== []) {
            $_SESSION['budget_form_errors'] = $errors;
            $_SESSION['budget_form_old'] = [
                'id_area' => $areaId > 0 ? (string) $areaId : '',
                'anio' => $yearRaw,
                'mes' => $month > 0 ? (string) $month : '',
                'monto' => $amountRaw,
            ];
            $this->redirect('/presupuestos/crear');
        }

        $this->budgetRepository->createBudget(
            $areaId,
            $year,
            $month,
            (float) $amount,
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
        );

        $_SESSION['budget_success'] = 'Presupuesto creado correctamente.';
        $this->redirect('/presupuestos');
    }
}
