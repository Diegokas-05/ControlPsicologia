<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';
require_once '../app/Security/Sesion.php';
require_once '../app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

validar_csrf();

$id     = trim($_POST['id'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');
$tipo   = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? '');

function mostrarError(string $mensaje, string $id): void {
    $idSeguro = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    $msgSeguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    die("
        <link rel='stylesheet' href='assets/css/estilos.css'>
        <div class='container'>
            <div class='alert alert-danger'>Fallo de validación: {$msgSeguro}</div>
            <a href='editar.php?id={$idSeguro}' class='btn-link'>Volver al formulario</a>
        </div>
    ");
}

if (empty($id) || empty($motivo) || strlen($motivo) > 100) {
    mostrarError("El motivo es obligatorio y máximo de 100 caracteres.", $id);
}

$tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
if (!in_array($tipo, $tiposPermitidos, true)) {
    mostrarError("Tipo de terapia no permitido.", $id);
}

$estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
if (!in_array($estado, $estadosPermitidos, true)) {
    mostrarError("Estado no permitido.", $id);
}

$citaModel = new Cita();
$datosFormulario = [
    'motivo' => $motivo,
    'tipo'   => $tipo,
    'estado' => $estado,
];

if ($citaModel->actualizar($id, $datosFormulario)) {
    $_SESSION['mensaje_exito'] = "La cita fue actualizada exitosamente.";
    header('Location: mvc.php', true, 303);
    exit;
}

mostrarError("Error al actualizar en la base de datos.", $id);