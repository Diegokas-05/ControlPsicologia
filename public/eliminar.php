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

$id = trim($_POST['id'] ?? '');

if (!empty($id)) {
    $citaModel = new Cita();
    $citaModel->eliminar($id);
    $_SESSION['mensaje_exito'] = "La cita fue eliminada.";
}

header('Location: mvc.php', true, 303);
exit;