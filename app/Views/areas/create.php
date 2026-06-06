<?php $pageTitle = 'Crear área'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Crear área</h1>
    <div class="page-actions">
        <a href="/areas" class="btn btn-secondary btn-sm">Volver al listado</a>
    </div>
</div>

<form method="post" action="/areas" class="col-lg-8">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required
               value="<?= htmlspecialchars((string) ($old['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-4">
        <label for="id_jefe_area" class="form-label">Jefe de área (opcional)</label>
        <select class="form-select" id="id_jefe_area" name="id_jefe_area">
            <option value="">Sin jefe asignado</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int) $user['id_usuario'] ?>"
                    <?= ((string) ($old['id_jefe_area'] ?? '') === (string) $user['id_usuario']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $user['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
