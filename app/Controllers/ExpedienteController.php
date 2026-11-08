<?php
declare(strict_types=1);

final class ExpedienteController
{
    private Cita $modeloCita;

    public function __construct(Cita $modeloCita)
    {
        $this->modeloCita = $modeloCita;
    }

    public function ver(string $id): void
    {
        $cita = $this->modeloCita->obtenerPorId($id);
        if ($cita === null) {
            http_response_code(404);
            $titulo = 'Expediente No Encontrado';
            require dirname(__DIR__) . '/Views/expediente/no_encontrado.php';
            return;
        }

        $titulo = 'Expediente Clínico — ' . ($cita['motivo'] ?? 'Paciente');
        require dirname(__DIR__) . '/Views/expediente/detalle.php';
    }

    public function guardarNotas(string $id, array $datos): void
    {
        validar_csrf();

        entrada_campos($datos, ['id', 'csrf', 'observaciones', 'sintomas', 'plan_tratamiento']);

        $observaciones   = entrada_texto($datos, 'observaciones', 0, 2000);
        $sintomas        = entrada_texto($datos, 'sintomas', 0, 1000);
        $planTratamiento = entrada_texto($datos, 'plan_tratamiento', 0, 2000);

        $usuario = usuario_actual();
        $psicologo = (string)($usuario['nombre'] ?? 'Psicólogo tratante');

        $this->modeloCita->guardarNotasExpediente($id, [
            'observaciones'    => $observaciones,
            'sintomas'         => $sintomas,
            'plan_tratamiento' => $planTratamiento,
            'psicologo'        => $psicologo,
        ]);

        $_SESSION['mensaje_exito'] = 'Las notas de evolución psicológica fueron guardadas exitosamente.';
        header('Location: expediente.php?id=' . urlencode($id), true, 303);
        exit;
    }
}
