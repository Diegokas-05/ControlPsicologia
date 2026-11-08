<?php
declare(strict_types=1);

const TIPOS_CITA = ['Individual', 'Pareja', 'Infantil', 'Familiar'];
const ESTADOS_CITA = ['Pendiente', 'Completada', 'Cancelada'];

function entrada_campos(array $datos, array $permitidos): void
{
    foreach (array_keys($datos) as $campo) {
        if (!in_array($campo, $permitidos, true)) {
            throw new InvalidArgumentException('Campo no permitido.');
        }
    }
}

function entrada_texto(array $datos, string $campo, int $min, int $max): string
{
    $valor = $datos[$campo] ?? '';
    if (!is_string($valor) || !mb_check_encoding($valor, 'UTF-8')) {
        throw new InvalidArgumentException('Se esperaba texto UTF-8.');
    }
    $valor = trim($valor);
    $longitud = mb_strlen($valor, 'UTF-8');
    if ($longitud < $min || $longitud > $max) {
        throw new InvalidArgumentException('Longitud no permitida.');
    }
    return $valor;
}

function entrada_opcion(array $datos, string $campo, array $opciones, bool $permiteVacio = false): string
{
    $valor = entrada_texto($datos, $campo, 0, 80);
    if ($permiteVacio && $valor === '') {
        return '';
    }
    if (!in_array($valor, $opciones, true)) {
        throw new InvalidArgumentException('Opcion no permitida.');
    }
    return $valor;
}

function entrada_id(array $datos, string $campo = 'id'): MongoDB\BSON\ObjectId
{
    $valor = entrada_texto($datos, $campo, 24, 24);
    if (!preg_match('/\A[a-f0-9]{24}\z/i', $valor)) {
        throw new InvalidArgumentException('Identificador invalido.');
    }
    return new MongoDB\BSON\ObjectId($valor);
}

function datos_cita_guia07(array $post, bool $edicion): array
{
    $permitidos = [
        'motivo', 'tipo', 'estado', 'csrf',
        'fecha_cita', 'psicologo', 'notas'
    ];
    if ($edicion) {
        $permitidos[] = 'id';
    }
    entrada_campos($post, $permitidos);

    $datos = [
        'motivo' => entrada_texto($post, 'motivo', 1, 100),
        'tipo'   => entrada_opcion($post, 'tipo', TIPOS_CITA),
        'estado' => entrada_opcion($post, 'estado', ESTADOS_CITA),
    ];

    if (isset($post['fecha_cita']) && is_string($post['fecha_cita'])) {
        $datos['fecha_cita'] = entrada_texto($post, 'fecha_cita', 0, 60);
    }
    if (isset($post['psicologo']) && is_string($post['psicologo'])) {
        $datos['psicologo'] = entrada_texto($post, 'psicologo', 0, 100);
    }
    if (isset($post['notas']) && is_string($post['notas'])) {
        $datos['notas'] = entrada_texto($post, 'notas', 0, 1000);
    }

    return $datos;
}
