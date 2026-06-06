<?php $pageTitle = 'Detalle de mi gasto'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Detalle de mi gasto</h1>
    <div class="page-actions">
        <a href="/mis-gastos" class="btn btn-secondary btn-sm">Volver a mis gastos</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <tbody>
            <tr>
                <th scope="row">Folio</th>
                <td><?= htmlspecialchars((string) $expense['folio'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Fecha gasto</th>
                <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Área</th>
                <td><?= htmlspecialchars((string) $expense['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Centro de costo</th>
                <td>
                    <?= htmlspecialchars(
                        (string) $expense['centro_codigo'] . ' — ' . $expense['centro_nombre'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Observaciones del solicitante</th>
                <td>
                    <?php if (!empty($expense['observaciones'])): ?>
                        <?= nl2br(htmlspecialchars((string) $expense['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Estatus actual</th>
                <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php if (!empty($expense['observaciones_aprobacion'])): ?>
                <tr>
                    <th scope="row">Observaciones de aprobación</th>
                    <td><?= nl2br(htmlspecialchars((string) $expense['observaciones_aprobacion'], ENT_QUOTES, 'UTF-8')) ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($expense['fecha_aprobacion'])): ?>
                <tr>
                    <th scope="row">Fecha de aprobación/rechazo</th>
                    <td><?= htmlspecialchars((string) $expense['fecha_aprobacion'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($expense['aprobador_nombre'])): ?>
                <tr>
                    <th scope="row">Aprobador</th>
                    <td><?= htmlspecialchars((string) $expense['aprobador_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
