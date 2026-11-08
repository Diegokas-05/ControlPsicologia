<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class Cita {
    private MongoDB\Collection $coleccion;

    public function __construct() {
        $db = new Database();
        $baseDatos = $db->conectar();
        $this->coleccion = $baseDatos->citas;
    }

    public function crear(array $datos): bool {
        try {
            $documento = [
                'motivo'          => $datos['motivo'],
                'tipo'            => $datos['tipo'],
                'estado'          => $datos['estado'] ?? 'Pendiente',
                'fecha_creacion'  => new MongoDB\BSON\UTCDateTime(),
                'estado_flujo'    => 'pendiente',
                'historial_flujo' => [],
                'actualizado_en'  => new MongoDB\BSON\UTCDateTime()
            ];

            if (!empty($datos['fecha_cita'])) {
                $documento['fecha_cita'] = (string)$datos['fecha_cita'];
            }
            if (!empty($datos['psicologo'])) {
                $documento['psicologo'] = (string)$datos['psicologo'];
            }
            if (!empty($datos['notas'])) {
                $documento['notas'] = (string)$datos['notas'];
            }

            $resultado = $this->coleccion->insertOne($documento);
            return $resultado->getInsertedCount() > 0;
        } catch (Exception $e) {
            error_log('Error al guardar cita: ' . $e->getMessage());
            return false;
        }
    }
    
    public function consultar(string $filtroTipo = '', string $filtroEstado = '') {
        $criterios = [];
        if (!empty($filtroTipo)) {
            $criterios['tipo'] = $filtroTipo;
        }
        if (!empty($filtroEstado)) {
            $criterios['estado'] = $filtroEstado;
        }
        return $this->coleccion->find($criterios);
    }

    public function eliminar(string $id): bool {
        try {
            $idMongo = new MongoDB\BSON\ObjectId($id);
            $resultado = $this->coleccion->deleteOne(['_id' => $idMongo]);
            return $resultado->getDeletedCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerPorId(string $id) {
        try {
            $idMongo = new MongoDB\BSON\ObjectId($id);
            return $this->coleccion->findOne(['_id' => $idMongo]);
        } catch (Exception $e) {
            return null;
        }
    }

    public function actualizar(string $id, array $datos): bool {
        try {
            $idMongo = new MongoDB\BSON\ObjectId($id);
            $set = [
                'motivo'         => $datos['motivo'],
                'tipo'           => $datos['tipo'],
                'estado'         => $datos['estado'],
                'actualizado_en' => new MongoDB\BSON\UTCDateTime()
            ];

            if (isset($datos['fecha_cita'])) {
                $set['fecha_cita'] = (string)$datos['fecha_cita'];
            }
            if (isset($datos['psicologo'])) {
                $set['psicologo'] = (string)$datos['psicologo'];
            }
            if (isset($datos['notas'])) {
                $set['notas'] = (string)$datos['notas'];
            }

            $this->coleccion->updateOne(
                ['_id' => $idMongo],
                ['$set' => $set]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function guardarNotasExpediente(string $id, array $notas): bool {
        try {
            $idMongo = new MongoDB\BSON\ObjectId($id);
            $fecha = new MongoDB\BSON\UTCDateTime();

            $this->coleccion->updateOne(
                ['_id' => $idMongo],
                [
                    '$set' => [
                        'expediente.observaciones'    => $notas['observaciones'] ?? '',
                        'expediente.sintomas'         => $notas['sintomas'] ?? '',
                        'expediente.plan_tratamiento' => $notas['plan_tratamiento'] ?? '',
                        'expediente.psicologo'        => $notas['psicologo'] ?? '',
                        'expediente.actualizado_en'   => $fecha,
                        'actualizado_en'              => $fecha,
                    ]
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('Error al guardar notas de expediente: ' . $e->getMessage());
            return false;
        }
    }
}
