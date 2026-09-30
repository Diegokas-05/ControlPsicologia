<?php
declare(strict_types=1);

function token_csrf(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf'];
}

function campo_csrf(): string {
    return '<input type="hidden" name="csrf" value="' . 
           htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') . '">';
}

function validar_csrf(): void {
    $esperado = $_SESSION['csrf'] ?? '';
    $recibido = $_POST['csrf'] ?? '';

    if (!is_string($esperado) || !is_string($recibido) || $esperado === '' || !hash_equals($esperado, $recibido)) {
        http_response_code(403);
        exit('Solicitud rechazada. Recargá la página e intentá de nuevo.');
    }
}