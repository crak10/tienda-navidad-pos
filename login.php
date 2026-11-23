<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
csrf_verificar();

if (usuario_actual()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Ingresa usuario y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, usuario, password_hash FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$usuario]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            iniciar_sesion((int)$u['id'], $u['usuario']);
            redirect('dashboard.php');
        }
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entrar · <?= e($CONFIG['app_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width:420px">
  <div class="text-center mb-4">
    <div style="font-size:3rem">🎄</div>
    <h1 class="h4"><?= e($CONFIG['app_name']) ?></h1>
  </div>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="card card-body">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Usuario</label>
      <input type="text" name="usuario" class="form-control form-control-lg" required autofocus autocomplete="username">
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Contraseña</label>
      <input type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary btn-lg py-3">Entrar</button>
  </form>
</main>
</body>
</html>