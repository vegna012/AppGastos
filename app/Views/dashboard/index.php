<?php $pageTitle = 'Dashboard'; ?>

<div class="page-header">
    <h1 class="h3 mb-0">Bienvenido <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h1>
</div>

<div class="row g-3">
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 card-title">Usuarios</h2>
                <p class="card-text text-muted small">Administrar usuarios del sistema.</p>
                <a href="/usuarios" class="btn btn-primary mt-auto align-self-start">Ir a Usuarios</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 card-title">Áreas</h2>
                <p class="card-text text-muted small">Gestionar áreas organizacionales.</p>
                <a href="/areas" class="btn btn-primary mt-auto align-self-start">Ir a Áreas</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 card-title">Presupuestos</h2>
                <p class="card-text text-muted small">Consultar y crear presupuestos.</p>
                <a href="/presupuestos" class="btn btn-primary mt-auto align-self-start">Ir a Presupuestos</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 card-title">Gastos</h2>
                <p class="card-text text-muted small">Capturar un nuevo gasto.</p>
                <a href="/gastos/crear" class="btn btn-primary mt-auto align-self-start">Crear gasto</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 card-title">Mis Gastos</h2>
                <p class="card-text text-muted small">Ver y gestionar tus gastos.</p>
                <a href="/mis-gastos" class="btn btn-primary mt-auto align-self-start">Ir a Mis Gastos</a>
            </div>
        </div>
    </div>
    <?php if (!empty($canAccessApprovals)): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5 card-title">Aprobaciones</h2>
                    <p class="card-text text-muted small">Revisar gastos pendientes de aprobación.</p>
                    <a href="/aprobaciones" class="btn btn-primary mt-auto align-self-start">Ir a Aprobaciones</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
