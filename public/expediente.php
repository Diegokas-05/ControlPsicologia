<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);

require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/funciones.php';
require_once $raiz . '/app/models/Cita.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';
require_once $raiz . '/app/Security/Entrada.php';
require_once $raiz . '/app/Security/AuditoriaEntrada.php';
require_once $raiz . '/app/Controllers/ExpedienteController.php';

iniciar_sesion_segura();
exigir_login();

// Conexión y auditoría
$db = new Database();
$baseDatos = $db->conectar();
global $auditoria;
$auditoria = $baseDatos->bitacora;

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$controlador = new ExpedienteController(new Cita());

if ($metodo === 'GET') {
    try {
        $idMongo = entrada_id($_GET);
        $controlador->ver((string)$idMongo);
    } catch (InvalidArgumentException $e) {
        registrar_rechazo_entrada_guia07('expediente.ver');
        http_response_code(400);
        exit('Identificador de cita no válido.');
    }
} elseif ($metodo === 'POST') {
    exigir_rol('admin');
    try {
        $idMongo = entrada_id($_POST);
        $controlador->guardarNotas((string)$idMongo, $_POST);
    } catch (InvalidArgumentException $e) {
        registrar_rechazo_entrada_guia07('expediente.guardar');
        http_response_code(422);
        exit('Datos de evolución no válidos.');
    }
} else {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Método no permitido.');
}
