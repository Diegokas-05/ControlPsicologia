<?php
declare(strict_types=1);

$idStr = (string)$cita['_id'];
$motivo = (string)($cita['motivo'] ?? 'Sin motivo');
$tipo = (string)($cita['tipo'] ?? 'Individual');
$estado = (string)($cita['estado'] ?? 'Pendiente');
$estadoFlujo = (string)($cita['estado_flujo'] ?? 'pendiente');
$fechaCreacion = isset($cita['fecha_creacion']) && $cita['fecha_creacion'] instanceof MongoDB\BSON\UTCDateTime
    ? $cita['fecha_creacion']->toDateTime()->format('d/m/Y H:i') . ' UTC'
    : 'No registrada';
$fechaCita = (string)($cita['fecha_cita'] ?? 'No programada');
$psicologo = (string)($cita['psicologo'] ?? 'Sin asignar');

$historial = $cita['historial_flujo'] ?? [];

$expedienteData = $cita['expediente'] ?? [];
$observaciones = (string)($expedienteData['observaciones'] ?? ($cita['notas'] ?? ''));
$sintomas = (string)($expedienteData['sintomas'] ?? '');
$planTratamiento = (string)($expedienteData['plan_tratamiento'] ?? '');
$ultimaActualizacion = isset($expedienteData['actualizado_en']) && $expedienteData['actualizado_en'] instanceof MongoDB\BSON\UTCDateTime
    ? $expedienteData['actualizado_en']->toDateTime()->format('d/m/Y H:i') . ' UTC'
    : null;

$slugTipo = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tipo));
$slugEstado = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $estado));
$slugFlujo = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $estadoFlujo));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <!-- Barra de Retorno y Acciones -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <a href="citas.php" class="btn btn-secondary" style="margin-bottom: 0.5rem;">
                    &larr; Volver a Expediente y Búsqueda de Citas
                </a>
                <h1>Expediente Clínico Individual</h1>
                <p>Seguimiento psicoterapéutico, bitácora de transiciones y evolución clínica.</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="editar.php?id=<?= e($idStr) ?>" class="btn btn-secondary">
                    Editar Cita
                </a>
            </div>
        </div>

        <!-- Alerta de éxito -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success" role="status">
                <span>&#10003;</span>
                <span><?= e($_SESSION['mensaje_exito']) ?></span>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <!-- 1. Header con Datos del Paciente -->
        <section class="card expediente-paciente-card" aria-labelledby="titulo-paciente">
            <div class="expediente-paciente-grid">
                <div class="paciente-principal">
                    <span class="expediente-id-tag">EXPEDIENTE ID: <?= e($idStr) ?></span>
                    <h2 id="titulo-paciente" class="paciente-nombre"><?= e($motivo) ?></h2>
                    <div class="paciente-badges">
                        <span class="badge badge-tipo-<?= e($slugTipo) ?>">
                            Terapia: <?= e($tipo) ?>
                        </span>
                        <span class="badge badge-estado-<?= e($slugEstado) ?>">
                            Estado Cita: <?= e($estado) ?>
                        </span>
                        <span class="badge badge-estado-<?= e($slugFlujo) ?>">
                            Flujo: <?= e(ucfirst($estadoFlujo)) ?>
                        </span>
                    </div>
                </div>

                <div class="paciente-metadatos">
                    <div class="meta-item">
                        <span class="meta-label">&#128197; Fecha de Registro:</span>
                        <span class="meta-valor"><?= e($fechaCreacion) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">&#9200; Programación:</span>
                        <span class="meta-valor"><?= e($fechaCita) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">&#9877; Terapeuta:</span>
                        <span class="meta-valor"><?= e($psicologo) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Grid de Expediente: Historial de Flujo + Notas de Evolución -->
        <div class="dashboard-grid">
            <!-- Sección A: Historial del Flujo de Trabajo (Timeline) -->
            <section class="card" aria-labelledby="titulo-historial">
                <div class="card-cabecera">
                    <div class="card-info">
                        <span class="card-icono">&#9881;</span>
                        <div>
                            <h3 id="titulo-historial" class="card-titulo">Historial de Flujo de Atención</h3>
                            <p class="card-subtitulo">Línea de tiempo de las transiciones de estado clínico</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-flujo">
                    <!-- Evento inicial: Creación -->
                    <div class="timeline-item">
                        <div class="timeline-punto timeline-punto-inicial"></div>
                        <div class="timeline-contenido">
                            <div class="timeline-cabecera">
                                <span class="badge badge-estado-pendiente">Ingreso / Pendiente</span>
                                <span class="timeline-fecha"><?= e($fechaCreacion) ?></span>
                            </div>
                            <p class="timeline-descripcion">
                                Registro inicial de la cita clínica en el sistema.
                            </p>
                        </div>
                    </div>

                    <!-- Eventos de Transición en historial_flujo -->
                    <?php if (!empty($historial)): ?>
                        <?php foreach ($historial as $evento): ?>
                            <?php 
                                $desde = (string)($evento['desde'] ?? '');
                                $hacia = (string)($evento['hacia'] ?? '');
                                $actor = (string)($evento['actor_id'] ?? 'Sistema');
                                $fechaEv = isset($evento['fecha']) && $evento['fecha'] instanceof MongoDB\BSON\UTCDateTime
                                    ? $evento['fecha']->toDateTime()->format('d/m/Y H:i') . ' UTC'
                                    : 'Reciente';
                                $slugHacia = strtolower($hacia);
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-punto"></div>
                                <div class="timeline-contenido">
                                    <div class="timeline-cabecera">
                                        <span class="badge badge-estado-<?= e($slugHacia) ?>">
                                            <?= e(ucfirst($desde)) ?> &rarr; <?= e(ucfirst($hacia)) ?>
                                        </span>
                                        <span class="timeline-fecha"><?= e($fechaEv) ?></span>
                                    </div>
                                    <p class="timeline-descripcion">
                                        Transición realizada por el usuario operador: <strong><?= e($actor) ?></strong>.
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="timeline-aviso">
                            <small>No se han registrado transiciones adicionales en la bandeja operativa.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Sección B: Notas de Evolución Psicológica -->
            <section class="card" aria-labelledby="titulo-notas">
                <div class="card-cabecera">
                    <div class="card-info">
                        <span class="card-icono">&#128221;</span>
                        <div>
                            <h3 id="titulo-notas" class="card-titulo">Notas de Evolución Psicológica</h3>
                            <p class="card-subtitulo">
                                <?= $ultimaActualizacion ? 'Última actualización: ' . e($ultimaActualizacion) : 'Redacción del terapeuta en sesión' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <form action="expediente.php" method="POST" class="form-expediente">
                    <?= campo_csrf() ?>
                    <input type="hidden" name="id" value="<?= e($idStr) ?>">

                    <div class="form-group">
                        <label for="observaciones">Observaciones Clínicas y Conducta en Sesión:</label>
                        <textarea id="observaciones" name="observaciones" rows="4" maxlength="2000"
                                  placeholder="Describe el estado de ánimo, reactividad emocional, lenguaje no verbal y temas abordados en consulta..."><?= e($observaciones) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="sintomas">Síntomas Detectados y Criterios Diagnósticos:</label>
                        <textarea id="sintomas" name="sintomas" rows="3" maxlength="1000"
                                  placeholder="Ej. Insomnio de conciliación, rumiación cognitiva, sintomatología de fobia social..."><?= e($sintomas) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="plan_tratamiento">Plan de Tratamiento e Intervenciones:</label>
                        <textarea id="plan_tratamiento" name="plan_tratamiento" rows="3" maxlength="2000"
                                  placeholder="Técnicas aplicadas (ej. Registro de pensamientos automáticos, respiración diafragmática) y tareas para casa..."><?= e($planTratamiento) ?></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            &#128190; Guardar Notas de Evolución
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
