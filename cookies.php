<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$titulo = 'Política de Cookies';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 mt-2">
  <a href="javascript:history.back()" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Política de Cookies</h1>
</div>
<div class="card card-body shadow-sm border-0 bg-body">
    <p>Esta web utiliza cookies para funcionar correctamente.</p>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Cookie</th><th>Tipo</th><th>Finalidad</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHPSESSID</td>
                    <td><span class="badge bg-primary">Esencial</span></td>
                    <td>Necesaria para mantener tu sesión iniciada y proteger la seguridad de los formularios (CSRF). No requiere consentimiento.</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p class="text-muted small mb-0">Actualmente no utilizamos cookies de análisis ni de publicidad.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>