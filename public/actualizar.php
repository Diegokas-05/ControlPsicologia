<?php
require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = trim($_POST['id'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? '');

function mostrarError($mensaje, $id) {
    die("
        <link rel='stylesheet' href='assets/css/estilos.css'>
        <div class='container'>
            <div class='alert alert-danger'>Fallo de validación: $mensaje</div>
            <a href='editar.php?id=$id' class='btn-link'>Volver al formulario</a>
        </div>
    ");
}

if (empty($id) || empty($motivo) || strlen($motivo) > 100) {
    mostrarError("El motivo es obligatorio y máximo de 100 caracteres.", $id);
}

$tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
if (!in_array($tipo, $tiposPermitidos)) {
    mostrarError("Tipo de terapia no permitido.", $id);
}

$estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
if (!in_array($estado, $estadosPermitidos)) {
    mostrarError("Estado no permitido.", $id);
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
    <title>Actualización - ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container">
        <?php if ($citaModel->actualizar($id, $datosFormulario)): ?>
            <div class="alert alert-success">Registro actualizado exitosamente.</div>
        <?php else: ?>
            <div class="alert alert-danger">Error al actualizar en la base de datos.</div>
        <?php endif; ?>
        <a href="index.php" class="btn-link">Volver al listado</a>
    </div>
</body>
</html>