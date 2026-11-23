<?php
declare(strict_types=1);

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $tokenSesion = $_SESSION['csrf_token'] ?? '';
    $tokenPost   = $_POST['csrf_token'] ?? '';
    if ($tokenSesion === '' || !is_string($tokenPost) || !hash_equals($tokenSesion, $tokenPost)) {
        http_response_code(419);
        exit('Token de seguridad inválido. Recarga la página.');
    }
}