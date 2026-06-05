<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áreas</title>
</head>
<body>
    <h1>Áreas</h1>

    <p><a href="/dashboard">Dashboard</a> | <a href="/usuarios">Usuarios</a> | <a href="/areas/crear">Crear área</a></p>

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
                <th>Jefe de área</th>
                <th>Activa</th>
                <th>Fecha creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($areas)): ?>
                <tr>
                    <td colspan="5">No hay áreas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($areas as $area): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $area['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($area['jefe_nombre'])): ?>
                                <?= htmlspecialchars((string) $area['jefe_nombre'], ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= (bool) $area['activo'] ? 'Sí' : 'No' ?></td>
                        <td><?= htmlspecialchars((string) $area['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="post" action="/areas/<?= (int) $area['id_area'] ?>/estado" style="display:inline;">
                                <button type="submit">
                                    <?= (bool) $area['activo'] ? 'Desactivar' : 'Activar' ?>
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
