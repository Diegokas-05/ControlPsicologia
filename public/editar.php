<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';
require_once '../app/Security/Sesion.php';
require_once '../app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die("<h3 style='color:red;'>Error: ID no proporcionado.</h3><a href='mvc.php'>Volver</a>");
}

$citaModel  = new Cita();
$citaActual = $citaModel->obtenerPorId($id);

if (!$citaActual) {
    die("<h3 style='color:red;'>Error: Registro no encontrado.</h3><a href='mvc.php'>Volver</a>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cita - ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container">
        <h1>Editar Cita</h1>

        <form action="actualizar.php" method="POST">
            <?= campo_csrf() ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

            <div class="form-group">
                <label for="motivo">Motivo de la consulta:</label>
                <input type="text" id="motivo" name="motivo" required maxlength="100"
                       value="<?= htmlspecialchars($citaActual['motivo']) ?>">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de terapia:</label>
                <select id="tipo" name="tipo">
                    <option value="Individual" <?= $citaActual['tipo'] === 'Individual' ? 'selected' : '' ?>>Individual</option>
                    <option value="Pareja"     <?= $citaActual['tipo'] === 'Pareja'     ? 'selected' : '' ?>>Pareja</option>
                    <option value="Infantil"   <?= $citaActual['tipo'] === 'Infantil'   ? 'selected' : '' ?>>Infantil</option>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado de la cita:</label>
                <select id="estado" name="estado">
                    <option value="Pendiente"  <?= $citaActual['estado'] === 'Pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Completada" <?= $citaActual['estado'] === 'Completada' ? 'selected' : '' ?>>Completada</option>
                    <option value="Cancelada"  <?= $citaActual['estado'] === 'Cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>

            <button type="submit">Actualizar Cita</button>
            <a href="mvc.php" class="btn-link" style="display: block; margin-top: 10px; text-align: center;">Cancelar y Volver</a>
        </form>
    </div>
</body>
</html>