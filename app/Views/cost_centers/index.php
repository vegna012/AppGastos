<?php $pageTitle = 'Centros de Costos'; ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h1 class="h3 mb-0">Centros de Costos</h1>
    <a href="/centros-costos/crear" class="btn btn-primary">Crear centro de costo</a>
</div>

<div class="table-responsive app-table-wrap">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Área</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Activo</th>
                <th>Fecha creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($costCenters)): ?>
                <tr>
                    <td colspan="7">No hay centros de costo registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($costCenters as $costCenter): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $costCenter['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $costCenter['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $costCenter['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($costCenter['descripcion'])): ?>
                                <?= htmlspecialchars((string) $costCenter['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= (bool) $costCenter['activo'] ? 'Sí' : 'No' ?></td>
                        <td><?= htmlspecialchars((string) $costCenter['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="post"
                                  action="/centros-costos/<?= (int) $costCenter['id_centro_costo'] ?>/estado"
                                  class="d-inline">
                                <button type="submit" class="btn btn-sm <?= (bool) $costCenter['activo'] ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= (bool) $costCenter['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
