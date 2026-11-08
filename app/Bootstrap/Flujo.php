<?php
declare(strict_types=1);

$raiz = dirname(__DIR__, 2);
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_rol('admin', 'consulta');

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

function crear_controlador_flujo(): FlujoController
{
    global $auditoria;
    $raiz = dirname(__DIR__, 2);
    
    // Cargar librerías de MongoDB a través de Composer
    require_once $raiz . '/vendor/autoload.php';

    // Conexión a BD
    require_once $raiz . '/config/Database.php';
    $db = new Database();
    $baseDatos = $db->conectar();
    $coleccion = $baseDatos->citas;
    $auditoria = $baseDatos->bitacora;

    require_once $raiz . '/funciones.php';
    require_once $raiz . '/app/Security/Entrada.php';
    require_once $raiz . '/app/Security/AuditoriaEntrada.php';
    require_once $raiz . '/app/Models/FlujoModel.php';
    require_once $raiz . '/app/Controllers/FlujoController.php';

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
    return new FlujoController(new FlujoModel($coleccion), $filtroBase);
}

function ejecutar_flujo(callable $tarea): void
{
    try {
        $tarea();
    } catch (InvalidArgumentException $error) {
        registrar_rechazo_entrada_guia07('flujo');
        http_response_code(422);
        echo '<p role="alert">Datos invalidos. Revisa la solicitud.</p>';
    } catch (OutOfBoundsException $error) {
        http_response_code(404);
        echo '<p role="alert">Registro no disponible.</p>';
    } catch (DomainException $error) {
        http_response_code(409);
        echo '<p role="alert">El estado cambio. Actualiza la bandeja.</p>';
    } catch (Throwable $error) {
        error_log('FLUJO_NO_DISPONIBLE: ' . $error->getMessage());
        http_response_code(503);
        echo '<p role="alert">No se pudo completar. Consulta el estado antes de reintentar.</p>';
    }
}
