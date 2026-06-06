<?php $pageTitle = 'Editar gasto'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Editar gasto #<?= (int) $expenseId ?></h1>
    <div class="page-actions">
        <a href="/mis-gastos" class="btn btn-secondary btn-sm">Volver a mis gastos</a>
    </div>
</div>

<form method="post" action="/gastos/<?= (int) $expenseId ?>/actualizar" class="col-lg-8">
    <div class="mb-3">
        <label for="id_area" class="form-label">Área</label>
        <select class="form-select" id="id_area" name="id_area" required>
            <option value="">Seleccione un área</option>
            <?php foreach ($areas as $area): ?>
                <option value="<?= (int) $area['id_area'] ?>"
                    <?= ((string) ($old['id_area'] ?? '') === (string) $area['id_area']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="id_centro_costo" class="form-label">Centro de costo</label>
        <select class="form-select" id="id_centro_costo" name="id_centro_costo" required>
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
    <div class="mb-3">
        <label for="fecha_gasto" class="form-label">Fecha gasto</label>
        <input type="date" class="form-control" id="fecha_gasto" name="fecha_gasto" required
               value="<?= htmlspecialchars((string) ($old['fecha_gasto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-4">
        <label for="observaciones" class="form-label">Observaciones (opcional)</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars((string) ($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>
