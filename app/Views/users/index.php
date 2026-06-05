<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
</head>
<body>
    <h1>Usuarios</h1>

    <p><a href="/dashboard">Dashboard</a> | <a href="/usuarios/crear">Crear usuario</a></p>

    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Área</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6">No hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $user['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $user['correo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $user['rol_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $user['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (bool) $user['activo'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <form method="post" action="/usuarios/<?= (int) $user['id_usuario'] ?>/estado" style="display:inline;">
                                <button type="submit">
                                    <?= (bool) $user['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
