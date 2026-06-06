<?php $pageTitle = 'Crear usuario'; ?>

<div class="page-header">
    <h1 class="h3 mb-3">Crear usuario</h1>
    <div class="page-actions">
        <a href="/usuarios" class="btn btn-secondary btn-sm">Volver al listado</a>
    </div>
</div>

<form method="post" action="/usuarios" class="col-lg-8">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required
               value="<?= htmlspecialchars((string) ($old['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" required
               value="<?= htmlspecialchars((string) ($old['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
    </div>
    <div class="mb-3">
        <label for="password_confirm" class="form-label">Confirmar contraseña</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required autocomplete="new-password">
    </div>
    <div class="mb-3">
        <label for="id_rol" class="form-label">Rol</label>
        <select class="form-select" id="id_rol" name="id_rol" required>
            <option value="">Seleccione un rol</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= (int) $role['id_rol'] ?>"
                    <?= ((string) ($old['id_rol'] ?? '') === (string) $role['id_rol']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $role['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-4">
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
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>
