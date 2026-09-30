<?php
require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die("<h3 style='color:red;'>Error: ID no proporcionado.</h3><a href='index.php'>Volver</a>");
}

$citaModel = new Cita();
$citaActual = $citaModel->obtenerPorId($id);

if (!$citaActual) {
    die("<h3 style='color:red;'>Error: Registro no encontrado.</h3><a href='index.php'>Volver</a>");
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
            <!-- Campo oculto para enviar el ID al controlador -->
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="form-group">
                <label for="motivo">Motivo de la consulta (Principal):</label>
                <input type="text" id="motivo" name="motivo" required maxlength="100" value="<?= htmlspecialchars($citaActual['motivo']) ?>">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de terapia (Definido 1):</label>
                <select id="tipo" name="tipo">
                    <option value="Individual" <?= $citaActual['tipo'] == 'Individual' ? 'selected' : '' ?>>Individual</option>
                    <option value="Pareja" <?= $citaActual['tipo'] == 'Pareja' ? 'selected' : '' ?>>Pareja</option>
                    <option value="Infantil" <?= $citaActual['tipo'] == 'Infantil' ? 'selected' : '' ?>>Infantil</option>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado de la cita (Definido 2):</label>
                <select id="estado" name="estado">
                    <option value="Pendiente" <?= $citaActual['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Completada" <?= $citaActual['estado'] == 'Completada' ? 'selected' : '' ?>>Completada</option>
                    <option value="Cancelada" <?= $citaActual['estado'] == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>

            <button type="submit">Actualizar Cita</button>
            <a href="index.php" class="btn-link" style="display: block; margin-top: 10px; text-align: center;">Cancelar y Volver</a>
        </form>
    </div>
</body>
</html>