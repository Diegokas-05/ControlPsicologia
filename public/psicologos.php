<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);

require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/funciones.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';
require_once $raiz . '/app/Security/Entrada.php';
require_once $raiz . '/app/Security/AuditoriaEntrada.php';
require_once $raiz . '/app/Models/UsuarioModel.php';
require_once $raiz . '/app/Controllers/PsicologoController.php';

iniciar_sesion_segura();
exigir_rol('admin');

$db = new Database();
$baseDatos = $db->conectar();
$coleccionUsuarios = $baseDatos->usuarios;

global $auditoria;
$auditoria = $baseDatos->bitacora;

$modelo = new UsuarioModel($coleccionUsuarios);
$controlador = new PsicologoController($modelo);

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accion = (string)($_GET['accion'] ?? 'index');

if ($metodo === 'GET') {
    $controlador->index();
    exit;
}

if ($metodo === 'POST') {
    try {
        if ($accion === 'crear') {
            $controlador->registrar();
        } elseif ($accion === 'toggle') {
            $controlador->toggle();
        } else {
            http_response_code(400);
            exit('Acción no reconocida.');
        }
    } catch (InvalidArgumentException $e) {
        registrar_rechazo_entrada_guia07('psicologos');
        $_SESSION['error_psicologo'] = $e->getMessage();
        header('Location: psicologos.php', true, 303);
        exit;
    } catch (OutOfBoundsException $e) {
        $_SESSION['error_psicologo'] = $e->getMessage();
        header('Location: psicologos.php', true, 303);
        exit;
    } catch (Throwable $e) {
        error_log('FALLO_PSICOLOGOS: ' . $e->getMessage());
        http_response_code(500);
        exit('Error interno al procesar la gestión de psicólogos.');
    }
}

header('Allow: GET, POST');
http_response_code(405);
exit('Método no permitido.');
