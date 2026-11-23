<?php
declare(strict_types=1);

function usuario_actual(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function requiere_login(): void {
    if (!usuario_actual()) {
        redirect('login.php');
    }
}

function iniciar_sesion(int $id, string $usuario): void {
    session_regenerate_id(true);
    $_SESSION['usuario'] = ['id' => $id, 'usuario' => $usuario];
}

function cerrar_sesion(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}