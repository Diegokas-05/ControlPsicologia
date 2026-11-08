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
require_once $raiz . '/app/Models/UsuarioModel.php';
require_once $raiz . '/funciones.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}

// Configurar auditoría global
$db = new Database();
$baseDatos = $db->conectar();
global $auditoria;
$auditoria = $baseDatos->bitacora;

// Cargar psicólogos activos del sistema
$usuarioModel = new UsuarioModel($baseDatos->usuarios);
$psicologosActivos = $usuarioModel->listarPsicologosActivos();

// Leer mensaje de error de sesión (si viene de un fallo en actualizar.php)
$error = $_SESSION['error_editar'] ?? null;
unset($_SESSION['error_editar']);

try {
    $id = entrada_id($_GET);

    $citaModel = new Cita();
    $cita = $citaModel->obtenerPorId((string)$id);

    if (!$cita) {
        http_response_code(404);
        exit('Registro clínico no encontrado.');
    }

    // ── Bloqueo: citas finalizadas no se pueden editar ──
    if (($cita->estado_flujo ?? '') === 'finalizado') {
        $_SESSION['mensaje_info'] = 'Esta cita ya fue finalizada en el flujo operativo y no puede modificarse. Solo es posible consultar su expediente o constancia de cierre.';
        header('Location: citas.php', true, 303);
        exit;
    }

} catch (InvalidArgumentException $e) {
    registrar_rechazo_entrada_guia07('editar');
    http_response_code(422);
    exit('Identificador de cita inválido.');
} catch (Throwable $e) {
    error_log('FALLO_EDITAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}

// Extraer campos actuales del documento para pre-rellenar el formulario
$citaId       = (string)($cita->_id ?? '');
$motivoActual = (string)($cita->motivo ?? '');
$tipoActual   = (string)($cita->tipo ?? 'Individual');
$estadoActual = (string)($cita->estado ?? 'Pendiente');
$psicActual   = (string)($cita->psicologo ?? '');
$fechaActual  = (string)($cita->fecha_cita ?? '');
$notasActual  = (string)($cita->notas ?? '');
$estadoFlujo  = (string)($cita->estado_flujo ?? 'pendiente');
$creado       = $cita->fecha_creacion ?? null;
$actualizado  = $cita->actualizado_en ?? null;

// Formatear fecha al formato requerido por datetime-local (HTML5)
$fechaHtmlInput = '';
if ($fechaActual !== '') {
    $ts = strtotime($fechaActual);
    if ($ts !== false) {
        $fechaHtmlInput = date('Y-m-d\TH:i', $ts);
    }
}

// Fecha de creación legible
$creadoTexto = '';
if ($creado instanceof MongoDB\BSON\UTCDateTime) {
    $creadoTexto = $creado->toDateTime()->setTimezone(new DateTimeZone('America/Mexico_City'))->format('d/m/Y H:i');
}
$actualizadoTexto = '';
if ($actualizado instanceof MongoDB\BSON\UTCDateTime) {
    $actualizadoTexto = $actualizado->toDateTime()->setTimezone(new DateTimeZone('America/Mexico_City'))->format('d/m/Y H:i');
}

// Historial de cambios
$historialCambios = [];
if (isset($cita->historial_cambios)) {
    foreach ($cita->historial_cambios as $cambio) {
        $historialCambios[] = $cambio;
    }
}

// Mapa de slugs de estado de flujo para badge
$badgeFlujo = match ($estadoFlujo) {
    'en_proceso' => ['clase' => 'badge-estado-en-proceso', 'texto' => 'En proceso'],
    'finalizado' => ['clase' => 'badge-estado-finalizado', 'texto' => 'Finalizado'],
    default      => ['clase' => 'badge-estado-pendiente',  'texto' => 'Pendiente'],
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Cita — ControlPsicología</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require $raiz . '/app/Views/layouts/header.php'; ?>

    <main class="dashboard-contenedor">

        <!-- ── Hero del Formulario ── -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <h1>&#9998; Editar Cita Clínica</h1>
                <p>Modificación de datos, reasignación de terapeuta y reprogramación de agenda.</p>
            </div>
            <a href="citas.php" class="btn btn-secondary">&larr; Volver a Citas</a>
        </div>

        <!-- ── Ficha de identidad de la cita (sólo lectura) ── -->
        <div class="card editar-ficha-id">
            <div class="ficha-id-grid">
                <div class="ficha-id-item">
                    <span class="ficha-id-etiqueta">ID del Expediente</span>
                    <code class="ficha-id-valor"><?= e($citaId) ?></code>
                </div>
                <div class="ficha-id-item">
                    <span class="ficha-id-etiqueta">Estado en el Flujo</span>
                    <span class="badge <?= e($badgeFlujo['clase']) ?>"><?= e($badgeFlujo['texto']) ?></span>
                </div>
                <?php if ($creadoTexto !== ''): ?>
                <div class="ficha-id-item">
                    <span class="ficha-id-etiqueta">Fecha de Registro</span>
                    <span class="ficha-id-valor"><?= e($creadoTexto) ?> h</span>
                </div>
                <?php endif; ?>
                <?php if ($actualizadoTexto !== ''): ?>
                <div class="ficha-id-item">
                    <span class="ficha-id-etiqueta">Última Modificación</span>
                    <span class="ficha-id-valor"><?= e($actualizadoTexto) ?> h</span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($estadoFlujo === 'finalizado'): ?>
                <div class="alert alert-finalizado" role="alert" style="margin-top: 1rem;">
                    <span style="font-weight: 700;">&#9888; Atención:</span>
                    <span>Esta cita ya fue <strong>finalizada</strong> en el flujo operativo. Los cambios se guardarán igualmente pero no revertirán el estado del flujo.</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Alerta de error de validación ── -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert" style="margin-bottom: 1.25rem;">
                <span style="font-weight: 700;">&#9888; Error de validación:</span>
                <span><?= e((string)$error) ?></span>
            </div>
        <?php endif; ?>

        <!-- ── Formulario en 2 columnas ── -->
        <div class="card card-formulario">
            <form action="actualizar.php" method="POST" id="form-editar-cita">
                <?= campo_csrf() ?>
                <input type="hidden" name="id"           value="<?= e($citaId) ?>">
                <input type="hidden" name="fecha_previa" value="<?= e($fechaHtmlInput) ?>">

                <div class="form-grid-2">

                    <!-- ── Columna 1: Paciente y datos clínicos ── -->
                    <div class="form-columna">
                        <h3 class="form-seccion-titulo">
                            <span class="icono-seccion">&#128100;</span> Datos del Paciente y Motivo
                        </h3>

                        <div class="form-group">
                            <label for="motivo">Paciente / Motivo Clínico <span class="campo-obligatorio">*</span></label>
                            <input type="text" id="motivo" name="motivo"
                                   required minlength="5" maxlength="100"
                                   pattern="^(?!\d+$).{5,100}$"
                                   title="Mínimo 5 caracteres. No puede ser exclusivamente numérico."
                                   value="<?= e($motivoActual) ?>"
                                   autocomplete="off">
                            <small class="form-ayuda">Nombre del paciente o descripción clínica. Mínimo 5 caracteres.</small>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo de Terapia <span class="campo-obligatorio">*</span></label>
                            <select id="tipo" name="tipo" required>
                                <?php foreach (TIPOS_CITA as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $tipoActual === $opt ? 'selected' : '' ?>>
                                        <?php echo match($opt) {
                                            'Individual' => 'Individual (Adultos y jóvenes)',
                                            'Pareja'     => 'Terapia de Pareja',
                                            'Infantil'   => 'Psicología Infantil y Adolescentes',
                                            'Familiar'   => 'Terapia Familiar Sistémica',
                                            default      => e($opt),
                                        }; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado Administrativo de la Cita</label>
                            <select id="estado" name="estado">
                                <?php foreach (ESTADOS_CITA as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $estadoActual === $opt ? 'selected' : '' ?>>
                                        <?php echo match($opt) {
                                            'Pendiente'  => 'Pendiente (Por atender)',
                                            'Completada' => 'Completada (Consulta realizada)',
                                            'Cancelada'  => 'Cancelada',
                                            default      => e($opt),
                                        }; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-ayuda">El flujo operativo (bandeja) gestiona la transición de estados automáticamente.</small>
                        </div>

                        <div class="form-group">
                            <label for="notas">Notas Clínicas / Observaciones</label>
                            <textarea id="notas" name="notas" rows="4" maxlength="1000"
                                      placeholder="Antecedentes relevantes, referencias, síntomas o indicaciones..."><?= e($notasActual) ?></textarea>
                            <small class="form-ayuda">Máximo 1,000 caracteres. Visible sólo en el expediente del paciente.</small>
                        </div>
                    </div>

                    <!-- ── Columna 2: Programación y terapeuta ── -->
                    <div class="form-columna">
                        <h3 class="form-seccion-titulo">
                            <span class="icono-seccion">&#128197;</span> Reprogramación y Reasignación
                        </h3>

                        <div class="form-group">
                            <label for="fecha_cita">Fecha y Hora de la Cita</label>
                            <?php if ($fechaHtmlInput !== ''): ?>
                                <div class="editar-fecha-actual">
                                    <span class="editar-fecha-etiqueta">&#128337; Fecha actual registrada:</span>
                                    <code class="editar-fecha-codigo"><?= e($fechaActual) ?></code>
                                </div>
                            <?php endif; ?>
                            <input type="datetime-local" id="fecha_cita" name="fecha_cita"
                                   value="<?= e($fechaHtmlInput) ?>"
                                   min="<?= date('Y-m-d\TH:i') ?>">
                            <small class="form-ayuda">
                                <?= $fechaHtmlInput !== ''
                                    ? 'Para mantener la fecha actual, déjela sin cambios. Para reprogramar, seleccione una nueva fecha futura.'
                                    : 'Opcional. Si se asigna, debe ser una fecha y hora presente o futura.'
                                ?>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="psicologo">Psicólogo / Terapeuta Asignado</label>
                            <?php if ($psicActual !== '' && $psicActual !== 'Por Asignar'): ?>
                                <div class="editar-psicologo-actual">
                                    <span class="editar-fecha-etiqueta">&#128100; Terapeuta actual:</span>
                                    <strong class="editar-psicologo-nombre"><?= e($psicActual) ?></strong>
                                </div>
                            <?php endif; ?>
                            <select id="psicologo" name="psicologo">
                                <option value="Por Asignar" <?= ($psicActual === '' || $psicActual === 'Por Asignar') ? 'selected' : '' ?>>
                                    Por Asignar en Recepción
                                </option>
                                <?php foreach ($psicologosActivos as $p): ?>
                                    <?php
                                        $nombreP = (string)($p->nombre ?? '');
                                        $espP    = (string)($p->especialidad ?? '');
                                        $sel     = ($psicActual === $nombreP) ? 'selected' : '';
                                    ?>
                                    <option value="<?= e($nombreP) ?>" <?= $sel ?>>
                                        <?= e($nombreP) ?><?= $espP !== '' ? ' — ' . e($espP) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-ayuda">Sólo se listan psicólogos activos del sistema. Para registrar uno nuevo, ve a "Equipo Psicólogos".</small>
                        </div>

                        <!-- ── Panel de historial de cambios ── -->
                        <?php if (!empty($historialCambios)): ?>
                        <div class="editar-historial-panel">
                            <h4 class="editar-historial-titulo">
                                <span>&#128203;</span> Historial de Modificaciones
                            </h4>
                            <ol class="editar-historial-lista">
                                <?php foreach (array_reverse($historialCambios) as $cambio): ?>
                                    <?php
                                        $fechaCambio = '';
                                        if (isset($cambio['fecha']) && $cambio['fecha'] instanceof MongoDB\BSON\UTCDateTime) {
                                            $fechaCambio = $cambio['fecha']->toDateTime()
                                                ->setTimezone(new DateTimeZone('America/Mexico_City'))
                                                ->format('d/m/Y H:i');
                                        }
                                        $campo  = (string)($cambio['campo']     ?? 'campo');
                                        $antes  = (string)($cambio['anterior']  ?? '—');
                                        $despues = (string)($cambio['nuevo']    ?? '—');
                                        $actor  = (string)($cambio['actor']     ?? 'Sistema');
                                    ?>
                                    <li class="editar-historial-item">
                                        <div class="historial-cambio-campo">
                                            <span class="historial-campo-nombre"><?= e(ucfirst($campo)) ?></span>
                                            <span class="historial-flecha">&rarr;</span>
                                            <span class="historial-valor-antes"><?= e($antes) ?></span>
                                            <span class="historial-flecha">&#8594;</span>
                                            <span class="historial-valor-nuevo"><?= e($despues) ?></span>
                                        </div>
                                        <div class="historial-meta-info">
                                            por <strong><?= e($actor) ?></strong>
                                            <?= $fechaCambio !== '' ? '&bull; ' . e($fechaCambio) . ' h' : '' ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                        <?php else: ?>
                        <div class="editar-historial-vacio">
                            <span>&#128203;</span>
                            <span>Sin modificaciones previas registradas.</span>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- ── Botones de Acción ── -->
                <div class="form-acciones">
                    <button type="submit" class="btn btn-primary">
                        &#10003; Guardar Cambios Clínicos
                    </button>
                    <a href="citas.php" class="btn btn-secondary">
                        Cancelar y Volver
                    </a>
                    <a href="expediente.php?id=<?= e($citaId) ?>" class="btn btn-outline" style="margin-left: auto;">
                        &#128196; Ver Expediente Completo
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
