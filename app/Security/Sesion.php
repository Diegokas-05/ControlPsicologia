<?php
declare(strict_types=1);

const INACTIVIDAD_MAXIMA = 1200; // 20 minutos

function solicitud_htmx(): bool {
    return strtolower((string)($_SERVER['HTTP_HX_REQUEST'] ?? '')) === 'true';
}

function iniciar_sesion_segura(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('controlpsicologia_sesion');

    $usaHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $usaHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    $ahora = time();
    $ultimo = $_SESSION['ultimo_acceso'] ?? null;

    if (is_int($ultimo) && ($ahora - $ultimo) > INACTIVIDAD_MAXIMA) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['sesion_expirada'] = true;
        $_SESSION['ultimo_acceso'] = $ahora;
    }
}

function usuario_actual(): ?array {
    $usuario = $_SESSION['usuario'] ?? null;
    return is_array($usuario) ? $usuario : null;
}

function tiene_rol(string ...$roles): bool {
    $usuario = usuario_actual();
    return $usuario !== null && in_array($usuario['rol'] ?? '', $roles, true);
}

function exigir_login(): void {
    if (usuario_actual() !== null) {
        return;
    }
    if (solicitud_htmx()) {
        header('HX-Redirect: login.php');
        http_response_code(401);
    } else {
        header('Location: login.php', true, 303);
    }
    exit;
}

function exigir_rol(string ...$roles): void {
    exigir_login();
    if (!tiene_rol(...$roles)) {
        http_response_code(403);
        exit('No tenés permiso para realizar esta operación.');
    }
}

function cerrar_sesion(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $p['path'],
            'domain' => $p['domain'],
            'secure' => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}