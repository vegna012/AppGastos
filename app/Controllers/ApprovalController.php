<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\ApprovalRepository;

class ApprovalController extends Controller
{
    private ApprovalRepository $approvalRepository;

    public function __construct()
    {
        $this->approvalRepository = new ApprovalRepository();
    }

    public function index(): void
    {
        $this->render('approvals/index', [
            'expenses' => $this->approvalRepository->listSentExpenses(),
            'success' => $_SESSION['approval_success'] ?? null,
            'error' => $_SESSION['approval_error'] ?? null,
        ]);

        unset($_SESSION['approval_success'], $_SESSION['approval_error']);
    }

    public function show(): void
    {
        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['approval_error'] = 'Gasto no encontrado.';
            $this->redirect('/aprobaciones');
        }

        $expense = $this->approvalRepository->getSentExpenseById($expenseId);

        if ($expense === null) {
            $_SESSION['approval_error'] = 'Gasto no encontrado o no está pendiente de aprobación.';
            $this->redirect('/aprobaciones');
        }

        $this->render('approvals/show', [
            'expense' => $expense,
        ]);
    }

    public function approve(): void
    {
        $approverId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

        if ($approverId <= 0) {
            $this->redirect('/login');
        }

        $expenseId = (int) (RouteContext::param('id') ?? 0);

        if ($expenseId <= 0) {
            $_SESSION['approval_error'] = 'Gasto no encontrado.';
            $this->redirect('/aprobaciones');
        }

        if (!$this->approvalRepository->validateSent($expenseId)) {
            $_SESSION['approval_error'] = 'El gasto no está pendiente de aprobación.';
            $this->redirect('/aprobaciones');
        }

        $sentStatusId = $this->approvalRepository->getStatusIdByKey('ENVIADO');
        $approvedStatusId = $this->approvalRepository->getStatusIdByKey('APROBADO');

        if ($sentStatusId === null || $approvedStatusId === null) {
            $_SESSION['approval_error'] = 'No se pudo determinar el estatus del gasto.';
            $this->redirect('/aprobaciones');
        }

        $approved = $this->approvalRepository->approveExpense(
            $expenseId,
            $approverId,
            $sentStatusId,
            $approvedStatusId
        );

        if (!$approved) {
            $_SESSION['approval_error'] = 'No se pudo aprobar el gasto. Verifique que siga en estado Enviado.';
            $this->redirect('/aprobaciones');
        }

        $_SESSION['approval_success'] = 'Gasto aprobado correctamente.';
        $this->redirect('/aprobaciones');
    }
}
