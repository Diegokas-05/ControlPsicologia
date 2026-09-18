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

        <form action="guardar.php" method="POST">
            <div class="form-group">
                <label for="motivo">Motivo de la Consulta:</label>
                <input type="text" id="motivo" name="motivo" required maxlength="100" placeholder="Ej. Estres Laboral">
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

            <button type="submit">Guardar Cita</button>
        </form>
    </div>
</body>
</html>
