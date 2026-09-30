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

    // Método para eliminar una cita (Requisito R04)
    public function eliminar($id) {
        try {
            // MongoDB requiere que los IDs de texto se conviertan a su formato especial ObjectId
            $idMongo = new MongoDB\BSON\ObjectId($id);
            $resultado = $this->coleccion->deleteOne(['_id' => $idMongo]);
            
            return $resultado->getDeletedCount() > 0;
            
        } catch (Exception $e) {
            return false;
        }

    }

    
    // Método para obtener una cita específica por su ID
    public function obtenerPorId($id) {
        $idMongo = new MongoDB\BSON\ObjectId($id);
        return $this->coleccion->findOne(['_id' => $idMongo]);
    }

    // Método para actualizar una cita (Requisito R03)
    public function actualizar($id, $datos) {
        try {
            $idMongo = new MongoDB\BSON\ObjectId($id);
            
            // $set le indica a MongoDB que solo reemplace los campos especificados, conservando el _id
            $resultado = $this->coleccion->updateOne(
                ['_id' => $idMongo],
                ['$set' => [
                    'motivo' => $datos['motivo'],
                    'tipo' => $datos['tipo'],
                    'estado' => $datos['estado']
                ]]
            );
            
            // Retornamos true incluso si el usuario guarda sin hacer cambios
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}