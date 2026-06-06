<?php $pageTitle = 'Presupuestos'; ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h1 class="h3 mb-0">Presupuestos</h1>
    <a href="/presupuestos/crear" class="btn btn-primary">Crear presupuesto</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Área</th>
                <th>Año</th>
                <th>Mes</th>
                <th>Monto</th>
                <th>Fecha creación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($budgets)): ?>
                <tr>
                    <td colspan="5">No hay presupuestos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($budgets as $budget): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $budget['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $budget['anio'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $budget['mes'] ?></td>
                        <td><?= htmlspecialchars(number_format((float) $budget['monto_presupuestado'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $budget['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
