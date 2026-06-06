<?php $pageTitle = 'Áreas'; ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h1 class="h3 mb-0">Áreas</h1>
    <a href="/areas/crear" class="btn btn-primary">Crear área</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
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
                            <form method="post" action="/areas/<?= (int) $area['id_area'] ?>/estado" class="d-inline">
                                <button type="submit" class="btn btn-sm <?= (bool) $area['activo'] ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= (bool) $area['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
