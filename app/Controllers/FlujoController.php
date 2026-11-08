<?php
declare(strict_types=1);

final class FlujoController
{
    public function __construct(
        private FlujoModel $modelo,
        private array $base
    ) {}

    public function bandeja(): void
    {
        entrada_campos($_GET, ['estado']);
        $estado = entrada_opcion($_GET, 'estado', ['pendiente', 'en_proceso', 'finalizado'], true);
        $items = $this->modelo->listar($this->base, $estado);
        require dirname(__DIR__) . '/Views/flujo/_bandeja.php';
    }

    public function cambiar(): void
    {
        entrada_campos($_POST, ['id', 'accion', 'csrf', 'token_csrf', 'csrf_token', 'estado']);
        
        $id = entrada_id($_POST);
        $accion = entrada_opcion($_POST, 'accion', ['iniciar', 'finalizar']);
        
        $actual = usuario_actual();
        $actor = (string) ($actual['id'] ?? $actual['_id'] ?? $actual['nombre'] ?? 'admin');

        $this->modelo->cambiar($id, $accion, $actor, $this->base);

        header('HX-Trigger: flujoActualizado');
        echo '<p role="status">Cambio guardado correctamente.</p>';
    }

    public function comprobante(): void
    {
        entrada_campos($_GET, ['id']);
        $id = entrada_id($_GET);
        $item = $this->modelo->obtenerComprobante($id, $this->base);
        require dirname(__DIR__) . '/Views/flujo/comprobante.php';
    }
}
