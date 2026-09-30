<?php
declare(strict_types=1);

final class CitaModel {
    
    private MongoDB\Collection $coleccion;

    public function __construct(MongoDB\Collection $coleccion) {
        $this->coleccion = $coleccion;
    }

    public function listar(array $filtro = [], string $busqueda = ''): array {
        if ($busqueda !== '') {
            $filtro['motivo'] = new MongoDB\BSON\Regex(
                preg_quote($busqueda),
            );
        }
        
        return $this->coleccion->find(
            $filtro,
            ['sort' => ['motivo' => 1, '_id' => 1]]
        )->toArray();
    }
}