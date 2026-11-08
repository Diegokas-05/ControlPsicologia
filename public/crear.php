<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';
require_once $raiz . '/funciones.php';
require_once $raiz . '/app/Security/Sesion.php';
require_once $raiz . '/app/Security/Csrf.php';
require_once $raiz . '/app/Security/Entrada.php';
require_once $raiz . '/app/Models/UsuarioModel.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}

$db = new Database();
$baseDatos = $db->conectar();
$usuarioModel = new UsuarioModel($baseDatos->usuarios);
$psicologosActivos = $usuarioModel->listarPsicologosActivos();

$error = $_SESSION['error_crear'] ?? ($_GET['error'] ?? null);
unset($_SESSION['error_crear']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Cita Clínica — ControlPsicología</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require $raiz . '/app/Views/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <!-- Hero del Formulario -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <h1>Registro de Nueva Cita Clínica</h1>
                <p>Apertura de expediente y programación de consulta psicológica.</p>
            </div>
            <a href="citas.php" class="btn btn-secondary">
                &larr; Volver a Citas
            </a>
        </div>

        <!-- Tarjeta de Registro Clínico en 2 Columnas -->
        <div class="card card-formulario">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <span style="font-weight: 700;">&#9888; Error de validación:</span>
                    <span><?= e((string)$error) ?></span>
                </div>
            <?php endif; ?>

            <form action="guardar.php" method="POST" id="form-crear-cita">
                <?= campo_csrf() ?>

                <div class="form-grid-2">
                    <!-- Columna 1: Datos Básicos y Clínicos -->
                    <div class="form-columna">
                        <h3 class="form-seccion-titulo">
                            <span class="icono-seccion">&#128100;</span> Datos del Paciente y Motivo
                        </h3>

                        <div class="form-group">
                            <label for="motivo">Paciente / Motivo Clínico <span class="campo-obligatorio">*</span></label>
                            <input type="text" id="motivo" name="motivo" required minlength="5" maxlength="100"
                                   pattern="^(?!\d+$).{5,100}$"
                                   title="El motivo o paciente debe tener al menos 5 caracteres y no puede ser sólo números."
                                   placeholder="Ej. Carlos Mendoza — Evaluación de estrés y ansiedad"
                                   autocomplete="off">
                            <small class="form-ayuda">Mínimo 5 caracteres. No se permiten nombres exclusivamente numéricos.</small>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo de Terapia <span class="campo-obligatorio">*</span></label>
                            <select id="tipo" name="tipo" required>
                                <option value="Individual">Individual (Adultos y jóvenes)</option>
                                <option value="Pareja">Terapia de Pareja</option>
                                <option value="Infantil">Psicología Infantil y Adolescentes</option>
                                <option value="Familiar">Terapia Familiar Sistémica</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado Inicial de la Cita</label>
                            <select id="estado" name="estado">
                                <option value="Pendiente" selected>Pendiente (Por atender)</option>
                                <option value="Completada">Completada (Consulta ya realizada)</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>

                    <!-- Columna 2: Programación y Asignación -->
                    <div class="form-columna">
                        <h3 class="form-seccion-titulo">
                            <span class="icono-seccion">&#128197;</span> Programación y Terapeuta
                        </h3>

                        <div class="form-group">
                            <label for="fecha_cita">Fecha y Hora Programada</label>
                            <input type="datetime-local" id="fecha_cita" name="fecha_cita"
                                   min="<?= date('Y-m-d\TH:i') ?>">
                            <small class="form-ayuda">Restringido a fecha y hora presente o futura.</small>
                        </div>

                        <div class="form-group">
                            <label for="psicologo">Psicólogo Asignado</label>
                            <select id="psicologo" name="psicologo">
                                <option value="Por Asignar">Por Asignar en Recepción</option>
                                <?php foreach ($psicologosActivos as $p): ?>
                                    <?php 
                                        $nombreP = (string)($p->nombre ?? '');
                                        $espP = (string)($p->especialidad ?? '');
                                    ?>
                                    <option value="<?= e($nombreP) ?>">
                                        <?= e($nombreP) ?><?= $espP !== '' ? ' (' . e($espP) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-ayuda">Psicólogos activos disponibles en el sistema.</small>
                        </div>

                        <div class="form-group">
                            <label for="notas">Notas Preliminares / Observaciones</label>
                            <textarea id="notas" name="notas" rows="3" maxlength="500"
                                      placeholder="Antecedentes relevantes, persona que refiere o síntomas iniciales..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción Diferenciados -->
                <div class="form-acciones">
                    <button type="submit" class="btn btn-primary">
                        &#10003; Guardar Cita Clínica
                    </button>
                    <a href="citas.php" class="btn btn-secondary">
                        Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
