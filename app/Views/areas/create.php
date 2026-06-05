<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear área</title>
</head>
<body>
    <h1>Crear área</h1>

    <p><a href="/areas">Volver al listado</a></p>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="/areas">
        <div>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" required
                   value="<?= htmlspecialchars((string) ($old['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <div>
            <label for="id_jefe_area">Jefe de área (opcional)</label><br>
            <select id="id_jefe_area" name="id_jefe_area">
                <option value="">Sin jefe asignado</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= (int) $user['id_usuario'] ?>"
                        <?= ((string) ($old['id_jefe_area'] ?? '') === (string) $user['id_usuario']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $user['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
