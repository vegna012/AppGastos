<?php $pageTitle = 'Detalle de gasto'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Detalle de gasto</h1>
    <div class="page-actions">
        <a href="/aprobaciones" class="btn btn-secondary btn-sm">Volver a la bandeja</a>
    </div>
</div>

<div class="table-responsive mb-4">
    <table class="table table-striped table-hover">
        <tbody>
            <tr>
                <th scope="row">Folio</th>
                <td><?= htmlspecialchars((string) $expense['folio'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Solicitante</th>
                <td><?= htmlspecialchars((string) $expense['solicitante_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
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
                <th scope="row">Fecha gasto</th>
                <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Observaciones</th>
                <td>
                    <?php if (!empty($expense['observaciones'])): ?>
                        <?= nl2br(htmlspecialchars((string) $expense['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Estatus</th>
                <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Fecha creación</th>
                <td><?= htmlspecialchars((string) $expense['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th scope="row">Fecha envío</th>
                <td>
                    <?php if (!empty($expense['fecha_envio_aprobacion'])): ?>
                        <?= htmlspecialchars((string) $expense['fecha_envio_aprobacion'], ENT_QUOTES, 'UTF-8') ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php if (($expense['estatus_clave'] ?? '') === 'ENVIADO'): ?>
    <form method="post" action="/aprobaciones/<?= (int) $expense['id_gasto_cabecera'] ?>/aprobar" class="mb-3">
        <button type="submit" class="btn btn-success">Aprobar</button>
    </form>

    <div class="card">
        <div class="card-body">
            <details <?= !empty($rejectErrors) ? 'open' : '' ?>>
                <summary class="mb-3">Rechazar</summary>
                <form method="post" action="/aprobaciones/<?= (int) $expense['id_gasto_cabecera'] ?>/rechazar" class="col-lg-8">
                    <div class="mb-3">
                        <label for="motivo_rechazo" class="form-label">Motivo del rechazo</label>
                        <textarea class="form-control" id="motivo_rechazo" name="motivo_rechazo" rows="4" required><?= htmlspecialchars((string) $rejectReason, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Confirmar rechazo</button>
                </form>
            </details>
        </div>
    </div>
<?php endif; ?>
