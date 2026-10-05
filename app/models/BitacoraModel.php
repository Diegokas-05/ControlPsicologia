<?php
declare(strict_types=1);

final class BitacoraModel
{
    public function __construct(
        private MongoDB\Collection $coleccion
    ) {}

    public function registrar(
        string $evento,
        array $datos = []
    ): void {
        $entrada = array_merge([
            'evento'     => $evento,
            'ip'         => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'desconocida'),
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120),
            'creado_en'  => new MongoDB\BSON\UTCDateTime(),
        ], $datos);

        $this->coleccion->insertOne($entrada);
    }
}