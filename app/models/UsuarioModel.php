<?php
declare(strict_types=1);

final class UsuarioModel
{
    public function __construct(
        private MongoDB\Collection $coleccion
    ) {}

    public function buscarActivoPorCorreo(string $correo): ?object
    {
        $documento = $this->coleccion->findOne([
            'correo_normalizado' => mb_strtolower(trim($correo), 'UTF-8'),
            'activo' => true,
        ]);

        return is_object($documento) ? $documento : null;
    }

    /**
     * Registra un intento fallido y devuelve el total actualizado.
     * Usamos $inc para que sea atómico y findOneAndUpdate para
     * obtener el valor real ya incrementado en una sola operación.
     */
    public function registrarFallo(MongoDB\BSON\ObjectId $id): int
    {
        $actualizado = $this->coleccion->findOneAndUpdate(
            ['_id' => $id],
            ['$inc' => ['intentos_fallidos' => 1]],
            [
                'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]
        );

        if (!is_array($actualizado)) {
            return 1;
        }

        return (int) ($actualizado['intentos_fallidos'] ?? 1);
    }

    /**
     * Bloquea la cuenta sumando minutos a la hora actual.
     */
    public function bloquearCuenta(MongoDB\BSON\ObjectId $id, int $minutos = 15): void
    {
        $milisegundos = (time() + ($minutos * 60)) * 1000;
        $desbloqueo   = new MongoDB\BSON\UTCDateTime($milisegundos);

        $this->coleccion->updateOne(
            ['_id' => $id],
            ['$set' => ['bloqueado_hasta' => $desbloqueo]]
        );
    }

    /**
     * Restaura los intentos a 0 y elimina el bloqueo al ingresar con éxito.
     */
    public function limpiarFallos(MongoDB\BSON\ObjectId $id): void
    {
        $this->coleccion->updateOne(
            ['_id' => $id],
            [
                '$unset' => ['bloqueado_hasta' => ''],
                '$set'   => ['intentos_fallidos' => 0],
            ]
        );
    }

        public function actualizarHash(MongoDB\BSON\ObjectId $id, string $nuevoHash): void
    {
        $this->coleccion->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'clave_hash'     => $nuevoHash,
                    'actualizado_en' => new MongoDB\BSON\UTCDateTime(),
                ],
            ]
        );
    }
}