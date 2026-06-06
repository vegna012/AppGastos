<?php
$pageTitle = 'Bandeja de aprobaciones';
$containerClass = 'container-fluid';
?>

<div class="page-header">
    <h1 class="h3 mb-0">Bandeja de aprobaciones</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha gasto</th>
                <th>Solicitante</th>
                <th>Área</th>
                <th>Centro de costo</th>
                <th>Estatus</th>
                <th>Fecha envío</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
                <tr>
                    <td colspan="8">No hay gastos pendientes de aprobación.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $expense['folio'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $expense['solicitante_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $expense['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars(
                                (string) $expense['centro_codigo'] . ' — ' . $expense['centro_nombre'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>
                        <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($expense['fecha_envio_aprobacion'])): ?>
                                <?= htmlspecialchars((string) $expense['fecha_envio_aprobacion'], ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/aprobaciones/<?= (int) $expense['id_gasto_cabecera'] ?>" class="btn btn-sm btn-primary">Ver detalle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
