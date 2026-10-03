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
}