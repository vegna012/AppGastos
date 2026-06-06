<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear gasto</title>
</head>
<body>
    <h1>Crear gasto</h1>

    <p><a href="/dashboard">Dashboard</a> | <a href="/mis-gastos">Mis gastos</a></p>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="/gastos">
        <div>
            <label for="id_area">Área</label><br>
            <select id="id_area" name="id_area" required>
                <option value="">Seleccione un área</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= (int) $area['id_area'] ?>"
                        <?= ((string) ($old['id_area'] ?? '') === (string) $area['id_area']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <div>
            <label for="id_centro_costo">Centro de costo</label><br>
            <select id="id_centro_costo" name="id_centro_costo" required>
                <option value="">Seleccione un centro de costo</option>
                <?php foreach ($costCenters as $center): ?>
                    <option value="<?= (int) $center['id_centro_costo'] ?>"
                        <?= ((string) ($old['id_centro_costo'] ?? '') === (string) $center['id_centro_costo']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(
                            (string) $center['area_nombre'] . ' — ' . $center['codigo'] . ' — ' . $center['nombre'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <div>
            <label for="fecha_gasto">Fecha gasto</label><br>
            <input type="date" id="fecha_gasto" name="fecha_gasto" required
                   value="<?= htmlspecialchars((string) ($old['fecha_gasto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <?php if ($budgetAvailability !== null): ?>
            <div>
                <strong>Disponibilidad presupuestal</strong><br>
                <?php if (!$budgetAvailability['configured']): ?>
                    <p>Sin presupuesto configurado para el periodo.</p>
                <?php else: ?>
                    <p>
                        Presupuesto: $<?= htmlspecialchars(number_format($budgetAvailability['presupuesto'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?><br>
                        Consumo: $<?= htmlspecialchars(number_format($budgetAvailability['consumo'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?><br>
                        Disponible: $<?= htmlspecialchars(number_format($budgetAvailability['disponible'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
            </div>
            <br>
        <?php endif; ?>
        <div>
            <label for="observaciones">Observaciones (opcional)</label><br>
            <textarea id="observaciones" name="observaciones" rows="4" cols="50"><?= htmlspecialchars((string) ($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <br>
        <button type="submit" formaction="/gastos/crear" formmethod="get" formnovalidate>
            Consultar presupuesto
        </button>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
