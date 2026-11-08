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

// Configurar auditoría global para registrar_rechazo_entrada_guia07()
$db = new Database();
$baseDatos = $db->conectar();
global $auditoria;
$auditoria = $baseDatos->bitacora; // O $baseDatos->auditoria

try {
    validar_csrf();

    // 1. Validar que solo se envíen campos permitidos en la eliminación
    entrada_campos($_POST, ['id', 'csrf']);

    // 2. Validar que el ID sea estrictamente un ObjectId de 24 hex
    $id = entrada_id($_POST);

    $citaModel = new Cita();
    $resultado = $citaModel->eliminar((string)$id);

    if (!$resultado) {
        http_response_code(404);
        exit('Registro no encontrado.');
    }

    $_SESSION['mensaje_exito'] = "La cita fue eliminada.";
    header('Location: mvc.php', true, 303);
    exit;

} catch (InvalidArgumentException $e) {
    // Si envían un id=abc o campos no permitidos, responde 422 y audita
    registrar_rechazo_entrada_guia07('eliminar');
    http_response_code(422);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Identificador o datos no permitidos.');
} catch (Throwable $e) {
    error_log('FALLO_ELIMINAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}