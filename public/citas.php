<?php
declare(strict_types=1);

// 1. Filtro de seguridad del método HTTP
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}

$raiz = dirname(__DIR__);

try {
    // 2. Cargar dependencias y clases
    require_once $raiz . '/vendor/autoload.php'; 
    require_once $raiz . '/config/Database.php';
    require_once $raiz . '/funciones.php';
    require_once $raiz . '/app/Models/CitaModel.php';
    require_once $raiz . '/app/Controllers/CitaController.php';
    require_once $raiz . '/app/Security/Sesion.php';
    require_once $raiz . '/app/Security/Csrf.php';
    require_once $raiz . '/app/Security/Entrada.php';
    require_once $raiz . '/app/Security/AuditoriaEntrada.php';

    iniciar_sesion_segura();
    exigir_login();

    // 3. Conexión a MongoDB
    $db = new Database();
    $baseDatos = $db->conectar();
    $coleccion = $baseDatos->citas;

    global $auditoria;
    $auditoria = $baseDatos->bitacora;

    // 4. Inyección de dependencias y ejecución
    $modelo = new CitaModel($coleccion);
    $controlador = new CitaController($modelo);
    $controlador->index();

} catch (InvalidArgumentException $error) {
    registrar_rechazo_entrada_guia07('listado');
    http_response_code(422);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Datos inválidos. Revisa tipo, longitud y opciones.');
} catch (Throwable $error) {
    error_log('FALLO_LISTADO_CITAS: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo completar la consulta de citas.');
}
