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
            'error' => $_SESSION['approval_error'] ?? null,
        ]);

        unset($_SESSION['approval_error']);
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
}
