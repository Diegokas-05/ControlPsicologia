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

try {
    // entrada_id valida formato de 24 hex y retorna un ObjectId
    $id = entrada_id($_GET);

    $citaModel = new Cita();
    $citaActual = $citaModel->obtenerPorId((string)$id);

    if (!$citaActual) {
        http_response_code(404);
        exit('Registro no encontrado.');
    }

} catch (InvalidArgumentException $e) {
    registrar_rechazo_entrada_guia07('editar');
    http_response_code(422);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Identificador invalido.');
} catch (Throwable $e) {
    error_log('FALLO_EDITAR_GUIA07: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Cita - ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require $raiz . '/app/Views/layouts/header.php'; ?>

    <main class="container">
        <h1>Editar Cita</h1>

        <form action="actualizar.php" method="POST">
            <?= campo_csrf() ?>
            <input type="hidden" name="id" value="<?= e((string)$id) ?>">

            <div class="form-group">
                <label for="motivo">Motivo de la consulta:</label>
                <input type="text" id="motivo" name="motivo" required maxlength="100"
                       value="<?= e($citaActual['motivo'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de terapia:</label>
                <select id="tipo" name="tipo">
                    <?php foreach (TIPOS_CITA as $tipo): ?>
                        <option value="<?= e($tipo) ?>" <?= ($citaActual['tipo'] ?? '') === $tipo ? 'selected' : '' ?>>
                            <?= e($tipo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado de la cita:</label>
                <select id="estado" name="estado">
                    <?php foreach (ESTADOS_CITA as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= ($citaActual['estado'] ?? '') === $estado ? 'selected' : '' ?>>
                            <?= e($estado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top: 24px; display: flex; align-items: center; gap: 14px;">
                <button type="submit" class="btn btn-primary">Actualizar Cita</button>
                <a href="citas.php" class="btn-link">Cancelar y Volver</a>
            </div>
        </form>
    </main>
</body>
</html>
