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

    // Valida que el ID recibido por POST sea un ObjectId válido
    $id = entrada_id($_POST);

    // Valida campos permitidos y opciones para edición
    $datos = datos_cita_guia07($_POST, true);

    $citaModel = new Cita();
    $resultado = $citaModel->actualizar((string)$id, $datos);

    if (!$resultado) {
        http_response_code(404);
        exit('Registro no encontrado.');
    }

    $_SESSION['mensaje_exito'] = "La cita fue actualizada exitosamente.";
    header('Location: mvc.php', true, 303);
    exit;

} catch (InvalidArgumentException $e) {
    registrar_rechazo_entrada_guia07('editar');
    http_response_code(422);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Datos invalidos. Revisa tipo, longitud y opciones.');
} catch (Throwable $e) {
    error_log('FALLO_ACTUALIZAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}