<?php
declare(strict_types=1);

function registrar_rechazo_entrada_guia07(string $operacion): void
{
    global $auditoria;
    $usuario = usuario_actual();
    try {
        $auditoria->insertOne([
            'evento'       => 'entrada.rechazada',
            'actor_id'     => $usuario['id'] ?? null,
            'actor_rol'    => $usuario['rol'] ?? 'anonimo',
            'recurso_tipo' => 'cita',
            'recurso_id'   => null,
            'operacion'    => $operacion,
            'resultado'    => 'rechazado',
            'creado_en'    => new MongoDB\BSON\UTCDateTime(),
            'motivo'       => 'tipo_longitud_opcion_o_campo'
        ]);
    } catch (Throwable $error) {
        error_log('AUDITORIA_ENTRADA_NO_DISPONIBLE');
    }
}