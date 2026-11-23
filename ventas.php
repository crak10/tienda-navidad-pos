<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();
csrf_verificar();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$error = '';
$accion = $_POST['accion'] ?? '';

// --- AGREGAR AL CARRITO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'agregar') {
    $productoId = (int)($_POST['producto_id'] ?? 0);
    $cantidad   = (int)($_POST['cantidad'] ?? 0);

    if ($productoId > 0 && $cantidad > 0) {
        $stmt = $pdo->prepare('SELECT id, nombre, precio, stock FROM productos WHERE id = ? AND disponible = 1');
        $stmt->execute([$productoId]);
        $p = $stmt->fetch();

        if ($p) {
            $cantActual = isset($_SESSION['carrito'][$p['id']]) ? $_SESSION['carrito'][$p['id']]['cantidad'] : 0;
            $nuevaCant = $cantActual + $cantidad;

            if ($nuevaCant <= $p['stock']) {
                $_SESSION['carrito'][$p['id']] = [
                    'nombre' => $p['nombre'],
                    'precio' => (float)$p['precio'],
                    'cantidad' => $nuevaCant,
                    'stock' => $p['stock']
                ];
                flash('success', 'Producto agregado al carrito.');
            } else {
                $error = 'No hay suficiente stock. Solo quedan ' . $p['stock'];
            }
        }
    }
    redirect('ventas.php');
}

// --- ELIMINAR DEL CARRITO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    unset($_SESSION['carrito'][$id]);
    redirect('ventas.php');
}

// --- VACIAR CARRITO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'vaciar') {
    $_SESSION['carrito'] = [];
    redirect('ventas.php');
}

// --- COBRAR (GUARDAR EN BASE DE DATOS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'cobrar') {
    if (empty($_SESSION['carrito'])) {
        $error = 'El carrito está vacío.';
    } else {
        $u = usuario_actual();
        $ticketId = uniqid('T-');

        try {
            $pdo->beginTransaction();
            foreach ($_SESSION['carrito'] as $id => $item) {
                $totalLinea = round($item['precio'] * $item['cantidad'], 2);
                $stmt = $pdo->prepare(
                    'INSERT INTO ventas (ticket_id, usuario_id, producto_id, producto_nombre, cantidad, precio_unitario, total, fecha)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
                );
                $stmt->execute([$ticketId, $u['id'], $id, $item['nombre'], $item['cantidad'], $item['precio'], $totalLinea]);

                $stmtStock = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
                $stmtStock->execute([$item['cantidad'], $id]);
            }
            $pdo->commit(); 
            
            $_SESSION['ticket_reciente'] = $ticketId;
            $_SESSION['carrito'] = []; 
            redirect('ventas.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Hubo un error al procesar la venta.';
        }
    }
}

// --- LÓGICA PARA MOSTRAR LA VENTANA FLOTANTE DEL TICKET ---
$ticketData = null;
if (isset($_SESSION['ticket_reciente'])) {
    $ticketReciente = $_SESSION['ticket_reciente'];
    unset($_SESSION['ticket_reciente']); 
    
    $stmt = $pdo->prepare('SELECT producto_nombre, cantidad, precio_unitario, total, fecha, ticket_id FROM ventas WHERE ticket_id = ?');
    $stmt->execute([$ticketReciente]);
    $ticketData = $stmt->fetchAll();
}

$productos = $pdo->query('SELECT id, nombre, precio, stock FROM productos WHERE disponible = 1 AND stock > 0 ORDER BY nombre ASC')->fetchAll();
$totalCarrito = 0;
foreach ($_SESSION['carrito'] as $item) {
    $totalCarrito += $item['precio'] * $item['cantidad'];
}

$titulo = 'Registrar venta';
require __DIR__ . '/includes/header.php';
?>

<!-- Librería para capturar el ticket como imagen -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    /* Estilos exclusivos para el diseño del Ticket tipo Factura */
    .ticket-box {
        font-family: 'Courier New', Courier, monospace;
        max-width: 380px;
        margin: 0 auto;
        background: #fff;
        color: #000;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .ticket-line {
        border-bottom: 1px dashed #000;
        margin: 10px 0;
    }
    .ticket-table th, .ticket-table td {
        font-size: 0.85rem;
        padding: 3px 0;
        border: none;
    }
    .qr-code {
        width: 90px;
        height: 90px;
    }

    /* Magia para que la impresora solo saque el ticket */
    @media print {
        @page { margin: 0; }
        body * { visibility: hidden; }
        #ticket-imprimible, #ticket-imprimible * { visibility: visible; }
        #ticket-imprimible { 
            position: absolute; left: 0; top: 0; width: 100%; 
            border: none !important; box-shadow: none !important;
        }
        .no-imprimir { display: none !important; }
    }

    /* Estilos para el Toast (aviso flotante) */
    .custom-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #198754; /* Verde de éxito */
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        display: none;
        font-weight: 500;
        max-width: 350px;
    }
</style>

<div class="d-flex align-items-center mb-4 no-imprimir">
  <a href="dashboard.php" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h4 mb-0 fw-bold text-secondary">Punto de Venta</h1>
</div>

<?php if ($error): ?><div class="alert alert-danger py-2 border-0 shadow-sm no-imprimir"><?= e($error) ?></div><?php endif; ?>

<!-- FORMULARIO PARA AGREGAR PRODUCTOS -->
<?php if ($productos): ?>
<form method="post" class="card card-body shadow-sm border-0 mb-4 no-imprimir">
  <input type="hidden" name="accion" value="agregar">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label fw-semibold text-muted">Producto</label>
    <select name="producto_id" class="form-select form-select-lg bg-light" required>
      <?php foreach ($productos as $p): ?>
        <option value="<?= (int)$p['id'] ?>">
          <?= e($p['nombre']) ?> — <?= formato_precio((float)$p['precio']) ?> (Quedan <?= (int)$p['stock'] ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label fw-semibold text-muted">Cantidad</label>
    <input type="number" name="cantidad" class="form-control form-control-lg bg-light" value="1" min="1" required>
  </div>
  <button type="submit" class="btn btn-primary btn-lg" style="background-color: #4A3ACF; border: none;">
    <i class="bi bi-cart-plus"></i> Agregar a la lista
  </button>
</form>
<?php endif; ?>

<!-- LISTA DEL CARRITO -->
<div class="card shadow-sm border-0 mb-4 no-imprimir">
  <div class="card-header bg-body border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0 text-body">Artículos en venta</h5>
      <?php if (!empty($_SESSION['carrito'])): ?>
          <form method="post" class="m-0">
              <input type="hidden" name="accion" value="vaciar">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i> Vaciar</button>
          </form>
      <?php endif; ?>
  </div>
  <div class="card-body p-0">
    <?php if (empty($_SESSION['carrito'])): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-cart-x fs-1 mb-2 d-block"></i>
        La lista está vacía
      </div>
    <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-body">
            <div>
              <div class="fw-bold text-body"><?= e($item['nombre']) ?></div>
              <div class="small text-muted"><?= $item['cantidad'] ?> × <?= formato_precio($item['precio']) ?></div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="fw-bold" style="color: #4A3ACF;"><?= formato_precio($item['precio'] * $item['cantidad']) ?></span>
              <form method="post" class="m-0">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="<?= (int)$id ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-x-lg"></i></button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<!-- BOTÓN DE COBRAR -->
<?php if (!empty($_SESSION['carrito'])): ?>
<form method="post" class="no-imprimir">
  <input type="hidden" name="accion" value="cobrar">
  <?= csrf_field() ?>
  <div class="alert" style="background-color: #f0efff; border-color: #4A3ACF;">
    <div class="small fw-semibold" style="color: #4A3ACF;">TOTAL A COBRAR</div>
    <div class="display-5 fw-bold mb-0" style="color: #4A3ACF;"><?= formato_precio($totalCarrito) ?></div>
  </div>
  <button type="submit" class="btn btn-success btn-lg py-3 w-100 shadow-sm" style="font-size: 1.2rem;">
    <i class="bi bi-check2-circle"></i> Cobrar e Imprimir Ticket
  </button>
</form>
<?php endif; ?>


<!-- VENTANA FLOTANTE (MODAL) DEL TICKET RECIÉN COBRADO -->
<?php if ($ticketData): 
    $totalTicket = array_sum(array_column($ticketData, 'total'));
    $fechaTicket = $ticketData[0]['fecha'];
    $idTicket = $ticketData[0]['ticket_id'];
    
    // Generador de código QR
    $qrData = urlencode("Ticket: $idTicket | Total: S/ $totalTicket | Fecha: " . date('d/m/Y', strtotime($fechaTicket)));
    $urlQR = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=$qrData";
?>
<div class="modal fade show" id="modalTicket" tabindex="-1" style="display: block; background: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-0 no-imprimir">
        <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill"></i> ¡Venta Exitosa!</h5>
        <button type="button" class="btn-close" onclick="document.getElementById('modalTicket').style.display='none';"></button>
      </div>
      
      <!-- ESTA ES LA ZONA QUE SE CONVIERTE EN IMAGEN Y SE IMPRIME -->
      <div class="modal-body bg-light" id="ticket-imprimible">
        
        <div class="ticket-box shadow-sm" id="ticketCaptura">
            <!-- Cabecera del negocio -->
            <div class="text-center">
                <h5 class="fw-bold mb-1" style="font-family: Arial, sans-serif;"><?= e(strtoupper($CONFIG['app_name'] ?? 'MI TIENDA')) ?></h5>
                <p class="mb-0" style="font-size: 0.8rem;">RUC: <?= e($CONFIG['negocio_ruc'] ?? '00000000000') ?></p>
                <p class="mb-0" style="font-size: 0.8rem;"><?= e($CONFIG['negocio_direccion'] ?? 'Dirección de la tienda') ?></p>
                <p class="mb-0" style="font-size: 0.8rem;">Teléf: <?= e($CONFIG['negocio_telefono'] ?? '999 999 999') ?></p>
            </div>
            
            <div class="ticket-line"></div>
            
            <!-- Datos del ticket -->
            <div class="text-center">
                <strong style="font-size: 0.9rem;">TICKET DE VENTA ELECTRÓNICO</strong><br>
                <span style="font-size: 0.85rem;"><?= e($idTicket) ?></span><br>
                <span style="font-size: 0.8rem;"><?= date('d/m/Y H:i:s', strtotime($fechaTicket)) ?></span>
            </div>
            
            <div class="ticket-line"></div>
            
            <!-- Tabla de productos -->
            <table class="table ticket-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 15%;">Cant.</th>
                        <th style="width: 45%;">Descripción</th>
                        <th class="text-end" style="width: 20%;">P. Unit</th>
                        <th class="text-end" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketData as $item): ?>
                    <tr>
                        <td><?= (int)$item['cantidad'] ?></td>
                        <td><?= e($item['producto_nombre']) ?></td>
                        <td class="text-end"><?= number_format((float)$item['precio_unitario'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$item['total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="ticket-line"></div>
            
            <!-- Totales -->
            <div class="d-flex justify-content-between mb-1">
                <span style="font-size: 0.9rem;">Subtotal:</span>
                <span style="font-size: 0.9rem;">S/ <?= number_format($totalTicket, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span style="font-size: 0.9rem;">IGV (18%):</span>
                <span style="font-size: 0.9rem;">S/ <?= number_format($totalTicket * 0.18, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <strong class="fs-6">TOTAL A PAGAR:</strong>
                <strong class="fs-6">S/ <?= number_format($totalTicket, 2) ?></strong>
            </div>
            
            <div class="ticket-line"></div>
            
            <!-- Pie de página con QR -->
            <div class="text-center mt-3">
                <img src="<?= $urlQR ?>" alt="QR Ticket" class="qr-code mb-2" crossorigin="anonymous">
                <p class="mb-0" style="font-size: 0.65rem;">
                    Representación impresa del Ticket de Venta.<br>
                    ¡Gracias por su compra!
                </p>
            </div>
        </div>

      </div>
      
      <div class="modal-footer border-0 d-grid gap-2 no-imprimir bg-light">
        <button type="button" class="btn btn-success py-2 fw-bold" onclick="compartirImagen()">
            <i class="bi bi-whatsapp"></i> Enviar Imagen por WhatsApp
        </button>
        <button type="button" class="btn btn-primary py-2" onclick="window.print();">
            <i class="bi bi-printer"></i> Imprimir Ticket
        </button>
      </div>
    </div>
  </div>
</div>

<!-- El contenedor del aviso flotante (Toast) -->
<div id="toastAviso" class="custom-toast">
    <i class="bi bi-check-circle-fill me-2"></i> <span id="toastMensaje">Imagen copiada. Pega con Ctrl+V.</span>
</div>

<!-- Script inteligente: Celular (Compartir) / PC (Copiar y abrir WhatsApp Web) -->
<script>
// Función para mostrar el aviso flotante en lugar del alert() feo
function mostrarToast(mensaje) {
    const toast = document.getElementById('toastAviso');
    document.getElementById('toastMensaje').innerText = mensaje;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 5000); // Se oculta a los 5 segundos
}

function compartirImagen() {
    const ticketElement = document.getElementById('ticketCaptura');
    
    html2canvas(ticketElement, { scale: 2, backgroundColor: '#ffffff' }).then(canvas => {
        canvas.toBlob(blob => {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                // --- LÓGICA PARA CELULAR ---
                const file = new File([blob], 'ticket_venta.png', { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        files: [file],
                        title: 'Ticket de Venta'
                    }).catch(console.error);
                } else {
                    mostrarToast("Tu celular no soporta compartir imágenes. Descargando...");
                    descargarImagen(canvas);
                }
            } else {
                // --- LÓGICA PARA COMPUTADORA (PC) ---
                try {
                    const item = new ClipboardItem({ "image/png": blob });
                    navigator.clipboard.write([item]).then(() => {
                        window.open('https://web.whatsapp.com/', '_blank');
                        // Usamos el aviso flotante elegante
                        mostrarToast("✅ Imagen copiada. Busca el chat en WhatsApp Web y presiona Ctrl+V.");
                    }).catch(err => {
                        console.error("Error al copiar al portapapeles: ", err);
                        descargarImagen(canvas);
                    });
                } catch (e) {
                    descargarImagen(canvas);
                }
            }
        });
    });
}

function descargarImagen(canvas) {
    const link = document.createElement('a');
    link.download = 'ticket_venta.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    mostrarToast("Imagen descargada. Puedes arrastrarla a WhatsApp Web manualmente.");
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>