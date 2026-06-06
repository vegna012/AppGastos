<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RouteContext;
use App\Repositories\ApprovalRepository;

class ApprovalController extends Controller
{
    private const REJECTION_REASON_MAX_LENGTH = 1000;

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
            'rejectErrors' => $_SESSION['approval_reject_errors'] ?? [],
            'rejectReason' => $_SESSION['approval_reject_reason'] ?? '',
        ]);

        unset($_SESSION['approval_reject_errors'], $_SESSION['approval_reject_reason']);
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

    public function reject(): void
    {
        $rejectorId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

        if ($rejectorId <= 0) {
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

        $rejectionReason = trim((string) ($_POST['motivo_rechazo'] ?? ''));
        $errors = [];

        if ($rejectionReason === '') {
            $errors[] = 'El motivo del rechazo es obligatorio.';
        } elseif (mb_strlen($rejectionReason) > self::REJECTION_REASON_MAX_LENGTH) {
            $errors[] = 'El motivo del rechazo no puede exceder '
                . self::REJECTION_REASON_MAX_LENGTH . ' caracteres.';
        }

        if ($errors !== []) {
            $_SESSION['approval_reject_errors'] = $errors;
            $_SESSION['approval_reject_reason'] = $rejectionReason;
            $this->redirect('/aprobaciones/' . $expenseId);
        }

        $sentStatusId = $this->approvalRepository->getStatusIdByKey('ENVIADO');
        $rejectedStatusId = $this->approvalRepository->getStatusIdByKey('RECHAZADO');

        if ($sentStatusId === null || $rejectedStatusId === null) {
            $_SESSION['approval_error'] = 'No se pudo determinar el estatus del gasto.';
            $this->redirect('/aprobaciones');
        }

        $rejected = $this->approvalRepository->rejectExpense(
            $expenseId,
            $rejectorId,
            $sentStatusId,
            $rejectedStatusId,
            $rejectionReason
        );

        if (!$rejected) {
            $_SESSION['approval_error'] = 'No se pudo rechazar el gasto. Verifique que siga en estado Enviado.';
            $this->redirect('/aprobaciones');
        }

        $_SESSION['approval_success'] = 'Gasto rechazado correctamente.';
        $this->redirect('/aprobaciones');
    }
}
