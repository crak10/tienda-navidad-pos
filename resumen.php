<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();
csrf_verificar();

// --- LÓGICA PARA ANULAR UN TICKET ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'anular') {
    $ticketId = $_POST['ticket_id'] ?? '';
    if ($ticketId) {
        try {
            $pdo->beginTransaction();
            
            // 1. Buscar los productos de este ticket para devolver el stock
            $stmt = $pdo->prepare('SELECT producto_id, cantidad FROM ventas WHERE ticket_id = ? AND estado = "completado"');
            $stmt->execute([$ticketId]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                $stmtStock = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
                $stmtStock->execute([$item['cantidad'], $item['producto_id']]);
            }

            // 2. Marcar el ticket como anulado
            $stmt = $pdo->prepare('UPDATE ventas SET estado = "anulado" WHERE ticket_id = ?');
            $stmt->execute([$ticketId]);

            $pdo->commit();
            flash('success', 'Ticket anulado y stock devuelto.');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('danger', 'Error al anular el ticket.');
        }
    }
    redirect('resumen.php?rango=' . ($_GET['rango'] ?? 'hoy'));
}

// 1. Lógica del filtro de fechas
$rango = $_GET['rango'] ?? 'hoy';
$hoy = date('Y-m-d');

switch ($rango) {
    case '7dias': $inicio = date('Y-m-d', strtotime('-6 days')); $fin = $hoy; $tituloRango = 'Últimos 7 días'; break;
    case 'este_mes': $inicio = date('Y-m-01'); $fin = date('Y-m-t'); $tituloRango = 'Este mes'; break;
    case 'mes_pasado': $inicio = date('Y-m-01', strtotime('first day of last month')); $fin = date('Y-m-t', strtotime('last day of last month')); $tituloRango = 'Mes pasado'; break;
    case 'este_ano': $inicio = date('Y-01-01'); $fin = date('Y-12-31'); $tituloRango = 'Este año'; break;
    default: $inicio = $hoy; $fin = $hoy; $tituloRango = 'Hoy'; break;
}

// 2. Totales (Solo contamos los tickets 'completado')
$stmt = $pdo->prepare('
    SELECT COUNT(DISTINCT IFNULL(ticket_id, id)) AS num, COALESCE(SUM(total),0) AS total 
    FROM ventas WHERE DATE(fecha) BETWEEN ? AND ? AND estado = "completado"
');
$stmt->execute([$inicio, $fin]);
$totales = $stmt->fetch();

// 3. Productos más vendidos
$stmt = $pdo->prepare('
    SELECT producto_nombre, SUM(cantidad) AS unidades, SUM(total) AS total
    FROM ventas WHERE DATE(fecha) BETWEEN ? AND ? AND estado = "completado" 
    GROUP BY producto_nombre ORDER BY unidades DESC, total DESC LIMIT 10
');
$stmt->execute([$inicio, $fin]);
$top = $stmt->fetchAll();

// 4. Últimos tickets (Mostramos también los anulados)
$stmt = $pdo->prepare('
    SELECT 
        IFNULL(v.ticket_id, CONCAT("V-", v.id)) AS ticket_id,
        MIN(v.fecha) AS fecha, SUM(v.total) AS total_ticket, SUM(v.cantidad) AS total_items,
        MAX(u.usuario) AS usuario, MAX(v.estado) AS estado
     FROM ventas v LEFT JOIN usuarios u ON u.id = v.usuario_id
     WHERE DATE(v.fecha) BETWEEN ? AND ? 
     GROUP BY ticket_id ORDER BY fecha DESC LIMIT 20
');
$stmt->execute([$inicio, $fin]);
$ultimas = $stmt->fetchAll();

$titulo = 'Resumen de ventas';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4">
  <a href="dashboard.php" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Resumen de ventas</h1>
</div>

<!-- Selector de Fechas -->
<form method="GET" class="mb-3">
    <label class="form-label fw-semibold text-muted small text-uppercase">Filtrar por periodo:</label>
    <select name="rango" class="form-select form-select-lg bg-body-tertiary shadow-sm border-0 fw-semibold" onchange="this.form.submit()">
        <option value="hoy" <?= $rango === 'hoy' ? 'selected' : '' ?>>Hoy (<?= date('d/m/Y') ?>)</option>
        <option value="7dias" <?= $rango === '7dias' ? 'selected' : '' ?>>Últimos 7 días</option>
        <option value="este_mes" <?= $rango === 'este_mes' ? 'selected' : '' ?>>Este mes (<?= date('m/Y') ?>)</option>
        <option value="mes_pasado" <?= $rango === 'mes_pasado' ? 'selected' : '' ?>>Mes pasado</option>
        <option value="este_ano" <?= $rango === 'este_ano' ? 'selected' : '' ?>>Este año (<?= date('Y') ?>)</option>
    </select>
</form>

<!-- Botón para descargar Excel -->
<div class="d-grid mb-4">
    <a href="exportar.php?rango=<?= e($rango) ?>" class="btn btn-success shadow-sm fw-bold py-2">
        <i class="bi bi-file-earmark-excel"></i> Descargar Excel de este periodo
    </a>
</div>

<p class="text-muted small fw-semibold text-uppercase mb-3">Mostrando: <span class="text-primary" style="color: #4A3ACF !important;"><?= e($tituloRango) ?></span></p>

<!-- Tarjetas de totales -->
<div class="row g-3 mb-4">
  <div class="col-6">
    <div class="card card-body shadow-sm border-0 text-center bg-body">
      <div class="small text-muted fw-semibold">Caja total</div>
      <div class="fs-4 fw-bold" style="color: #4A3ACF;"><?= formato_precio((float)$totales['total']) ?></div>
    </div>
  </div>
  <div class="col-6">
    <div class="card card-body shadow-sm border-0 text-center bg-body">
      <div class="small text-muted fw-semibold">Tickets</div>
      <div class="fs-4 fw-bold" style="color: #4A3ACF;"><?= (int)$totales['num'] ?></div>
    </div>
  </div>
</div>

<!-- Más vendidos -->
<h2 class="h6 text-muted fw-bold text-uppercase mb-3 mt-4">Más vendidos</h2>
<?php if (!$top): ?>
  <div class="alert alert-light border shadow-sm py-3">Sin ventas en este periodo.</div>
<?php else: ?>
  <div class="list-group shadow-sm mb-4">
    <?php foreach ($top as $t): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center p-3 bg-body">
        <div>
          <div class="fw-bold text-body fs-6"><?= e($t['producto_nombre']) ?></div>
          <div class="small text-muted"><?= (int)$t['unidades'] ?> unidades</div>
        </div>
        <div class="fw-bold" style="color: #4A3ACF;"><?= formato_precio((float)$t['total']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Últimos tickets -->
<h2 class="h6 text-muted fw-bold text-uppercase mb-3 mt-4">Últimos tickets</h2>
<?php if (!$ultimas): ?>
  <div class="alert alert-light border shadow-sm py-3">Aún no hay ventas.</div>
<?php else: ?>
  <div class="list-group shadow-sm">
    <?php foreach ($ultimas as $v): ?>
      <div class="list-group-item p-3 bg-body <?= $v['estado'] === 'anulado' ? 'opacity-50' : '' ?>">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="badge bg-secondary mb-1"><?= e($v['ticket_id']) ?></span>
            <?php if ($v['estado'] === 'anulado'): ?>
                <span class="badge bg-danger mb-1">ANULADO</span>
            <?php endif; ?>
            <div class="small text-muted">
              <i class="bi bi-clock"></i> <?= e(date('d/m H:i', strtotime($v['fecha']))) ?> · <?= e($v['usuario'] ?? '') ?>
            </div>
          </div>
          <div class="text-end">
            <div class="fw-bold fs-6 <?= $v['estado'] === 'anulado' ? 'text-decoration-line-through text-muted' : '' ?>" style="<?= $v['estado'] === 'completado' ? 'color: #4A3ACF;' : '' ?>">
                <?= formato_precio((float)$v['total_ticket']) ?>
            </div>
            <div class="small text-muted"><?= (int)$v['total_items'] ?> artículos</div>
            
            <?php if ($v['estado'] === 'completado'): ?>
            <form method="post" class="mt-2" onsubmit="return confirm('¿Seguro que deseas anular este ticket? El stock será devuelto.');">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="anular">
                <input type="hidden" name="ticket_id" value="<?= e($v['ticket_id']) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">Anular</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>