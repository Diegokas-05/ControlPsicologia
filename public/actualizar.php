<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);

require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/app/models/Cita.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';
require_once $raiz . '/app/Security/Entrada.php';
require_once $raiz . '/app/Security/AuditoriaEntrada.php';
require_once $raiz . '/funciones.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

// Configurar auditoría global
$db = new Database();
$baseDatos = $db->conectar();
global $auditoria;
$auditoria = $baseDatos->bitacora;

try {
    validar_csrf();

    // 1. Validar ID
    $id = entrada_id($_POST);

    // 2. Leer la fecha previa (enviada como campo hidden) para no rechazarla si no cambió
    $fechaPrevia = isset($_POST['fecha_previa']) && is_string($_POST['fecha_previa'])
        ? trim($_POST['fecha_previa'])
        : null;

    // Añadir el campo fecha_previa a la lista de permitidos
    $camposPermitidos = ['csrf', 'id', 'fecha_previa', 'motivo', 'tipo', 'estado', 'fecha_cita', 'psicologo', 'notas'];
    entrada_campos($_POST, $camposPermitidos);

    // 3. Validar motivo (reutilizamos las funciones de Entrada.php)
    $motivoTexto   = entrada_texto($_POST, 'motivo', 1, 100);
    $motivoNuevo   = validar_motivo_clinico($motivoTexto);
    $tipoNuevo     = entrada_opcion($_POST, 'tipo',   TIPOS_CITA);
    $estadoNuevo   = entrada_opcion($_POST, 'estado', ESTADOS_CITA);
    $psicologoNuevo = isset($_POST['psicologo']) ? entrada_texto($_POST, 'psicologo', 0, 100) : '';
    $notasNuevo    = isset($_POST['notas'])      ? entrada_texto($_POST, 'notas',     0, 1000) : '';

    // 4. Validar fecha: si es distinta a la previa, debe ser futura
    $fechaNueva = '';
    if (isset($_POST['fecha_cita']) && is_string($_POST['fecha_cita']) && trim($_POST['fecha_cita']) !== '') {
        $fechaEnviada = trim($_POST['fecha_cita']);
        if ($fechaPrevia !== null && $fechaEnviada === $fechaPrevia) {
            // Sin cambio de fecha — se conserva sin validar contra el pasado
            $fechaNueva = $fechaEnviada;
        } else {
            // Fecha diferente: debe ser futura
            $fechaNueva = validar_fecha_futura($fechaEnviada) ?? '';
        }
    }

    // 5. Obtener cita actual para detectar qué cambió (historial)
    $citaModel  = new Cita();
    $citaActual = $citaModel->obtenerPorId((string)$id);
    if (!$citaActual) {
        http_response_code(404);
        exit('Registro no encontrado.');
    }

    // ── Bloqueo de seguridad en servidor: citas finalizadas no se pueden editar ──
    if (($citaActual->estado_flujo ?? '') === 'finalizado') {
        http_response_code(403);
        exit('Esta cita ya fue finalizada y no puede modificarse.');
    }

    $actor = usuario_actual()['nombre'] ?? 'Admin';

    // Detectar cambios para registrar historial
    $cambios = [];

    if ($motivoNuevo !== (string)($citaActual->motivo ?? '')) {
        $cambios[] = [
            'campo'    => 'motivo',
            'anterior' => (string)($citaActual->motivo ?? ''),
            'nuevo'    => $motivoNuevo,
            'actor'    => $actor,
        ];
    }
    if ($tipoNuevo !== (string)($citaActual->tipo ?? '')) {
        $cambios[] = [
            'campo'    => 'tipo',
            'anterior' => (string)($citaActual->tipo ?? ''),
            'nuevo'    => $tipoNuevo,
            'actor'    => $actor,
        ];
    }
    if ($estadoNuevo !== (string)($citaActual->estado ?? '')) {
        $cambios[] = [
            'campo'    => 'estado',
            'anterior' => (string)($citaActual->estado ?? ''),
            'nuevo'    => $estadoNuevo,
            'actor'    => $actor,
        ];
    }
    if ($psicologoNuevo !== (string)($citaActual->psicologo ?? '') && $psicologoNuevo !== '') {
        $cambios[] = [
            'campo'    => 'psicologo',
            'anterior' => (string)($citaActual->psicologo ?? 'Sin asignar'),
            'nuevo'    => $psicologoNuevo,
            'actor'    => $actor,
        ];
    }
    if ($fechaNueva !== '' && $fechaNueva !== (string)($citaActual->fecha_cita ?? '')) {
        $cambios[] = [
            'campo'    => 'fecha_cita',
            'anterior' => (string)($citaActual->fecha_cita ?? 'Sin fecha'),
            'nuevo'    => $fechaNueva,
            'actor'    => $actor,
        ];
    }

    // 6. Construir array de datos para el modelo
    $datos = [
        'motivo'  => $motivoNuevo,
        'tipo'    => $tipoNuevo,
        'estado'  => $estadoNuevo,
    ];
    if ($fechaNueva !== '') {
        $datos['fecha_cita'] = $fechaNueva;
    }
    if ($psicologoNuevo !== '') {
        $datos['psicologo'] = $psicologoNuevo;
    }
    $datos['notas'] = $notasNuevo;

    // 7. Persistir en MongoDB
    $resultado = $citaModel->actualizar((string)$id, $datos, $cambios);

    if (!$resultado) {
        http_response_code(500);
        exit('Error al actualizar el registro en la base de datos.');
    }

    $totalCambios = count($cambios);
    $msg = $totalCambios > 0
        ? "Cita actualizada exitosamente con {$totalCambios} cambio(s) registrado(s) en el historial."
        : "Cita revisada. No se detectaron cambios en los datos.";

    $_SESSION['mensaje_exito'] = $msg;
    header('Location: citas.php', true, 303);
    exit;

} catch (InvalidArgumentException $e) {
    registrar_rechazo_entrada_guia07('editar');
    // Redirigir de vuelta con el error visible (no texto plano)
    $idRaw = isset($_POST['id']) ? trim((string)$_POST['id']) : '';
    $_SESSION['error_editar'] = $e->getMessage();
    $destino = (preg_match('/\A[a-f0-9]{24}\z/i', $idRaw))
        ? 'editar.php?id=' . rawurlencode($idRaw)
        : 'citas.php';
    header('Location: ' . $destino, true, 303);
    exit;

} catch (Throwable $e) {
    error_log('FALLO_ACTUALIZAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}
