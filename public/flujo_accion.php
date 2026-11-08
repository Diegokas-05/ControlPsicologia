<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Bootstrap/Flujo.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

ejecutar_flujo(function (): void {
    validar_csrf();
    crear_controlador_flujo()->cambiar();
});