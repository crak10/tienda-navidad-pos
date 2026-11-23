<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();

// Consultamos el total vendido hoy para el KPI
$hoy = date('Y-m-d');
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total),0) AS total, COUNT(DISTINCT IFNULL(ticket_id, id)) as num FROM ventas WHERE DATE(fecha) = ? AND estado = "completado"');
$stmt->execute([$hoy]);
$stats = $stmt->fetch();

$titulo = 'Inicio';
require __DIR__ . '/includes/header.php';
?>

<style>
    /* Estilos específicos para el Dashboard con el morado #4A3ACF */
    .glass-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    .btn-purple {
        background-color: #4A3ACF;
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-purple:hover { background-color: #3b2eb0; color: white; transform: translateY(-2px); }
    .kpi-number { font-size: 2.5rem; font-weight: 700; color: #4A3ACF; }
</style>

<!-- Tarjeta KPI de Ventas de Hoy -->
<div class="row mb-4">
    <div class="col-12">
        <div class="glass-card p-4 text-center">
            <p class="text-body-secondary small mb-1 fw-semibold text-uppercase">Ventas de hoy</p>
            <h2 class="kpi-number mb-0" id="kpi-total">S/ 0.00</h2>
            <span class="badge bg-success bg-opacity-25 text-success mt-2" id="kpi-count">0 ventas registradas</span>
        </div>
    </div>
</div>

<h1 class="h5 mb-3 text-body-secondary fw-semibold">¿Qué quieres hacer?</h1>

<!-- Botones de Acción Principales -->
<div class="d-grid gap-3 mb-5">
    <a href="ventas.php" class="btn btn-purple btn-lg py-4 shadow-sm" style="font-size: 1.2rem;">
        <i class="bi bi-receipt"></i> Registrar una venta
    </a>
    <a href="productos.php" class="btn btn-outline-primary btn-lg py-4" style="border-color: #4A3ACF; color: #4A3ACF;">
        <i class="bi bi-box-seam"></i> Productos
    </a>
    <a href="resumen.php" class="btn btn-outline-secondary btn-lg py-4">
        <i class="bi bi-bar-chart-line"></i> Resumen del día
    </a>
    <a href="perfil.php" class="btn btn-outline-secondary btn-lg py-4">
        <i class="bi bi-person-gear"></i> Mi Perfil / Contraseña
    </a>
</div>

<script>
    // Animación del contador (KPI) al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        const targetTotal = <?= (float)$stats['total'] ?>;
        const targetCount = <?= (int)$stats['num'] ?>;
        const elTotal = document.getElementById('kpi-total');
        const elCount = document.getElementById('kpi-count');
        
        let currentTotal = 0;
        const duration = 1500; // 1.5 segundos de animación
        const steps = 60;
        const increment = targetTotal / steps;
        let step = 0;

        if (targetTotal > 0) {
            const timer = setInterval(() => {
                step++;
                currentTotal += increment;
                if (step >= steps) {
                    currentTotal = targetTotal;
                    clearInterval(timer);
                }
                // Formatear como S/ 1,234.50
                elTotal.textContent = 'S/ ' + currentTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }, duration / steps);
        }
        
        // Texto en singular o plural según la cantidad de ventas
        if (targetCount === 1) {
            elCount.textContent = '1 venta registrada';
        } else {
            elCount.textContent = targetCount + ' ventas registradas';
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>