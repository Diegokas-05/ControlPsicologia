<?php
declare(strict_types=1);

final class FlujoModel
{
    public function __construct(private MongoDB\Collection $coleccion) {}

    private function filtro(array $base, array $condicion): array
    {
        return $base === [] ? $condicion : ['$and' => [$base, $condicion]];
    }

    public function listar(array $base, string $estado): array
    {
        $condicion = ['estado_flujo' => ['$in' => ['pendiente', 'en_proceso', 'finalizado']]];
        if ($estado !== '') {
            $condicion['estado_flujo'] = $estado;
        }
        return $this->coleccion->find(
            $this->filtro($base, $condicion),
            ['sort' => ['_id' => -1], 'limit' => 50]
        )->toArray();
    }

    public function cambiar(
        MongoDB\BSON\ObjectId $id, string $accion,
        string $actor, array $base
    ): void {
        $pasos = [
            'iniciar'   => ['pendiente', 'en_proceso'],
            'finalizar' => ['en_proceso', 'finalizado'],
        ];

        if (!isset($pasos[$accion]) || $actor === '') {
            throw new InvalidArgumentException('Accion o actor no valido.');
        }

        [$desde, $hacia] = $pasos[$accion];
        $fecha = new MongoDB\BSON\UTCDateTime();

        $resultado = $this->coleccion->updateOne(
            $this->filtro($base, ['_id' => $id, 'estado_flujo' => $desde]),
            [
                '$set'  => ['estado_flujo' => $hacia, 'actualizado_en' => $fecha],
                '$push' => ['historial_flujo' => [
                    'desde'    => $desde,
                    'hacia'    => $hacia,
                    'actor_id' => $actor,
                    'fecha'    => $fecha,
                ]],
            ]
        );

        if ($resultado->getMatchedCount() === 1) {
            return;
        }

        $actual = $this->coleccion->findOne(
            $this->filtro($base, ['_id' => $id]),
            ['projection' => ['_id' => 1]]
        );

        if ($actual === null) {
            throw new OutOfBoundsException('Registro no disponible.');
        }

        throw new DomainException('El estado cambio. Actualiza la bandeja.');
    }
}