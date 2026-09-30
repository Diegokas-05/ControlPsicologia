<?php
declare(strict_types=1);

final class AuthController
{
    private UsuarioModel $usuarios;

    public function __construct(UsuarioModel $usuarios)
    {
        $this->usuarios = $usuarios;
    }

    public function formulario(?string $error = null): void
    {
        $expirada = (bool) ($_SESSION['sesion_expirada'] ?? false);
        unset($_SESSION['sesion_expirada']);
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    public function ingresar(): void
    {
        validar_csrf();

        $correo = mb_strtolower(trim((string)($_POST['correo'] ?? '')), 'UTF-8');
        $clave = (string)($_POST['clave'] ?? '');

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
            $this->formulario('Credenciales inválidas.');
            return;
        }

        $usuario = $this->usuarios->buscarActivoPorCorreo($correo);
        
        // Hash ficticio para igualar el tiempo de respuesta si el usuario no existe
        $hashFicticio = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $hash = $usuario !== null ? (string) ($usuario->clave_hash ?? '') : $hashFicticio;
        
        $claveValida = password_verify($clave, $hash);

        // Mensaje genérico para no revelar si el correo existe en la base de datos
        if ($usuario === null || !$claveValida) {
            $this->formulario('Credenciales inválidas.');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (string)$usuario->_id,
            'nombre' => (string)$usuario->nombre,
            'correo' => (string)$usuario->correo,
            'rol' => (string)$usuario->rol,
        ];
        $_SESSION['ultimo_acceso'] = time();
        
        header('Location: mvc.php', true, 303);
        exit;
    }
}