<?php
declare(strict_types=1);

final class UsuarioModel
{
    private MongoDB\Collection $coleccion;

    public function __construct(MongoDB\Collection $coleccion)
    {
        $this->coleccion = $coleccion;
    }

    public function buscarActivoPorCorreo(string $correo): ?object
    {
        $documento = $this->coleccion->findOne([
            'correo_normalizado' => mb_strtolower(trim($correo), 'UTF-8'),
            'activo' => true,
        ]);
        
        return is_object($documento) ? $documento : null;
    }
}