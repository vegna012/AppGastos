<?php $pageTitle = 'Mis gastos'; ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h1 class="h3 mb-0">Mis gastos</h1>
    <a href="/gastos/crear" class="btn btn-primary">Crear gasto</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha gasto</th>
                <th>Área</th>
                <th>Centro de costo</th>
                <th>Estatus</th>
                <th>Fecha creación</th>
                <th>Detalle</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
                <tr>
                    <td colspan="8">No hay gastos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= (int) $expense['id_gasto_cabecera'] ?></td>
                        <td><?= htmlspecialchars((string) $expense['fecha_gasto'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $expense['area_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars(
                                (string) $expense['centro_codigo'] . ' — ' . $expense['centro_nombre'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>
                        <td><?= htmlspecialchars((string) $expense['estatus_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $expense['creado_en'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="/mis-gastos/<?= (int) $expense['id_gasto_cabecera'] ?>" class="btn btn-sm btn-secondary">Ver</a>
                        </td>
                        <td>
                            <?php if (($expense['estatus_clave'] ?? '') === 'BORRADOR'): ?>
                                <a href="/gastos/<?= (int) $expense['id_gasto_cabecera'] ?>/editar" class="btn btn-sm btn-primary">Editar</a>
                                <form method="post"
                                      action="/gastos/<?= (int) $expense['id_gasto_cabecera'] ?>/enviar"
                                      class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-success">Enviar</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
