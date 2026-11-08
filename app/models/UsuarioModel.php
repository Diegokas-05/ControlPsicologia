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

    /**
     * Devuelve la lista completa de psicólogos (rol consulta) para administración.
     */
    public function listarPsicologos(): array
    {
        return $this->coleccion->find(
            ['rol' => 'consulta'],
            ['sort' => ['nombre' => 1, '_id' => -1]]
        )->toArray();
    }

    /**
     * Devuelve psicólogos activos para asignación en citas clínicas.
     */
    public function listarPsicologosActivos(): array
    {
        return $this->coleccion->find(
            ['rol' => 'consulta', 'activo' => true],
            ['sort' => ['nombre' => 1]]
        )->toArray();
    }

    /**
     * Registra un nuevo psicólogo con rol 'consulta' y credenciales seguras.
     */
    public function crearPsicologo(string $nombre, string $correo, string $clave, string $especialidad = ''): MongoDB\BSON\ObjectId
    {
        $nombre = trim($nombre);
        $correo = trim($correo);
        $clave  = trim($clave);

        if (mb_strlen($nombre, 'UTF-8') < 3) {
            throw new InvalidArgumentException('El nombre del psicólogo debe tener al menos 3 caracteres.');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico proporcionado no es válido.');
        }

        if (mb_strlen($clave, 'UTF-8') < 6) {
            throw new InvalidArgumentException('La contraseña provisional debe tener un mínimo de 6 caracteres.');
        }

        $correoNormalizado = mb_strtolower($correo, 'UTF-8');
        $existente = $this->coleccion->findOne(['correo_normalizado' => $correoNormalizado]);
        if ($existente !== null) {
            throw new InvalidArgumentException('Ya existe un usuario registrado con este correo electrónico.');
        }

        $documento = [
            'nombre'             => $nombre,
            'correo'             => $correo,
            'correo_normalizado' => $correoNormalizado,
            'clave_hash'         => password_hash($clave, PASSWORD_DEFAULT),
            'rol'                => 'consulta',
            'activo'             => true,
            'intentos_fallidos'  => 0,
            'especialidad'       => trim($especialidad),
            'creado_en'          => new MongoDB\BSON\UTCDateTime(),
            'actualizado_en'     => new MongoDB\BSON\UTCDateTime(),
        ];

        $resultado = $this->coleccion->insertOne($documento);
        return $resultado->getInsertedId();
    }

    /**
     * Alterna el estado activo/inactivo de un psicólogo.
     */
    public function toggleEstado(MongoDB\BSON\ObjectId $id): bool
    {
        $usuario = $this->coleccion->findOne(['_id' => $id, 'rol' => 'consulta']);
        if ($usuario === null) {
            throw new OutOfBoundsException('El psicólogo solicitado no existe en la base de datos.');
        }

        $nuevoEstado = !((bool)($usuario->activo ?? false));

        $this->coleccion->updateOne(
            ['_id' => $id],
            [
                '$set' => [
                    'activo'         => $nuevoEstado,
                    'actualizado_en' => new MongoDB\BSON\UTCDateTime(),
                ]
            ]
        );

        return $nuevoEstado;
    }

    public function obtenerPorId(MongoDB\BSON\ObjectId $id): ?object
    {
        return $this->coleccion->findOne(['_id' => $id]);
    }
}
