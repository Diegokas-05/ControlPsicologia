<?php
declare(strict_types=1);

/**
 * Función centralizada de escape seguro para texto y atributos HTML.
 * Evita inyecciones XSS convirtiendo caracteres especiales en entidades HTML.
 */
function e(mixed $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}