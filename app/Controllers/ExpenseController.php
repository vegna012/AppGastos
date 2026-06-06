<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\BudgetAvailabilityRepository;
use App\Repositories\ExpenseRepository;

class ExpenseController extends Controller
{
    private ExpenseRepository $expenseRepository;
    private BudgetAvailabilityRepository $budgetAvailabilityRepository;

    public function __construct()
    {
        $this->expenseRepository = new ExpenseRepository();
        $this->budgetAvailabilityRepository = new BudgetAvailabilityRepository();
    }

    public function create(): void
    {
        $old = $_SESSION['expense_form_old'] ?? [];

        if (isset($_GET['id_area'])) {
            $old['id_area'] = (string) $_GET['id_area'];
        }

        if (isset($_GET['fecha_gasto'])) {
            $old['fecha_gasto'] = (string) $_GET['fecha_gasto'];
        }

        $this->render('expenses/create', [
            'areas' => $this->expenseRepository->getActiveAreas(),
            'costCenters' => $this->expenseRepository->getActiveCostCenters(),
            'errors' => $_SESSION['expense_form_errors'] ?? [],
            'old' => $old,
            'budgetAvailability' => $this->resolveBudgetAvailability($old),
        ]);

        unset($_SESSION['expense_form_errors'], $_SESSION['expense_form_old']);
    }

    public function store(): void
    {
        $userId = $this->requireAuthenticatedUserId();

        $expenseDateRaw = trim((string) ($_POST['fecha_gasto'] ?? ''));

        [$errors, $areaId, $costCenterId, $expenseDate, $observations] = $this->validateExpenseInput(
            (int) ($_POST['id_area'] ?? 0),
            (int) ($_POST['id_centro_costo'] ?? 0),
            $expenseDateRaw,
            trim((string) ($_POST['observaciones'] ?? ''))
        );

        $draftStatusId = $this->expenseRepository->getDraftStatusId();

        if ($draftStatusId === null) {
            $errors[] = 'No se encontró el estatus BORRADOR en el catálogo.';
        }

        if ($errors !== []) {
            $_SESSION['expense_form_errors'] = $errors;
            $_SESSION['expense_form_old'] = [
                'id_area' => $areaId > 0 ? (string) $areaId : '',
                'id_centro_costo' => $costCenterId > 0 ? (string) $costCenterId : '',
                'fecha_gasto' => $expenseDateRaw,
                'observaciones' => $observations,
            ];
            $this->redirect('/gastos/crear');
        }

        $folio = $this->expenseRepository->generateFolio($userId);

        $this->expenseRepository->createExpense(
            $folio,
            $userId,
            $areaId,
            $costCenterId,
            $draftStatusId,
            $expenseDate,
            $observations
        );

        $_SESSION['expense_success'] = 'Gasto creado correctamente en estado Borrador.';
        $this->redirect('/mis-gastos');
    }

    public function myExpenses(): void
    {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

        if ($userId <= 0) {
            $this->redirect('/login');
        }

        $this->render('expenses/my_expenses', [
            'expenses' => $this->expenseRepository->listExpensesByUser($userId),
            'success' => $_SESSION['expense_success'] ?? null,
            'error' => $_SESSION['expense_error'] ?? null,
        ]);

        unset($_SESSION['expense_success'], $_SESSION['expense_error']);
    }

    public function show(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        $expense = $this->expenseRepository->getExpenseDetailByUser($expenseId, $userId);

        if ($expense === null) {
            $_SESSION['expense_error'] = 'Gasto no encontrado o no tiene permiso para consultarlo.';
            $this->redirect('/mis-gastos');
        }

        $this->render('expenses/show', [
            'expense' => $expense,
        ]);
    }

    public function edit(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        $expense = $this->expenseRepository->getExpenseById($expenseId);

        if ($expense === null) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        if ((int) $expense['id_usuario'] !== $userId) {
            $_SESSION['expense_error'] = 'No tiene permiso para editar este gasto.';
            $this->redirect('/mis-gastos');
        }

        if ($expense['estatus_clave'] !== 'BORRADOR') {
            $_SESSION['expense_error'] = 'El gasto ya no puede ser modificado.';
            $this->redirect('/mis-gastos');
        }

        $old = $_SESSION['expense_form_old'] ?? [
            'id_area' => (string) $expense['id_area'],
            'id_centro_costo' => (string) $expense['id_centro_costo'],
            'fecha_gasto' => (string) $expense['fecha_gasto'],
            'observaciones' => (string) ($expense['observaciones'] ?? ''),
        ];

        $this->render('expenses/edit', [
            'expenseId' => $expenseId,
            'areas' => $this->expenseRepository->getActiveAreas(),
            'costCenters' => $this->expenseRepository->getActiveCostCenters(),
            'errors' => $_SESSION['expense_form_errors'] ?? [],
            'old' => $old,
        ]);

        unset($_SESSION['expense_form_errors'], $_SESSION['expense_form_old']);
    }

    public function update(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        $expense = $this->expenseRepository->getExpenseById($expenseId);

        if ($expense === null) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        if ((int) $expense['id_usuario'] !== $userId) {
            $_SESSION['expense_error'] = 'No tiene permiso para editar este gasto.';
            $this->redirect('/mis-gastos');
        }

        if ($expense['estatus_clave'] !== 'BORRADOR') {
            $_SESSION['expense_error'] = 'El gasto ya no puede ser modificado.';
            $this->redirect('/mis-gastos');
        }

        [$errors, $areaId, $costCenterId, $expenseDate, $observations] = $this->validateExpenseInput(
            (int) ($_POST['id_area'] ?? 0),
            (int) ($_POST['id_centro_costo'] ?? 0),
            trim((string) ($_POST['fecha_gasto'] ?? '')),
            trim((string) ($_POST['observaciones'] ?? ''))
        );

        $draftStatusId = $this->expenseRepository->getDraftStatusId();

        if ($draftStatusId === null) {
            $errors[] = 'No se encontró el estatus BORRADOR en el catálogo.';
        }

        if ($errors !== []) {
            $_SESSION['expense_form_errors'] = $errors;
            $_SESSION['expense_form_old'] = [
                'id_area' => $areaId > 0 ? (string) $areaId : '',
                'id_centro_costo' => $costCenterId > 0 ? (string) $costCenterId : '',
                'fecha_gasto' => trim((string) ($_POST['fecha_gasto'] ?? '')),
                'observaciones' => $observations,
            ];
            $this->redirect('/gastos/' . $expenseId . '/editar');
        }

        $updated = $this->expenseRepository->updateExpense(
            $expenseId,
            $userId,
            $draftStatusId,
            $areaId,
            $costCenterId,
            $expenseDate,
            $observations
        );

        if (!$updated) {
            $_SESSION['expense_error'] = 'El gasto ya no puede ser modificado.';
            $this->redirect('/mis-gastos');
        }

        $_SESSION['expense_success'] = 'Gasto actualizado correctamente.';
        $this->redirect('/mis-gastos');
    }

    public function send(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        $expense = $this->expenseRepository->getExpenseById($expenseId);

        if ($expense === null) {
            $_SESSION['expense_error'] = 'Gasto no encontrado.';
            $this->redirect('/mis-gastos');
        }

        if ((int) $expense['id_usuario'] !== $userId) {
            $_SESSION['expense_error'] = 'No tiene permiso para enviar este gasto.';
            $this->redirect('/mis-gastos');
        }

        if (!$this->expenseRepository->validateDraft($expenseId)) {
            $_SESSION['expense_error'] = 'Solo se pueden enviar gastos en estado Borrador.';
            $this->redirect('/mis-gastos');
        }

        $draftStatusId = $this->expenseRepository->getDraftStatusId();
        $sentStatusId = $this->expenseRepository->getStatusIdByKey('ENVIADO');

        if ($draftStatusId === null || $sentStatusId === null) {
            $_SESSION['expense_error'] = 'No se pudo determinar el estatus del gasto.';
            $this->redirect('/mis-gastos');
        }

        $sent = $this->expenseRepository->sendExpense(
            $expenseId,
            $userId,
            $draftStatusId,
            $sentStatusId
        );

        if (!$sent) {
            $_SESSION['expense_error'] = 'No se pudo enviar el gasto. Verifique que siga en estado Borrador.';
            $this->redirect('/mis-gastos');
        }

        $_SESSION['expense_success'] = 'Gasto enviado a aprobación correctamente.';
        $this->redirect('/mis-gastos');
    }

    private function requireAuthenticatedUserId(): int
    {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

        if ($userId <= 0) {
            $this->redirect('/login');
        }

        return $userId;
    }

    /**
     * @return array{0: list<string>, 1: int, 2: int, 3: string|null, 4: string}
     */
    private function validateExpenseInput(
        int $areaId,
        int $costCenterId,
        string $expenseDateRaw,
        string $observations
    ): array {
        $errors = [];
        $expenseDate = null;

        if ($areaId <= 0) {
            $errors[] = 'El área es requerida.';
        } elseif (!$this->expenseRepository->activeAreaExists($areaId)) {
            $errors[] = 'El área seleccionada no es válida.';
        }

        if ($costCenterId <= 0) {
            $errors[] = 'El centro de costo es requerido.';
        } elseif (
            $areaId > 0
            && !$this->expenseRepository->activeCostCenterBelongsToArea($costCenterId, $areaId)
        ) {
            $errors[] = 'El centro de costo seleccionado no es válido.';
        }

        if ($expenseDateRaw === '') {
            $errors[] = 'La fecha del gasto es requerida.';
        } else {
            $parsedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $expenseDateRaw);
            $dateErrors = \DateTimeImmutable::getLastErrors();

            if (
                $parsedDate === false
                || ($dateErrors['warning_count'] ?? 0) > 0
                || ($dateErrors['error_count'] ?? 0) > 0
                || $parsedDate->format('Y-m-d') !== $expenseDateRaw
            ) {
                $errors[] = 'La fecha del gasto no es válida.';
            } else {
                $expenseDate = $parsedDate->format('Y-m-d');
            }
        }

        return [$errors, $areaId, $costCenterId, $expenseDate, $observations];
    }

    /** @param array<string, string> $old */
    private function resolveBudgetAvailability(array $old): ?array
    {
        $areaId = (int) ($old['id_area'] ?? 0);
        $expenseDateRaw = trim((string) ($old['fecha_gasto'] ?? ''));

        if ($areaId <= 0 || $expenseDateRaw === '') {
            return null;
        }

        $parsedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $expenseDateRaw);
        $dateErrors = \DateTimeImmutable::getLastErrors();

        if (
            $parsedDate === false
            || ($dateErrors['warning_count'] ?? 0) > 0
            || ($dateErrors['error_count'] ?? 0) > 0
            || $parsedDate->format('Y-m-d') !== $expenseDateRaw
        ) {
            return null;
        }

        return $this->budgetAvailabilityRepository->getAvailability(
            $areaId,
            (int) $parsedDate->format('Y'),
            (int) $parsedDate->format('n')
        );
    }
}
