<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$titulo = 'Aviso Legal';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 mt-2">
  <a href="javascript:history.back()" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Aviso Legal</h1>
</div>
<div class="card card-body shadow-sm border-0 bg-body">
    <p class="fw-bold mb-2">Datos del Proveedor:</p>
    <ul class="mb-0">
        <li><strong>Nombre / Razón Social:</strong> [Tu Nombre Completo o Empresa]</li>
        <li><strong>RUC:</strong> [Tu RUC]</li>
        <li><strong>Domicilio:</strong> [Tu Dirección en Perú]</li>
        <li><strong>Correo electrónico:</strong> [Tu Correo]</li>
        <li><strong>Teléfono / WhatsApp:</strong> [Tu Teléfono]</li>
    </ul>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>