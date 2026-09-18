<?php

require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';

// Requisito p09: rechazar peticiones get
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("
        <link rel='stylesheet' href='assets/css/estilos.css'>
        <div class='container'>
            <div class='alert alert-danger'>Error: La aplicacion rechazada la peticion GET. Usa el formulario.</div>
            <a href='crear.php' class='btn-link'>Volver al formulario</a>
        </div>
    ");
}

# capturar  y limpia los datos 
$motivo = trim($_POST['motivo'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? '');

//funcion auxiliar para mostrar errores sin romper el diseño
function mostrarError($mensaje) {
    die("
        <link rel='stylesheet' href='assets/css/estilos.css'>
        <div class='container'>
            <div class='alert alert-danger'>Fallo de validacion: $mensaje</div>
            <a href='crear.php' class='btn-link'> Volver al formularion</a>
        </div>
    ");
}

// validar obligatorio sea mayor a 100 la longitud
if (empty($motivo) || strlen($motivo) > 100) {
    mostrarError("El motivo es obligatorio y máximo de 100 caracteres. ");  
}
// validar que los valores pertenezcan a las listas permitidas
$tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
if (!in_array($tipo, $tiposPermitidos)){
    mostrarError("Tipo de terapia no permitida.");
}

$estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
if (!in_array($estado, $estadosPermitidos)){
    mostrarError("Estado no permitido.");
}

$citaModel = new Cita();
$datosFormulario = [
    'motivo' => $motivo,
    'tipo' => $tipo,
    'estado' => $estado
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ControlPsicologico</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container">
        <?php if ($citaModel->crear($datosFormulario)): ?>
            <div class="alert alert-success"> Registro guardado exitosamente.</div>
        <?php else: ?>
            <div class="alert alert-danger"> Error al guardar en la base de datos.</div>
        <?php endif; ?>
        <a href="crear.php" class="btn-link">Registrar otra cita</a>
    </div>
</body>
</html>
