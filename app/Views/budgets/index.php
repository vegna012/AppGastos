<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuestos</title>
</head>
<body>
    <h1>Presupuestos</h1>

    <p>
        <a href="/dashboard">Dashboard</a> |
        <a href="/areas">Áreas</a> |
        <a href="/presupuestos/crear">Crear presupuesto</a>
    </p>

    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
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
</body>
</html>
