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
    require_once $raiz . '/app/Models/CitaModel.php';
    require_once $raiz . '/app/Controllers/CitaController.php';
    require_once $raiz.'/app/Security/Sesion.php';
    require_once $raiz.'/app/Security/Csrf.php';
    iniciar_sesion_segura();
    exigir_login();

    // 4. Preparar la conexión a MongoDB (usando tu clase Database)
    $db = new Database();
    $baseDatos = $db->conectar();
    $coleccion = $baseDatos->citas;

    // 5. Construir el flujo de dependencias (Inyección)
    $modelo = new CitaModel($coleccion);
    $controlador = new CitaController($modelo);
    
    // 6. Ejecutar la acción
    $controlador->index();

} catch (Throwable $error) {
    // 7. Manejo de errores fatales
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo cargar el listado. Revisá la terminal.');
}