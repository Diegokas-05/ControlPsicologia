<?php
declare(strict_types=1);

$mensaje = $_SESSION['mensaje_psicologo'] ?? ($mensaje ?? null);
unset($_SESSION['mensaje_psicologo']);
$error = $_SESSION['error_psicologo'] ?? ($error ?? null);
unset($_SESSION['error_psicologo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Equipo Clínico — ControlPsicología') ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <!-- Hero con Encabezado y Métricas -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <h1>Equipo de Psicólogos y Terapeutas</h1>
                <p>Gestión de cuentas profesionales con rol de consulta y asignación de expedientes.</p>
            </div>
            <div class="hero-stats-pills">
                <div class="stat-pill">
                    <span class="stat-pill-valor"><?= (int)$total ?></span>
                    <span class="stat-pill-etiqueta">Total Terapeutas</span>
                </div>
                <div class="stat-pill stat-pill-exito">
                    <span class="stat-pill-valor"><?= (int)$activos ?></span>
                    <span class="stat-pill-etiqueta">Activos</span>
                </div>
                <?php if ($inactivos > 0): ?>
                    <div class="stat-pill stat-pill-aviso">
                        <span class="stat-pill-valor"><?= (int)$inactivos ?></span>
                        <span class="stat-pill-etiqueta">Inactivos</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success" role="alert" style="margin-bottom: 1.5rem;">
                <span style="font-weight: 700;">&#10003; Éxito:</span>
                <span><?= e((string)$mensaje) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
                <span style="font-weight: 700;">&#9888; Atención:</span>
                <span><?= e((string)$error) ?></span>
            </div>
        <?php endif; ?>

        <div class="psicologos-grid-layout">
            <!-- Formulario de Registro de Terapeuta -->
            <section class="card card-formulario card-nuevo-terapeuta">
                <div class="card-cabecera">
                    <h3 class="card-titulo">
                        <span class="icono-seccion">&#128101;</span> Registrar Nuevo Psicólogo
                    </h3>
                    <p class="card-subtitulo">Se creará un acceso con rol 'consulta' para atender expedientes.</p>
                </div>

                <form action="psicologos.php?accion=crear" method="POST" id="form-crear-psicologo">
                    <?= campo_csrf() ?>

                    <div class="form-group">
                        <label for="nombre">Nombre Completo <span class="campo-obligatorio">*</span></label>
                        <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="100"
                               placeholder="Ej. Dra. Sofía Valenzuela" autocomplete="off">
                        <small class="form-ayuda">Mínimo 3 caracteres con título profesional.</small>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electrónico <span class="campo-obligatorio">*</span></label>
                        <input type="email" id="correo" name="correo" required maxlength="120"
                               placeholder="terapeuta@controlpsicologia.com" autocomplete="off">
                        <small class="form-ayuda">Servirá como usuario de inicio de sesión.</small>
                    </div>

                    <div class="form-group">
                        <label for="especialidad">Especialidad / Enfoque Clínico</label>
                        <input type="text" id="especialidad" name="especialidad" maxlength="100"
                               placeholder="Ej. Terapia Cognitivo-Conductual, Parejas, Infantil" autocomplete="off">
                        <small class="form-ayuda">Opcional para referencia en asignación de citas.</small>
                    </div>

                    <div class="form-group">
                        <label for="clave">Contraseña Provisional <span class="campo-obligatorio">*</span></label>
                        <input type="password" id="clave" name="clave" required minlength="6" maxlength="100"
                               placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                        <small class="form-ayuda">El terapeuta podrá iniciar sesión de forma segura.</small>
                    </div>

                    <div class="form-acciones" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            &#10003; Registrar Terapeuta Clínico
                        </button>
                    </div>
                </form>
            </section>

            <!-- Tabla de Psicólogos Registrados -->
            <section class="card card-tabla card-lista-terapeutas">
                <div class="card-cabecera">
                    <h3 class="card-titulo">
                        <span class="icono-seccion">&#128196;</span> Terapeutas en Base de Datos
                    </h3>
                    <p class="card-subtitulo">Listado de psicólogos para asignación de citas y control de acceso.</p>
                </div>

                <div class="tabla-responsive">
                    <table class="tabla-clinica">
                        <thead>
                            <tr>
                                <th>Terapeuta</th>
                                <th>Correo</th>
                                <th>Especialidad</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($psicologos)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-style: italic;">
                                        No hay psicólogos registrados en el sistema. Registra el primero en el formulario lateral.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($psicologos as $p): ?>
                                <?php
                                    $id = (string)($p->_id ?? '');
                                    $nombre = (string)($p->nombre ?? 'Sin nombre');
                                    $correo = (string)($p->correo ?? '');
                                    $esp = (string)($p->especialidad ?? 'Psicología General');
                                    $activo = (bool)($p->activo ?? false);
                                    $iniciales = mb_strtoupper(mb_substr($nombre, 0, 2, 'UTF-8'), 'UTF-8');
                                ?>
                                <tr>
                                    <td>
                                        <div class="terapeuta-item-perfil">
                                            <div class="avatar-terapeuta"><?= e($iniciales) ?></div>
                                            <div>
                                                <strong style="color: var(--text-main); display: block;"><?= e($nombre) ?></strong>
                                                <span class="badge badge-rol-consulta">Terapeuta Clínico</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="correo-terapeuta"><?= e($correo) ?></span>
                                    </td>
                                    <td>
                                        <span class="especialidad-tag"><?= e($esp ?: 'Psicología General') ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($activo): ?>
                                            <span class="badge badge-activo">&#9679; Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactivo">&#9675; Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <form action="psicologos.php?accion=toggle" method="POST" style="display: inline-block; margin: 0;">
                                            <?= campo_csrf() ?>
                                            <input type="hidden" name="id" value="<?= e($id) ?>">
                                            <?php if ($activo): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar acceso del terapeuta">
                                                    Desactivar
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Reactivar acceso del terapeuta">
                                                    Habilitar
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
