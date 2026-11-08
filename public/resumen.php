<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz . '/app/Security/Sesion.php';

iniciar_sesion_segura();
exigir_rol('admin', 'consulta');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Metodo no permitido.');
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

try {
    require_once $raiz . '/vendor/autoload.php'; 
    require_once $raiz . '/config/Database.php';
    require_once $raiz . '/funciones.php';
    require_once $raiz . '/app/Security/Entrada.php'; 
    require_once $raiz . '/app/Services/ResumenGo.php';
    require_once $raiz . '/app/Models/IndicadoresModel.php';
    require_once $raiz . '/app/Controllers/IndicadoresController.php';

    $db = new Database();
    $coleccion = $db->conectar();

    $controlador = new IndicadoresController(
        new IndicadoresModel($coleccion->citas),
        new ResumenGo()
    );

    $usuario = usuario_actual();
    $filtroBase = [];
    if (($usuario['rol'] ?? '') === 'consulta') {
        $nombrePsicologo = (string)($usuario['nombre'] ?? '');
        $idPsicologo = (string)($usuario['id'] ?? '');
        $filtroBase = [
            '$or' => [
                ['psicologo' => $nombrePsicologo],
                ['psicologo' => $idPsicologo],
            ]
        ];
    }

    $controlador->mostrar($filtroBase);

} catch (Throwable $error) {
    error_log('RESUMEN_GO_NO_DISPONIBLE: ' . $error->getMessage());
    http_response_code(503);
    echo '<p role="alert" style="color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 5px;">Resumen no disponible. Intenta nuevamente.</p>';
}
