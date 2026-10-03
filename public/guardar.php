<?php
require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';
require_once '../app/Security/Sesion.php';
require_once '../app/Security/Csrf.php';

// Controles de seguridad de la Guía 06
iniciar_sesion_segura();
exigir_rol('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

validar_csrf();

# Capturar y limpiar los datos 
$motivo = trim($_POST['motivo'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? '');

// Validar y redirigir con error si algo falla
if (empty($motivo) || strlen($motivo) > 100) {
    header('Location: crear.php?error=El motivo es obligatorio y máximo de 100 caracteres.');
    exit;
}

$tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
if (!in_array($tipo, $tiposPermitidos)){
    header('Location: crear.php?error=Tipo de terapia no permitida.');
    exit;
}

$estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
if (!in_array($estado, $estadosPermitidos)){
    header('Location: crear.php?error=Estado no permitido.');
    exit;
}

// Guardar en la base de datos
$citaModel = new Cita();
$datosFormulario = [
    'motivo' => $motivo,
    'tipo' => $tipo,
    'estado' => $estado
];

if ($citaModel->crear($datosFormulario)) {
    // 1. Guardamos el mensaje temporal en la sesión
    $_SESSION['mensaje_exito'] = "La cita fue registrada exitosamente.";
    
    // 2. Redirigimos al listado
    header('Location: mvc.php', true, 303);
    exit;
} else {
    header('Location: crear.php?error=Error al guardar en la base de datos.');
    exit;
}