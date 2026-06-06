<?php $pageTitle = 'Crear presupuesto'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Crear presupuesto</h1>
    <div class="page-actions">
        <a href="/presupuestos" class="btn btn-secondary btn-sm">Volver al listado</a>
    </div>
</div>

<form method="post" action="/presupuestos" class="col-lg-8">
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
        <label for="anio" class="form-label">Año</label>
        <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2100" required
               value="<?= htmlspecialchars((string) ($old['anio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-3">
        <label for="mes" class="form-label">Mes</label>
        <select class="form-select" id="mes" name="mes" required>
            <option value="">Seleccione un mes</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>"
                    <?= ((string) ($old['mes'] ?? '') === (string) $m) ? 'selected' : '' ?>>
                    <?= $m ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="mb-4">
        <label for="monto" class="form-label">Monto</label>
        <input type="number" class="form-control" id="monto" name="monto" min="0.01" step="0.01" required
               value="<?= htmlspecialchars((string) ($old['monto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
