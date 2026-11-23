<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$titulo = 'Política de Privacidad';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 mt-2">
  <a href="javascript:history.back()" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Política de Privacidad</h1>
</div>
<div class="card card-body shadow-sm border-0 bg-body">
    <p><strong>Responsable:</strong> [Tu Nombre o Razón Social] con RUC [Tu RUC], domiciliado en [Tu Dirección].</p>
    <p><strong>Finalidad:</strong> Los datos ingresados (credenciales del administrador) se utilizan exclusivamente para gestionar el acceso al sistema de ventas de la tienda.</p>
    <p><strong>Datos recolectados:</strong> Solo solicitamos usuario y contraseña para el acceso al sistema. No capturamos datos de clientes finales en esta fase.</p>
    <p><strong>Derechos ARCO:</strong> Puedes ejercer tus derechos de Acceso, Rectificación, Cancelación y Oposición escribiendo a [Tu Correo].</p>
    <p class="mb-0"><strong>Transferencia de datos:</strong> No compartimos tus datos con terceros.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>