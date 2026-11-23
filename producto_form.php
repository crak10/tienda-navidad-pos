<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();
csrf_verificar();

$id         = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$esEdicion  = $id > 0;
$nombre     = '';
$categoria  = 'General';
$precio     = '';
$stock      = 0;
$disponible = 1;
$error      = '';

if ($esEdicion) {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) {
        flash('danger', 'Producto no encontrado.');
        redirect('productos.php');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $nombre     = $p['nombre'];
        $categoria  = $p['categoria'] ?? 'General';
        $precio     = number_format((float)$p['precio'], 2, '.', '');
        $stock      = (int)$p['stock'];
        $disponible = (int)$p['disponible'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre'] ?? '');
    $categoria  = trim($_POST['categoria'] ?? 'General');
    $precio     = trim($_POST['precio'] ?? '');
    $stock      = (int)($_POST['stock'] ?? 0);
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if ($nombre === '') {
        $error = 'Escribe el nombre del producto.';
    } elseif ($precio === '' || !is_numeric($precio) || (float)$precio < 0) {
        $error = 'El precio debe ser un número mayor o igual a 0.';
    } else {
        $precioNum = round((float)$precio, 2);
        if ($esEdicion) {
            $stmt = $pdo->prepare('UPDATE productos SET nombre = ?, categoria = ?, precio = ?, stock = ?, disponible = ? WHERE id = ?');
            $stmt->execute([$nombre, $categoria, $precioNum, $stock, $disponible, $id]);
            flash('success', 'Producto actualizado.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO productos (nombre, categoria, precio, stock, disponible) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nombre, $categoria, $precioNum, $stock, $disponible]);
            flash('success', 'Producto agregado.');
        }
        redirect('productos.php');
    }
}

$titulo = $esEdicion ? 'Editar producto' : 'Nuevo producto';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4">
  <a href="productos.php" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary"><?= e($titulo) ?></h1>
</div>

<?php if ($error): ?><div class="alert alert-danger py-2 border-0 shadow-sm"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="card card-body shadow-sm border-0">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">

  <div class="mb-3">
    <label class="form-label fw-semibold text-muted">Nombre del producto</label>
    <input type="text" name="nombre" class="form-control form-control-lg bg-light" value="<?= e($nombre) ?>" required maxlength="100" autofocus>
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold text-muted">Categoría</label>
    <input type="text" name="categoria" class="form-control form-control-lg bg-light" value="<?= e($categoria) ?>" placeholder="Ej: Adornos, Comida, Luces..." list="lista-categorias">
    <datalist id="lista-categorias">
        <option value="Adornos">
        <option value="Comida">
        <option value="Luces">
        <option value="General">
    </datalist>
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold text-muted">Precio (S/)</label>
    <input type="number" name="precio" class="form-control form-control-lg bg-light" value="<?= e($precio) ?>" step="0.01" min="0" required>
  </div>

  <div class="mb-4">
    <label class="form-label fw-semibold text-muted">Stock (Cantidad en almacén)</label>
    <input type="number" name="stock" class="form-control form-control-lg bg-light" value="<?= e((string)$stock) ?>" min="0" required>
  </div>

  <div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" id="disponible" name="disponible" <?= $disponible ? 'checked' : '' ?> style="transform: scale(1.5); margin-right: 10px;">
    <label class="form-check-label fw-semibold ms-2 mt-1" for="disponible">Disponible para la venta</label>
  </div>

  <div class="d-grid gap-2">
    <button type="submit" class="btn btn-primary btn-lg py-3" style="background-color: #4A3ACF; border: none;">
      <i class="bi bi-check-lg"></i> Guardar Producto
    </button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>