<?php
declare(strict_types=1);

final class PsicologoController
{
    public function __construct(
        private UsuarioModel $modelo
    ) {}

    public function index(?string $mensaje = null, ?string $error = null): void
    {
        $psicologos = $this->modelo->listarPsicologos();
        $total = count($psicologos);
        $activos = count(array_filter($psicologos, fn($p) => (bool)($p->activo ?? false)));
        $inactivos = $total - $activos;

        $titulo = 'Gestión del Equipo Clínico — ControlPsicología';
        require dirname(__DIR__) . '/Views/psicologos/index.php';
    }

    public function registrar(): void
    {
        validar_csrf();
        entrada_campos($_POST, ['csrf', 'nombre', 'correo', 'clave', 'especialidad']);

        $nombre = entrada_texto($_POST, 'nombre', 3, 100);
        $correo = entrada_texto($_POST, 'correo', 5, 120);
        $clave  = entrada_texto($_POST, 'clave', 6, 100);
        $especialidad = isset($_POST['especialidad']) ? entrada_texto($_POST, 'especialidad', 0, 100) : '';

        $this->modelo->crearPsicologo($nombre, $correo, $clave, $especialidad);

        $_SESSION['mensaje_psicologo'] = "Psicólogo(a) '{$nombre}' registrado exitosamente en el sistema.";
        header('Location: psicologos.php', true, 303);
        exit;
    }

    public function toggle(): void
    {
        validar_csrf();
        entrada_campos($_POST, ['csrf', 'id']);

        $id = entrada_id($_POST, 'id');
        $nuevoEstado = $this->modelo->toggleEstado($id);

        $estadoTexto = $nuevoEstado ? 'habilitada' : 'deshabilitada';
        $_SESSION['mensaje_psicologo'] = "La cuenta del terapeuta ha sido {$estadoTexto} correctamente.";
        header('Location: psicologos.php', true, 303);
        exit;
    }
}
