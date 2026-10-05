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

$motivo = trim($_POST['motivo'] ?? '');
$tipo   = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? '');

if (empty($motivo) || strlen($motivo) > 100) {
    header('Location: crear.php?error=El motivo es obligatorio y máximo de 100 caracteres.', true, 303);
    exit;
}

$tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
if (!in_array($tipo, $tiposPermitidos, true)) {
    header('Location: crear.php?error=Tipo de terapia no permitida.', true, 303);
    exit;
}

$estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
if (!in_array($estado, $estadosPermitidos, true)) {
    header('Location: crear.php?error=Estado no permitido.', true, 303);
    exit;
}

$citaModel = new Cita();
$datosFormulario = [
    'motivo' => $motivo,
    'tipo'   => $tipo,
    'estado' => $estado,
];

if ($citaModel->crear($datosFormulario)) {
    $_SESSION['mensaje_exito'] = "La cita fue registrada exitosamente.";
    header('Location: mvc.php', true, 303);
    exit;
}

header('Location: crear.php?error=Error al guardar en la base de datos.', true, 303);
exit;