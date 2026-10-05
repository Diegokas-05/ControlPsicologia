<?php
declare(strict_types=1);

require_once '../app/Security/Sesion.php';
require_once '../app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('Método no permitido.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Cita - Control de Psicología</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Nueva Cita</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars((string)$_GET['error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="guardar.php" method="POST">
            <?= campo_csrf() ?>

            <div class="form-group">
                <label for="motivo">Motivo de la Consulta:</label>
                <input type="text" id="motivo" name="motivo" required maxlength="100" placeholder="Ej. Estrés Laboral">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de Terapia:</label>
                <select id="tipo" name="tipo">
                    <option value="Individual">Individual</option>
                    <option value="Pareja">Pareja</option>
                    <option value="Infantil">Infantil</option>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado de la cita:</label>
                <select id="estado" name="estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Completada">Completada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit">Guardar Cita</button>
                <a href="mvc.php" class="btn-link" style="margin-left: 15px;">Volver al listado</a>
            </div>
        </form>
    </div>
</body>
</html>