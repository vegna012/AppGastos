<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear presupuesto</title>
</head>
<body>
    <h1>Crear presupuesto</h1>

    <p><a href="/presupuestos">Volver al listado</a></p>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="/presupuestos">
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
            <label for="anio">Año</label><br>
            <input type="number" id="anio" name="anio" min="2000" max="2100" required
                   value="<?= htmlspecialchars((string) ($old['anio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <div>
            <label for="mes">Mes</label><br>
            <select id="mes" name="mes" required>
                <option value="">Seleccione un mes</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>"
                        <?= ((string) ($old['mes'] ?? '') === (string) $m) ? 'selected' : '' ?>>
                        <?= $m ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <br>
        <div>
            <label for="monto">Monto</label><br>
            <input type="number" id="monto" name="monto" min="0.01" step="0.01" required
                   value="<?= htmlspecialchars((string) ($old['monto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
