<?php $pageTitle = 'Usuarios'; ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h1 class="h3 mb-0">Usuarios</h1>
    <a href="/usuarios/crear" class="btn btn-primary">Crear usuario</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
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
                            <form method="post" action="/usuarios/<?= (int) $user['id_usuario'] ?>/estado" class="d-inline">
                                <button type="submit" class="btn btn-sm <?= (bool) $user['activo'] ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= (bool) $user['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
