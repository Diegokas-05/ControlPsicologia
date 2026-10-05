<?php
declare(strict_types=1);

final class AuthController
{
    private const MAX_INTENTOS    = 3;
    private const MINUTOS_BLOQUEO = 15; # el tiempo que se desativa la cuenta para iniciar session

    private UsuarioModel  $usuarios;
    private BitacoraModel $bitacora;

    public function __construct(UsuarioModel $usuarios, BitacoraModel $bitacora)
    {
        $this->usuarios = $usuarios;
        $this->bitacora = $bitacora;
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
        $clave  = (string)($_POST['clave'] ?? '');

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
            $this->bitacora->registrar('login.formato_invalido', [
                'correo_intentado' => $correo,
                'resultado'        => 'denegado',
            ]);
            $this->formulario('Credenciales inválidas.');
            return;
        }

        $usuario = $this->usuarios->buscarActivoPorCorreo($correo);

        // ─── 1. Control de bloqueo ────────────────────────────────────────
        if ($usuario !== null && isset($usuario->bloqueado_hasta)) {
            $tiempoDesbloqueo = $usuario->bloqueado_hasta->toDateTime()->getTimestamp();
            $tiempoRestante   = $tiempoDesbloqueo - time();

            if ($tiempoRestante > 0) {
                $minutos = (int) ceil($tiempoRestante / 60);
                $this->bitacora->registrar('login.cuenta_bloqueada', [
                    'actor_id'     => (string) $usuario->_id,
                    'actor_correo' => $correo,
                    'resultado'    => 'denegado',
                    'detalles'     => ['minutos_restantes' => $minutos],
                ]);
                $this->formulario("Demasiados intentos fallidos. Cuenta bloqueada por seguridad. Espere {$minutos} minuto(s).");
                return;
            }

            $this->usuarios->limpiarFallos($usuario->_id);
            $usuario->intentos_fallidos = 0;
            unset($usuario->bloqueado_hasta);
        }

        // ─── 2. Verificación de contraseña ────────────────────────────────
        $hashFicticio = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $hash         = $usuario !== null ? (string) ($usuario->clave_hash ?? '') : $hashFicticio;
        $claveValida  = password_verify($clave, $hash);

        // ─── 3. Credenciales incorrectas ──────────────────────────────────
        if ($usuario === null || !$claveValida) {
            if ($usuario !== null) {
                $nuevosIntentos = $this->usuarios->registrarFallo($usuario->_id);

                $this->bitacora->registrar('login.fallido', [
                    'actor_id'     => (string) $usuario->_id,
                    'actor_correo' => $correo,
                    'resultado'    => 'denegado',
                    'detalles'     => ['intentos' => $nuevosIntentos],
                ]);

                if ($nuevosIntentos >= self::MAX_INTENTOS) {
                    $this->usuarios->bloquearCuenta($usuario->_id, self::MINUTOS_BLOQUEO);

                    $this->bitacora->registrar('login.bloqueado', [
                        'actor_id'     => (string) $usuario->_id,
                        'actor_correo' => $correo,
                        'resultado'    => 'denegado',
                        'detalles'     => ['minutos' => self::MINUTOS_BLOQUEO],
                    ]);

                    $this->formulario('Demasiados intentos fallidos. Cuenta bloqueada por ' . self::MINUTOS_BLOQUEO . ' minutos.');
                    return;
                }

                $restantes = self::MAX_INTENTOS - $nuevosIntentos;
                $this->formulario("Credenciales inválidas. Le quedan {$restantes} intento(s).");
                return;
            }

            $this->bitacora->registrar('login.usuario_inexistente', [
                'correo_intentado' => $correo,
                'resultado'        => 'denegado',
            ]);

            $this->formulario('Credenciales inválidas.');
            return;
        }

                // ─── 4. Login exitoso ─────────────────────────────────────────────
        $this->usuarios->limpiarFallos($usuario->_id);

        // Rehash si el algoritmo cambió
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $nuevoHash = password_hash($clave, PASSWORD_DEFAULT);
            $this->usuarios->actualizarHash($usuario->_id, $nuevoHash);
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (string) $usuario->_id,
            'nombre' => (string) $usuario->nombre,
            'correo' => (string) $usuario->correo,
            'rol'    => (string) $usuario->rol,
        ];
        $_SESSION['ultimo_acceso'] = time();
        $_SESSION['inicio_sesion'] = time();

        $this->bitacora->registrar('login.exitoso', [
            'actor_id'     => (string) $usuario->_id,
            'actor_correo' => $correo,
            'actor_rol'    => (string) $usuario->rol,
            'resultado'    => 'permitido',
        ]);

        header('Location: mvc.php', true, 303);
        exit;
}
}