<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$titulo = 'Términos y Condiciones';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 mt-2">
  <a href="javascript:history.back()" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Términos y Condiciones</h1>
</div>

<div class="card card-body shadow-sm border-0 bg-body">
    <p>El presente sistema es una herramienta interna para el registro de ventas de <strong>Tienda Navideña</strong>.</p>
    <ul>
        <li><strong>Uso:</strong> El acceso está restringido al personal autorizado. El usuario es responsable de mantener su contraseña segura.</li>
        <li><strong>Propiedad Intelectual:</strong> El código, diseño e imágenes utilizadas son propiedad del negocio o cuentan con licencia.</li>
        <li><strong>Limitación:</strong> Esta herramienta registra ventas, pero no procesa pagos con tarjeta directamente.</li>
    </ul>
    <p class="mb-0">Ley aplicable: Legislación de la República del Perú.</p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>