<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$isActive = static function (string $path) use ($currentPath): string {
    if ($path === '/dashboard') {
        return $currentPath === '/dashboard' ? ' active' : '';
    }

    return str_starts_with($currentPath, $path) ? ' active' : '';
};
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard">App Gastos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Alternar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/dashboard') ?>" href="/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/usuarios') ?>" href="/usuarios">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/areas') ?>" href="/areas">Áreas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/centros-costos') ?>" href="/centros-costos">Centros de Costos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/presupuestos') ?>" href="/presupuestos">Presupuestos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/gastos') ?>" href="/gastos/crear">Gastos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $isActive('/mis-gastos') ?>" href="/mis-gastos">Mis Gastos</a>
                </li>
                <?php if (!empty($canAccessApprovals)): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $isActive('/aprobaciones') ?>" href="/aprobaciones">Aprobaciones</a>
                    </li>
                <?php endif; ?>
            </ul>
            <form method="post" action="/logout" class="d-flex">
                <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>
