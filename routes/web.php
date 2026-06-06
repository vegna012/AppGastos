<?php

declare(strict_types=1);

use App\Controllers\ApprovalController;
use App\Controllers\AreaController;
use App\Controllers\AuthController;
use App\Controllers\BudgetController;
use App\Controllers\DashboardController;
use App\Controllers\ExpenseController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

/** @var Router $router */
$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/usuarios', [UserController::class, 'index'], [AuthMiddleware::class]);
$router->get('/usuarios/crear', [UserController::class, 'create'], [AuthMiddleware::class]);
$router->post('/usuarios', [UserController::class, 'store'], [AuthMiddleware::class]);
$router->post('/usuarios/{id}/estado', [UserController::class, 'toggleStatus'], [AuthMiddleware::class]);

$router->get('/areas', [AreaController::class, 'index'], [AuthMiddleware::class]);
$router->get('/areas/crear', [AreaController::class, 'create'], [AuthMiddleware::class]);
$router->post('/areas', [AreaController::class, 'store'], [AuthMiddleware::class]);
$router->post('/areas/{id}/estado', [AreaController::class, 'toggleStatus'], [AuthMiddleware::class]);

$router->get('/presupuestos', [BudgetController::class, 'index'], [AuthMiddleware::class]);
$router->get('/presupuestos/crear', [BudgetController::class, 'create'], [AuthMiddleware::class]);
$router->post('/presupuestos', [BudgetController::class, 'store'], [AuthMiddleware::class]);

$router->get('/gastos/crear', [ExpenseController::class, 'create'], [AuthMiddleware::class]);
$router->post('/gastos', [ExpenseController::class, 'store'], [AuthMiddleware::class]);
$router->get('/gastos/{id}/editar', [ExpenseController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/gastos/{id}/actualizar', [ExpenseController::class, 'update'], [AuthMiddleware::class]);
$router->post('/gastos/{id}/enviar', [ExpenseController::class, 'send'], [AuthMiddleware::class]);
$router->get('/mis-gastos', [ExpenseController::class, 'myExpenses'], [AuthMiddleware::class]);

$router->get('/aprobaciones', [ApprovalController::class, 'index'], [AuthMiddleware::class]);
$router->get('/aprobaciones/{id}', [ApprovalController::class, 'show'], [AuthMiddleware::class]);
$router->post('/aprobaciones/{id}/aprobar', [ApprovalController::class, 'approve'], [AuthMiddleware::class]);
