<?php $pageTitle = 'Crear centro de costo'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Crear centro de costo</h1>
    <div class="page-actions">
        <a href="/centros-costos" class="btn btn-secondary btn-sm">Volver al listado</a>
    </div>
</div>

<form method="post" action="/centros-costos" class="col-lg-8">
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
        <label for="codigo" class="form-label">Código</label>
        <input type="text" class="form-control" id="codigo" name="codigo" maxlength="30" required
               value="<?= htmlspecialchars((string) ($old['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
               value="<?= htmlspecialchars((string) ($old['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-4">
        <label for="descripcion" class="form-label">Descripción (opcional)</label>
        <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="255"
               value="<?= htmlspecialchars((string) ($old['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
