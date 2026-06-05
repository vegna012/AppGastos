<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario</title>
</head>
<body>
    <h1>Crear usuario</h1>

    <p><a href="/usuarios">Volver al listado</a></p>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="/usuarios">
        <div>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" required
                   value="<?= htmlspecialchars((string) ($old['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <div>
            <label for="correo">Correo</label><br>
            <input type="email" id="correo" name="correo" required
                   value="<?= htmlspecialchars((string) ($old['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        <div>
            <label for="password">Contraseña</label><br>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>
        <br>
        <div>
            <label for="password_confirm">Confirmar contraseña</label><br>
            <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
        </div>
        <br>
        <div>
            <label for="id_rol">Rol</label><br>
            <select id="id_rol" name="id_rol" required>
                <option value="">Seleccione un rol</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id_rol'] ?>"
                        <?= ((string) ($old['id_rol'] ?? '') === (string) $role['id_rol']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $role['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
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
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
