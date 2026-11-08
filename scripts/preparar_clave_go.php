<?php
declare(strict_types=1);
$directorio = dirname(__DIR__) . '/.local';
if (!is_dir($directorio)) {
    mkdir($directorio, 0700, true);
}
$archivo = $directorio . '/servicio-go.key';
if (!is_file($archivo)) {
    if (file_put_contents($archivo, bin2hex(random_bytes(32))) === false) {
        throw new RuntimeException('No se pudo guardar la clave.');
    }
}
echo "Clave local disponible; no se muestra en pantalla.\n";