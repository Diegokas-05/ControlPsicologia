<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz.'/vendor/autoload.php';
require_once $raiz.'/config/Database.php';
require_once $raiz.'/app/Security/Sesion.php';
require_once $raiz.'/app/Security/Csrf.php';
require_once $raiz.'/app/Models/UsuarioModel.php';
require_once $raiz.'/app/Controllers/AuthController.php';

iniciar_sesion_segura();

// Conectar a MongoDB
$db = new Database();
$baseDatos = $db->conectar();
$usuarios = $baseDatos->usuarios;

$controlador = new AuthController(new UsuarioModel($usuarios));
$metodo = $_SERVER['REQUEST_METHOD'] ?? '';

// Si entra normal (GET), mostrar formulario o redirigir si ya ingresó
if ($metodo === 'GET') {
    if (usuario_actual() !== null) {
        header('Location: mvc.php', true, 303);
        exit;
    }
    $controlador->formulario();
    exit;
}

// Si envía el formulario (POST), procesar datos
if ($metodo === 'POST') {
    $controlador->ingresar();
    exit;
}

header('Allow: GET, POST');
http_response_code(405);
exit('Método no permitido.');