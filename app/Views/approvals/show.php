<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de gasto</title>
</head>
<body>
    <h1>Detalle de gasto</h1>

    <p><a href="/aprobaciones">Volver a la bandeja</a></p>

    <table border="0" cellpadding="6" cellspacing="0">
        <tr>
            <th align="left">Folio</th>
            <td><?= htmlspecialchars((string) $expense['folio'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Solicitante</th>
            <td><?= htmlspecialchars((string) $expense['solicitante_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
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
            <th align="left">Fecha gasto</th>
            <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Observaciones</th>
            <td>
                <?php if (!empty($expense['observaciones'])): ?>
                    <?= nl2br(htmlspecialchars((string) $expense['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th align="left">Estatus</th>
            <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Fecha creación</th>
            <td><?= htmlspecialchars((string) $expense['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th align="left">Fecha envío</th>
            <td>
                <?php if (!empty($expense['fecha_envio_aprobacion'])): ?>
                    <?= htmlspecialchars((string) $expense['fecha_envio_aprobacion'], ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
    </table>
</body>
</html>
