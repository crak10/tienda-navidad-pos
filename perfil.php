<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();
csrf_verificar();

$u = usuario_actual();
$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $nueva  = $_POST['nueva'] ?? '';
    $repetir = $_POST['repetir'] ?? '';

    if ($actual === '' || $nueva === '' || $repetir === '') {
        $error = 'Llena todos los campos.';
    } elseif ($nueva !== $repetir) {
        $error = 'La nueva contraseña y su confirmación no coinciden.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } else {
        // Verificamos la contraseña actual
        $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$u['id']]);
        $row = $stmt->fetch();

        if ($row && password_verify($actual, $row['password_hash'])) {
            // Guardamos la nueva
            $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
            $stmt->execute([$nuevoHash, $u['id']]);
            $exito = '¡Contraseña actualizada correctamente!';
        } else {
            $error = 'La contraseña actual es incorrecta.';
        }
    }
}

$titulo = 'Mi Perfil';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4">
  <a href="dashboard.php" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Mi Perfil</h1>
</div>

<div class="card card-body shadow-sm border-0 bg-body mb-4">
    <div class="d-flex align-items-center mb-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #4A3ACF !important;">
            <i class="bi bi-person-fill fs-4"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold"><?= e($u['usuario']) ?></h5>
            <small class="text-muted">Administrador</small>
        </div>
    </div>
</div>

<?php if ($error): ?><div class="alert alert-danger py-2 border-0 shadow-sm"><?= e($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success py-2 border-0 shadow-sm"><?= e($exito) ?></div><?php endif; ?>

<form method="post" class="card card-body shadow-sm border-0 bg-body">
    <h6 class="fw-bold text-muted mb-3">Cambiar Contraseña</h6>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold text-muted">Contraseña Actual</label>
        <input type="password" name="actual" class="form-control form-control-lg bg-light" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold text-muted">Nueva Contraseña</label>
        <input type="password" name="nueva" class="form-control form-control-lg bg-light" required minlength="6">
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold text-muted">Repetir Nueva Contraseña</label>
        <input type="password" name="repetir" class="form-control form-control-lg bg-light" required minlength="6">
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="background-color: #4A3ACF; border: none;">
        <i class="bi bi-shield-lock"></i> Actualizar Contraseña
    </button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>