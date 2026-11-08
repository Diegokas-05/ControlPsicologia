<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);

require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/app/models/Cita.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';
require_once $raiz . '/app/Security/Entrada.php';
require_once $raiz . '/app/Security/AuditoriaEntrada.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

// Configurar auditoría global
$db = new Database();
$baseDatos = $db->conectar();
global $auditoria;
$auditoria = $baseDatos->bitacora;

try {
    validar_csrf();

    // Valida que solo vengan los campos permitidos y con sus tipos/opciones exactas
    $datos = datos_cita_guia07($_POST, false);

    $citaModel = new Cita();
    if ($citaModel->crear($datos)) {
        $_SESSION['mensaje_exito'] = "La cita clínica de '{$datos['motivo']}' fue registrada exitosamente.";
        header('Location: citas.php', true, 303);
        exit;
    }

    http_response_code(500);
    exit('Error al guardar en la base de datos.');

} catch (InvalidArgumentException $e) {
    registrar_rechazo_entrada_guia07('crear');
    $_SESSION['error_crear'] = 'Datos no válidos: verifica que el motivo tenga entre 1 y 100 caracteres y que las opciones sean correctas.';
    header('Location: crear.php', true, 303);
    exit;
} catch (Throwable $e) {
    error_log('FALLO_GUARDAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}
