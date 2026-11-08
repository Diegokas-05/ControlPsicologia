<?php
declare(strict_types=1);

final class IndicadoresController
{
    public function __construct(
        private IndicadoresModel $modelo,
        private ResumenGo $servicio
    ) {}

    public function mostrar(array $filtroBase = []): void
    {
        $grupos = $this->modelo->porFlujo($filtroBase);
        $resumen = $this->servicio->calcular($grupos);

        require dirname(__DIR__) . '/Views/indicadores/_resumen.php';
    }
}