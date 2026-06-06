<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de mi gasto</title>
</head>
<body>
    <h1>Detalle de mi gasto</h1>

    <p><a href="/mis-gastos">Volver a mis gastos</a></p>

    <table border="0" cellpadding="6" cellspacing="0">
        <tr>
            <th align="left">Folio</th>
            <td><?= htmlspecialchars((string) $expense['folio'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Fecha gasto</th>
            <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Área</th>
            <td><?= htmlspecialchars((string) $expense['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Centro de costo</th>
            <td>
                <?= htmlspecialchars(
                    (string) $expense['centro_codigo'] . ' — ' . $expense['centro_nombre'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </td>
        </tr>
        <tr>
            <th align="left">Observaciones del solicitante</th>
            <td>
                <?php if (!empty($expense['observaciones'])): ?>
                    <?= nl2br(htmlspecialchars((string) $expense['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th align="left">Estatus actual</th>
            <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php if (!empty($expense['observaciones_aprobacion'])): ?>
            <tr>
                <th align="left">Observaciones de aprobación</th>
                <td><?= nl2br(htmlspecialchars((string) $expense['observaciones_aprobacion'], ENT_QUOTES, 'UTF-8')) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($expense['fecha_aprobacion'])): ?>
            <tr>
                <th align="left">Fecha de aprobación/rechazo</th>
                <td><?= htmlspecialchars((string) $expense['fecha_aprobacion'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($expense['aprobador_nombre'])): ?>
            <tr>
                <th align="left">Aprobador</th>
                <td><?= htmlspecialchars((string) $expense['aprobador_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>
