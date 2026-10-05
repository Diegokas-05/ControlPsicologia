<?php
declare(strict_types=1);

$raiz = dirname(__DIR__);
require_once $raiz . '/vendor/autoload.php';
require_once $raiz . '/config/Database.php';

$db         = new Database();
$baseDatos  = $db->conectar();
$usuarios   = $baseDatos->usuarios;

$usuarios->createIndex(
    ['correo_normalizado' => 1],
    ['unique' => true]
);

$cuentas = [
    ['Administrador Psicología', 'admin@gmail.com', 'Admin2026', 'admin'],
    ['Asistente Consulta', 'consulta@gmail.com', 'hola', 'consulta'],
];

foreach ($cuentas as [$nombre, $correo, $clave, $rol]) {
    $correoNormalizado = mb_strtolower(trim($correo), 'UTF-8');

    $usuarios->updateOne(
        ['correo_normalizado' => $correoNormalizado],
        [
            '$set' => [
                'nombre'              => $nombre,
                'correo'              => $correo,
                'correo_normalizado'  => $correoNormalizado,
                'clave_hash'          => password_hash($clave, PASSWORD_DEFAULT),
                'rol'                 => $rol,
                'activo'              => true,
                'actualizado_en'      => new MongoDB\BSON\UTCDateTime(),
                'intentos_fallidos'   => 0,
            ],
            '$unset' => ['bloqueado_hasta' => ''],
        ],
        ['upsert' => true]
    );

    echo "Cuenta preparada: {$correo} [{$rol}]\n";
}