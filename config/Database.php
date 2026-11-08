<?php
declare(strict_types=1);

class Database {
    private string $uri = "mongodb://localhost:27017"; 
    private ?MongoDB\Client $cliente = null;

    public function conectar(): MongoDB\Database {
        try {
            if ($this->cliente === null) {
                $this->cliente = new MongoDB\Client($this->uri);
            }
            return $this->cliente->control_psicologia;
        } catch (MongoDB\Driver\Exception\Exception $e) {
            die("Error crítico de conexión a MongoDB: " . $e->getMessage());
        }
    }

    public function getAuditoria(): MongoDB\Collection {
        return $this->conectar()->bitacora; // O ->auditoria
    }
}