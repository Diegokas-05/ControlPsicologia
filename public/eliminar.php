<?php
// public/eliminar.php

require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';

// Verificamos que la eliminación venga de un formulario POST por seguridad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    
    if (!empty($id)) {
        $citaModel = new Cita();
        $citaModel->eliminar($id);
    }
}

// Redirigimos de vuelta al listado principal de forma automática
header('Location: index.php');
exit;