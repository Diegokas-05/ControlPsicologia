<?php
// 1. Cargar las dependencias de Composer (OBLIGATORIO)
require_once '../vendor/autoload.php';
// requerimos el archivo de configuración de la base de datos
require_once '../config/Database.php';

// instanciamos la clase Database para establecer la conexión
$db = new Database();

// llamamos al método conectar() para obtener la conexión
$baseDatos = $db->conectar();

if ($baseDatos) {
    echo "<h1>Entorno listo para el Avance 01</h1>";
    echo "<p style='color: green; '> conectado exitosamente a la base de datos</p>";
}
