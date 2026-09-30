<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz.'/app/Security/Sesion.php';
require_once $raiz.'/app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_login();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('El cierre de sesión requiere POST.');
}

validar_csrf();
cerrar_sesion();

header('Location: login.php', true, 303);
exit;