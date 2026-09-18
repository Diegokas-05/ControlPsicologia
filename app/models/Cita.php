<?php
// app/models/Cita.php

require_once __DIR__ . '/../../config/Database.php';

class Cita {
    private $coleccion;

    public function __construct() { // Constructor de la clase Cita
        
        $db = new Database(); // Instanciamos la clase Database para establecer la conexión
        $baseDatos = $db->conectar(); // Llamamos al método conectar() para obtener la conexión a la base de datos
        $this->coleccion = $baseDatos->citas; // Accedemos a la colección 'citas'
    }


    public function crear($datos) {

        try {

            $resultado = $this->coleccion->insertOne([
                'motivo' => $datos['motivo'],
                'tipo' => $datos['tipo'],
                'estado' => $datos['estado'],
                'fecha_creacion' => new MongoDB\BSON\UTCDateTime() // Guardamos la fecha de creación como un objeto UTCDateTime
            ]);

            return $resultado->getInsertedCount(); // Retorna el número de documentos insertados (debería ser 1 si la inserción fue exitosa)
        } catch (Exception $e) {
            echo "hubo un error al guardar la cita.";
            return false; // Retorna false si ocurre un error durante la inserción
        }
    }
    
    //metodo para consultar citas
    public function consultar($filtroTipo = '', $filtroEstado = '') {
        $criterios = [];
        
        // Si el usuario seleccionó un filtro, lo agregamos a los criterios
        if (!empty($filtroTipo)) {
            $criterios['tipo'] = $filtroTipo;
        }
        if (!empty($filtroEstado)) {
            $criterios['estado'] = $filtroEstado;
        }
        
        // Ejecutamos la consulta find() requerida en la rúbrica
        return $this->coleccion->find($criterios);
    }
}