<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de aprobaciones</title>
</head>
<body>
    <h1>Bandeja de aprobaciones</h1>

    <p><a href="/dashboard">Dashboard</a></p>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
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
                            <a href="/aprobaciones/<?= (int) $expense['id_gasto_cabecera'] ?>">Ver detalle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
