<?php

class Database {
    // Cadena de conexión. Por defecto es el puerto 27017 para MongoDB local.
    private $uri = "mongodb://localhost:27017"; 
    private $cliente;

    // Método principal para establecer la conexión
    public function conectar() {
        try {
        
        // instanciamos el cliente de MongoDB con la URI proporcionada
        $this->cliente = new MongoDB\Client($this->uri);

        return $this->cliente->control_psicologia; // Retorna la conexión a la base de datos 'control_psicologia'
            
        } catch (MongoDB\Driver\Exception\Exception $e) {
            // Si el servidor de Mongo está apagado o hay un error, detenemos todo
            die("Error crítico de conexión a MongoDB: " . $e->getMessage());
        }
    }
}