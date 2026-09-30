<?php

echo "<h1 style='font-family: sans-serif; border-bottom: 2px solid #28a745; padding-bottom: 10px;'>Prueba de Validación (P07) - ControlPsicologia</h1>";

// Función que aísla la lógica de validación para ser probada
function validarCita($motivo, $tipo, $estado) {
    $errores = [];

    if (empty(trim($motivo)) || strlen(trim($motivo)) > 100) {
        $errores[] = "El motivo es obligatorio y máximo de 100 caracteres.";
    }

    $tiposPermitidos = ['Individual', 'Pareja', 'Infantil'];
    if (!in_array($tipo, $tiposPermitidos)) {
        $errores[] = "Tipo de terapia no permitido.";
    }

    $estadosPermitidos = ['Pendiente', 'Completada', 'Cancelada'];
    if (!in_array($estado, $estadosPermitidos)) {
        $errores[] = "Estado no permitido.";
    }

    return empty($errores) ? "OK" : "FALLO: " . implode(" ", $errores);
}

// Casos de prueba exigidos por el requerimiento P07
$casos = [
    ['nombre' => '1. Datos válidos', 'motivo' => 'Ansiedad general', 'tipo' => 'Individual', 'estado' => 'Pendiente'],
    ['nombre' => '2. Campo obligatorio vacío', 'motivo' => '', 'tipo' => 'Pareja', 'estado' => 'Completada'],
    ['nombre' => '3. Texto demasiado largo', 'motivo' => str_repeat('A', 101), 'tipo' => 'Infantil', 'estado' => 'Pendiente'],
    ['nombre' => '4. Valor fuera de la lista permitida', 'motivo' => 'Estrés laboral', 'tipo' => 'Familiar', 'estado' => 'Pendiente'],
];

echo "<table border='1' cellpadding='12' style='border-collapse: collapse; font-family: sans-serif; width: 100%; max-width: 800px;'>";
echo "<tr style='background-color: #28a745; color: white;'><th>Caso de Prueba</th><th>Resultado Esperado</th><th>Resultado Obtenido</th></tr>";

foreach ($casos as $caso) {
    $resultado = validarCita($caso['motivo'], $caso['tipo'], $caso['estado']);
    $esperado = ($caso['nombre'] === '1. Datos válidos') ? 'OK' : 'FALLO';
    
    // Comprobación visual
    $color = (strpos($resultado, $esperado) !== false) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><strong>{$caso['nombre']}</strong><br><small>Motivo: '{$caso['motivo']}', Tipo: '{$caso['tipo']}', Estado: '{$caso['estado']}'</small></td>";
    echo "<td>$esperado</td>";
    echo "<td style='color: $color; font-weight: bold;'>$resultado</td>";
    echo "</tr>";
}
echo "</table>";
?>