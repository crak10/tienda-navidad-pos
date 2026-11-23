<?php
declare(strict_types=1);
$titulo  = $titulo ?? 'Inicio';
$usuario = usuario_actual();
$appName = $CONFIG['app_name'] ?? 'Tienda';
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#4A3ACF">
  <title><?= e($titulo) ?> · <?= e($appName) ?></title>
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" href="assets/icons/icon.svg">
  
  <!-- Bootstrap y sus iconos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="assets/css/app.css" rel="stylesheet">
  
  <!-- Script para aplicar el tema guardado antes de que cargue la página (evita parpadeos) -->
  <script>
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
  </script>
</head>
<body class="bg-body-tertiary">
<?php if ($usuario): ?>
  <nav class="navbar navbar-dark bg-primary mb-3" style="background-color: #4A3ACF !important;">
    <div class="container">
      <a href="dashboard.php" class="navbar-brand fw-bold text-decoration-none"><i class="bi bi-shop me-1"></i> <?= e($appName) ?></a>
      
      <div class="d-flex align-items-center">
        <!-- Botón de Modo Oscuro / Claro -->
        <button class="btn btn-sm btn-outline-light me-2" id="themeToggle" title="Cambiar tema">
          <i class="bi bi-moon-fill"></i>
        </button>
        <a class="btn btn-sm btn-outline-light" href="logout.php">Salir</a>
      </div>
    </div>
  </nav>
<?php endif; ?>
<main class="container pb-5">
<?php foreach (get_flashes() as $f): ?>
  <div class="alert alert-<?= e($f['tipo']) ?> py-2"><?= e($f['mensaje']) ?></div>
<?php endforeach; ?>