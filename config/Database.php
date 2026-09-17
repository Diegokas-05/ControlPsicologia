<?php

class Database {
    // Cadena de conexión. Por defecto es el puerto 27017 para MongoDB local.
    private $uri = "mongodb://localhost:27017"; 
    private $conexion;

    // Método principal para establecer la conexión
    public function conectar() {
        try {
            // Instanciamos el Manager de MongoDB que activamos en el php.ini
            $this->conexion = new MongoDB\Driver\Manager($this->uri);
            return $this->conexion;
            
        } catch (MongoDB\Driver\Exception\Exception $e) {
            // Si el servidor de Mongo está apagado o hay un error, detenemos todo
            die("Error crítico de conexión a MongoDB: " . $e->getMessage());
        }
    }
}