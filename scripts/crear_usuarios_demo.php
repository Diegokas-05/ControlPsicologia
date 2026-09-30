<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz.'/vendor/autoload.php';
require_once $raiz.'/config/Database.php';

// Conectar a la base de datos de tu proyecto
$db = new Database();
$baseDatos = $db->conectar();
$usuarios = $baseDatos->usuarios;

// Crear índice único para el correo normalizado
$usuarios->createIndex(
    ['correo_normalizado' => 1],
    ['unique' => true]
);

// Cuentas ficticias exigidas por la rúbrica
$cuentas = [
    ['Administrador Psicología', 'admin@psicologia.test', 'Admin-2026!', 'admin'],
    ['Asistente Consulta', 'consulta@psicologia.test', 'Consulta-2026!', 'consulta'],
];

foreach ($cuentas as [$nombre, $correo, $clave, $rol]) {
    $correoNormalizado = mb_strtolower(trim($correo), 'UTF-8');
    
    $usuarios->updateOne(
        ['correo_normalizado' => $correoNormalizado],
        ['$set' => [
            'nombre' => $nombre,
            'correo' => $correo,
            'correo_normalizado' => $correoNormalizado,
            'clave_hash' => password_hash($clave, PASSWORD_DEFAULT),
            'rol' => $rol,
            'activo' => true,
            'actualizado_en' => new MongoDB\BSON\UTCDateTime()
        ]],
        ['upsert' => true]
    );
    echo "Cuenta preparada: $correo [$rol]\n";
}