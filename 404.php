<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$titulo = 'Página no encontrada';
require __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
    <div style="font-size: 5rem; line-height: 1;">🛍️</div>
    <h1 class="h3 mt-4 fw-bold text-secondary">No encontramos esta página</h1>
    <p class="text-muted mb-4">Es posible que el enlace esté roto o que la página haya sido movida.</p>
    <a href="dashboard.php" class="btn btn-primary btn-lg px-4 py-3 shadow-sm" style="background-color: #4A3ACF; border: none;">
        <i class="bi bi-house-door"></i> Volver al inicio
    </a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>