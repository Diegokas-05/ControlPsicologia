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

function validar_motivo_clinico(string $valor): string
{
    $valor = trim($valor);
    $longitud = mb_strlen($valor, 'UTF-8');
    if ($longitud < 5 || $longitud > 100) {
        throw new InvalidArgumentException('El motivo debe tener entre 5 y 100 caracteres.');
    }
    if (preg_match('/^\d+$/', $valor)) {
        throw new InvalidArgumentException('El motivo no puede ser únicamente numérico.');
    }
    if (!preg_match('/[\p{L}]{3,}/u', $valor)) {
        throw new InvalidArgumentException('El motivo debe contener un nombre o descripción clínica válida (mínimo 3 letras).');
    }
    return $valor;
}

function validar_fecha_futura(?string $fecha): ?string
{
    if ($fecha === null || trim($fecha) === '') {
        return null;
    }
    $fecha = trim($fecha);
    $tiempo = strtotime($fecha);
    if ($tiempo === false) {
        throw new InvalidArgumentException('Formato de fecha u hora inválido.');
    }
    // Margen de 60 segundos de tolerancia por latencia en envío
    if ($tiempo < (time() - 60)) {
        throw new InvalidArgumentException('La fecha y hora de la cita no puede ser en el pasado.');
    }
    return $fecha;
}

function datos_cita_guia07(array $post, bool $edicion, ?string $fechaPrevia = null): array
{
    $permitidos = [
        'motivo', 'tipo', 'estado', 'csrf',
        'fecha_cita', 'psicologo', 'notas'
    ];
    if ($edicion) {
        $permitidos[] = 'id';
    }
    entrada_campos($post, $permitidos);

    $motivoTexto = entrada_texto($post, 'motivo', 1, 100);
    $motivoValidado = validar_motivo_clinico($motivoTexto);

    $datos = [
        'motivo' => $motivoValidado,
        'tipo'   => entrada_opcion($post, 'tipo', TIPOS_CITA),
        'estado' => entrada_opcion($post, 'estado', ESTADOS_CITA),
    ];

    if (isset($post['fecha_cita']) && is_string($post['fecha_cita']) && trim($post['fecha_cita']) !== '') {
        $fechaEnviada = trim($post['fecha_cita']);
        if ($edicion && $fechaPrevia !== null && $fechaEnviada === trim($fechaPrevia)) {
            $datos['fecha_cita'] = $fechaEnviada;
        } else {
            $datos['fecha_cita'] = validar_fecha_futura($fechaEnviada);
        }
    } else {
        $datos['fecha_cita'] = '';
    }

    if (isset($post['psicologo']) && is_string($post['psicologo'])) {
        $datos['psicologo'] = entrada_texto($post, 'psicologo', 0, 100);
    }
    if (isset($post['notas']) && is_string($post['notas'])) {
        $datos['notas'] = entrada_texto($post, 'notas', 0, 1000);
    }

    return $datos;
}
