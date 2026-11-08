<?php
declare(strict_types=1);

final class ResumenGo
{
    public function calcular(array $grupos): array
    {
        $ruta = dirname(__DIR__, 2) . '/.local/servicio-go.key';
        if (!is_readable($ruta)) {
            throw new RuntimeException('Falta la clave local.');
        }

        $clave = trim((string) file_get_contents($ruta));
        $ch = curl_init('http://127.0.0.1:8081/resumen');

        if ($ch === false || strlen($clave) < 32) {
            throw new RuntimeException('Servicio sin configurar.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS => 1500,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Service-Key: ' . $clave,
            ],
            CURLOPT_POSTFIELDS => json_encode(
                ['grupos' => $grupos], JSON_THROW_ON_ERROR
            ),
        ]);

        $texto = curl_exec($ch);
        $estado = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        unset($ch);

        if ($texto === false || $estado !== 200) {
            throw new RuntimeException('Servicio no disponible.');
        }

        $datos = json_decode($texto, true, 32, JSON_THROW_ON_ERROR);

        if (!is_array($datos) || !is_int($datos['total'] ?? null)
            || $datos['total'] < 0 || !is_array($datos['grupos'] ?? null)) {
            throw new RuntimeException('Respuesta no valida.');
        }

        foreach ($datos['grupos'] as $fila) {
            if (!is_array($fila) || !is_string($fila['etiqueta'] ?? null)
                || !is_int($fila['cantidad'] ?? null)
                || !is_numeric($fila['porcentaje'] ?? null)) {
                throw new RuntimeException('Fila no valida.');
            }
        }
        return $datos;
    }
}