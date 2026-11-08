<?php
declare(strict_types=1);

final class IndicadoresModel
{
    public function __construct(private MongoDB\Collection $coleccion) {}

    public function porTipo(array $filtroBase = []): array
    {
        $grupos = [];
        // Clasificación de tu entidad real
        $tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];

        foreach ($tiposPermitidos as $tipo) {
            $filtro = ['tipo' => $tipo];
            if ($filtroBase !== []) {
                $filtro = ['$and' => [$filtroBase, $filtro]];
            }
            $grupos[] = [
                'etiqueta' => $tipo,
                'cantidad' => $this->coleccion->countDocuments($filtro),
            ];
        }
        return $grupos;
    }

    // --- NUEVO MÉTODO AGREGADO PARA LA GUÍA 10 ---
    public function porFlujo(array $filtroBase = []): array
    {
        $opciones = [
            'Pendiente' => 'pendiente',
            'En proceso' => 'en_proceso',
            'Finalizado' => 'finalizado',
        ];

        $grupos = [];
        foreach ($opciones as $etiqueta => $valor) {
            $filtro = ['estado_flujo' => $valor];
            if ($filtroBase !== []) {
                $filtro = ['$and' => [$filtroBase, $filtro]];
            }
            $grupos[] = [
                'etiqueta' => $etiqueta,
                'cantidad' => $this->coleccion->countDocuments($filtro),
            ];
        }
        return $grupos;
    }
}