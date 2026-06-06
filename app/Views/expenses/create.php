<?php $pageTitle = 'Crear gasto'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Crear gasto</h1>
    <div class="page-actions">
        <a href="/mis-gastos" class="btn btn-secondary btn-sm">Mis gastos</a>
    </div>
</div>

<form method="post" action="/gastos" class="col-lg-8">
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
        </ul>
    <?php endif; ?>

    <form method="post" action="/gastos">
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

        <div class="mb-3">
            <label for="total" class="form-label">Monto</label>
            <input type="number" class="form-control" id="total" name="total" min="0" step="0.01"
                   value="<?= htmlspecialchars((string) ($old['total'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <?php if ($budgetAvailability !== null): ?>
            <div class="alert alert-info">
                <strong>Disponibilidad presupuestal</strong><br>

                <?php if (!$budgetAvailability['configured']): ?>
                    <p class="mb-0">Sin presupuesto configurado para el periodo.</p>
                <?php else: ?>
                    <p class="mb-0">
                        Presupuesto: $<?= htmlspecialchars(number_format($budgetAvailability['presupuesto'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?><br>
                        Consumo: $<?= htmlspecialchars(number_format($budgetAvailability['consumo'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?><br>
                        Disponible: $<?= htmlspecialchars(number_format($budgetAvailability['disponible'], 2, '.', ','), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exceedsBudgetWarning)): ?>
            <div class="alert alert-warning">
                Advertencia: el monto capturado supera el presupuesto disponible para el periodo.
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <label for="observaciones" class="form-label">Observaciones (opcional)</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars((string) ($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <button type="submit" class="btn btn-secondary" formaction="/gastos/crear" formmethod="get" formnovalidate>
            Consultar presupuesto
        </button>

        <button type="submit" class="btn btn-primary">
            Guardar
        </button>
    </form>
