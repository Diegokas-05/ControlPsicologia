<?php
declare(strict_types=1);

// 1. Filtro de seguridad del método
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Este listado se consulta mediante GET.');
}

// 2. Definir la ruta raíz del proyecto
$raiz = dirname(__DIR__);

try {
    // 3. Cargar las dependencias y clases
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

    // 4. Preparar la conexión a MongoDB y la colección de auditoría
    $db = new Database();
    $baseDatos = $db->conectar();
    $coleccion = $baseDatos->citas;

    // Declarar $auditoria global para que registrar_rechazo_entrada_guia07() pueda usarla
    global $auditoria;
    $auditoria = $baseDatos->bitacora; // O $baseDatos->auditoria según el nombre de tu colección

    // 5. Construir el flujo de dependencias (Inyección)
    $modelo = new CitaModel($coleccion);
    $controlador = new CitaController($modelo);
    
    // 6. Ejecutar la acción
    $controlador->index();

} catch (InvalidArgumentException $error) {
    // Captura específica de validación de la Guía 07 (Estado HTTP 422)
    registrar_rechazo_entrada_guia07('listado');
    http_response_code(422);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Datos invalidos. Revisa tipo, longitud y opciones.');
} catch (Throwable $error) {
    // Errores internos no controlados (Estado HTTP 500)
    error_log('FALLO_LISTADO_GUIA07: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo completar la consulta.');
}