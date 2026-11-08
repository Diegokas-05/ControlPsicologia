<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);

require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/funciones.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_rol('admin', 'consulta');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}

$titulo = 'Dashboard de Trabajo Operativo — ControlPsicología';
require $raiz . '/app/Views/flujo/dashboard.php';
