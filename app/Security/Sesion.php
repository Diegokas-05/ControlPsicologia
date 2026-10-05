<?php
declare(strict_types=1);

const INACTIVIDAD_MAXIMA     = 1200;  // 20 min
//const INACTIVIDAD_MAXIMA     = 30;  // para la prueba es 30 
const DURACION_MAXIMA_SESION = 7200;  // 2 horas

function solicitud_htmx(): bool {
    return strtolower((string)($_SERVER['HTTP_HX_REQUEST'] ?? '')) === 'true';
}

function registrar_bitacora(string $evento, array $datos = []): void {
    static $coleccion = null;
    try {
        if ($coleccion === null) {
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
            require_once dirname(__DIR__, 2) . '/config/Database.php';
            $db = (new Database())->conectar();
            $coleccion = $db->bitacora;
        }

        $entrada = array_merge([
            'evento'     => $evento,
            'ip'         => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'desconocida'),
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120),
            'creado_en'  => new MongoDB\BSON\UTCDateTime(),
        ], $datos);

        $coleccion->insertOne($entrada);
    } catch (\Throwable $e) {
        error_log('Bitácora: ' . $e->getMessage());
    }
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
        'path'     => '/',
        'secure'   => $usaHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    $ahora  = time();
    $ultimo = $_SESSION['ultimo_acceso'] ?? null;
    $inicio = $_SESSION['inicio_sesion'] ?? null;

    // Timeout de inactividad
    if (is_int($ultimo) && ($ahora - $ultimo) > INACTIVIDAD_MAXIMA) {
        $actor = $_SESSION['usuario'] ?? null;
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['sesion_expirada'] = true;
        $_SESSION['ultimo_acceso']   = $ahora;

        registrar_bitacora('sesion.expirada', [
            'actor_id'     => (string)($actor['id']     ?? ''),
            'actor_correo' => (string)($actor['correo'] ?? ''),
            'resultado'    => 'denegado',
            'detalles'     => ['motivo' => 'inactividad'],
        ]);
        return;
    }

    // Timeout absoluto
    if (is_int($inicio) && ($ahora - $inicio) > DURACION_MAXIMA_SESION) {
        $actor = $_SESSION['usuario'] ?? null;
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['sesion_expirada'] = true;
        $_SESSION['ultimo_acceso']   = $ahora;

        registrar_bitacora('sesion.expirada', [
            'actor_id'     => (string)($actor['id']     ?? ''),
            'actor_correo' => (string)($actor['correo'] ?? ''),
            'resultado'    => 'denegado',
            'detalles'     => ['motivo' => 'duracion_maxima'],
        ]);
        return;
    }

    if (isset($_SESSION['usuario'])) {
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
        $actor = usuario_actual();
        registrar_bitacora('acceso.denegado', [
            'actor_id'     => (string)($actor['id']     ?? ''),
            'actor_correo' => (string)($actor['correo'] ?? ''),
            'actor_rol'    => (string)($actor['rol']    ?? ''),
            'resultado'    => 'denegado',
            'detalles'     => [
                'roles_requeridos' => array_values($roles),
                'ruta'             => $_SERVER['REQUEST_URI'] ?? '',
            ],
        ]);
        http_response_code(403);
        exit('No tenés permiso para realizar esta operación.');
    }
}

function cerrar_sesion(): void {
    $actor = usuario_actual();

    registrar_bitacora('logout', [
        'actor_id'     => (string)($actor['id']     ?? ''),
        'actor_correo' => (string)($actor['correo'] ?? ''),
        'actor_rol'    => (string)($actor['rol']    ?? ''),
        'resultado'    => 'permitido',
    ]);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}