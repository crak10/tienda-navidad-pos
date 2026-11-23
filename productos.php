<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();
csrf_verificar();

// Alternar disponibilidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE productos SET disponible = 1 - disponible WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Producto actualizado.');
    }
    redirect('productos.php');
}

// Traemos los productos incluyendo el stock
$productos = $pdo->query(
    'SELECT id, nombre, precio, disponible, stock FROM productos
     ORDER BY disponible DESC, nombre ASC'
)->fetchAll();

$titulo = 'Productos';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0 fw-bold text-secondary">Productos</h1>
  <a href="producto_form.php" class="btn btn-primary" style="background-color: #4A3ACF; border: none;">
    <i class="bi bi-plus-lg"></i> Agregar
  </a>
</div>

<?php if (!$productos): ?>
  <div class="alert alert-info border-0 shadow-sm">
    Todavía no hay productos. Toca <b>Agregar</b> para crear el primero.
  </div>
<?php else: ?>
  <!-- Aquí empieza la cuadrícula: 1 columna en celular, 2 en tablet, 3 en PC -->
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    <?php foreach ($productos as $p): ?>
      <div class="col">
        <div class="card h-100 shadow-sm border-0 <?= $p['disponible'] ? 'bg-body' : 'bg-light opacity-75' ?>">
          <div class="card-body d-flex flex-column">
            
            <h5 class="card-title fw-bold text-body mb-1"><?= e($p['nombre']) ?></h5>
            <h6 class="card-subtitle mb-3 text-muted fw-semibold"><?= formato_precio((float)$p['precio']) ?></h6>
            
            <div class="mb-3 d-flex flex-wrap gap-2">
              <span class="badge bg-<?= $p['disponible'] ? 'success' : 'secondary' ?>">
                <?= $p['disponible'] ? 'Disponible' : 'No disponible' ?>
              </span>
              <span class="badge bg-secondary bg-opacity-10 text-dark border">
                Stock: <?= (int)($p['stock'] ?? 0) ?>
              </span>
            </div>

            <!-- Botones empujados hacia abajo -->
            <div class="mt-auto d-grid gap-2">
              <a href="producto_form.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary" style="color: #4A3ACF; border-color: #4A3ACF;">
                <i class="bi bi-pencil"></i> Editar
              </a>
              
              <form method="post" class="m-0">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                  <?= $p['disponible'] ? 'Marcar Agotado' : 'Activar' ?>
                </button>
              </form>
            </div>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>