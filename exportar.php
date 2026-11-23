<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
requiere_login();

// Capturamos el rango de fechas (igual que en resumen.php)
$rango = $_GET['rango'] ?? 'hoy';
$hoy = date('Y-m-d');

switch ($rango) {
    case '7dias': $inicio = date('Y-m-d', strtotime('-6 days')); $fin = $hoy; break;
    case 'este_mes': $inicio = date('Y-m-01'); $fin = date('Y-m-t'); break;
    case 'mes_pasado': $inicio = date('Y-m-01', strtotime('first day of last month')); $fin = date('Y-m-t', strtotime('last day of last month')); break;
    case 'este_ano': $inicio = date('Y-01-01'); $fin = date('Y-12-31'); break;
    default: $inicio = $hoy; $fin = $hoy; break;
}

// Buscamos todas las ventas de ese periodo
$stmt = $pdo->prepare('
    SELECT 
        IFNULL(ticket_id, CONCAT("V-", id)) AS ticket_id,
        fecha, producto_nombre, cantidad, precio_unitario, total, estado
    FROM ventas 
    WHERE DATE(fecha) BETWEEN ? AND ? 
    ORDER BY fecha DESC
');
$stmt->execute([$inicio, $fin]);
$ventas = $stmt->fetchAll();

// Configuramos las cabeceras para forzar la descarga del archivo
$nombreArchivo = 'Reporte_Ventas_' . $inicio . '_al_' . $fin . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

// Abrimos la salida de PHP para escribir el archivo
$output = fopen('php://output', 'w');

// TRUCO MAESTRO: Agregamos el BOM de UTF-8 para que Excel lea bien las tildes y la ñ
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribimos los títulos de las columnas
fputcsv($output, ['Ticket', 'Fecha y Hora', 'Producto', 'Cantidad', 'Precio Unitario', 'Total', 'Estado'], ';');

// Escribimos cada fila de venta
foreach ($ventas as $v) {
    fputcsv($output, [
        $v['ticket_id'],
        date('d/m/Y H:i', strtotime($v['fecha'])),
        $v['producto_nombre'],
        $v['cantidad'],
        number_format((float)$v['precio_unitario'], 2, '.', ''),
        number_format((float)$v['total'], 2, '.', ''),
        strtoupper($v['estado'])
    ], ';');
}

fclose($output);
exit;