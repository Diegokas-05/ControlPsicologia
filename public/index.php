<?php
// requerimos el archivo de configuración de la base de datos
require_once '../config/Database.php';

// instanciamos la clase Database para establecer la conexión
$db = new Database();

// llamamos al método conectar() para obtener la conexión
$conexion = $db->conectar();

if ($conexion instanceof MongoDB\Driver\Manager) {
    echo "<h1>Conexión exitosa a MongoDB</h1>";
    echo "<p style='color: green; '> conectado exitosamente a la base de datos</p>";
}
