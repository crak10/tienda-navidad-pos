<?php
declare(strict_types=1);

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true,
        'secure' => $secure, 'samesite' => 'Lax',
    ]);
    session_start();
}

$configPath = dirname(__DIR__, 3) . '/config/tienda.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Falta el archivo de configuración en: ' . htmlspecialchars($configPath));
}
$CONFIG = require $configPath;

date_default_timezone_set($CONFIG['timezone'] ?? 'America/Lima');

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $CONFIG['db_host'], $CONFIG['db_name'], $CONFIG['db_charset'] ?? 'utf8mb4'
);
try {
    $pdo = new PDO($dsn, $CONFIG['db_user'], $CONFIG['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Revisa config/tienda.php');
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';