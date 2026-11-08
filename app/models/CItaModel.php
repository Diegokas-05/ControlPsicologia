<?php
declare(strict_types=1);

final class CitaModel {
    
    private MongoDB\Collection $coleccion;

    public function __construct(MongoDB\Collection $coleccion) {
        $this->coleccion = $coleccion;
    }

    public function listar(array $filtro = [], string $busqueda = ''): array {
        if ($busqueda !== '') {
            $condicionBusqueda = [
                'motivo' => new MongoDB\BSON\Regex(preg_quote($busqueda), 'i')
            ];
            if (isset($filtro['$and'])) {
                $filtro['$and'][] = $condicionBusqueda;
            } elseif ($filtro !== []) {
                $filtro = ['$and' => [$filtro, $condicionBusqueda]];
            } else {
                $filtro = $condicionBusqueda;
            }
        }
        
        return $this->coleccion->find(
            $filtro,
            ['sort' => ['_id' => -1]]
        )->toArray();
    }
}
