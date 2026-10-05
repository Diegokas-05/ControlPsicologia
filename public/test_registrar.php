<?php


declare(strict_types=1);


error_reporting(E_ALL);
ini_set('display_errors', '1');

$raiz = dirname(__DIR__);
require_once $raiz . '/app/Security/Sesion.php';

echo "<h3>Test de registrar_bitacora()</h3>";

try {
    registrar_bitacora('test.directo', [
        'actor_id' => 'prueba123',
        'resultado' => 'test',
    ]);
    echo "<p style='color:green'>✅ registrar_bitacora() se ejecutó sin excepción</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Verificar cuántos documentos hay
require_once $raiz . '/config/Database.php';
$db = (new Database())->conectar();
$col = $db->bitacora;
echo "<p>Total documentos en bitacora: " . $col->countDocuments() . "</p>";

echo "<h4>Últimos 5 eventos:</h4><pre>";
foreach ($col->find([], ['limit' => 5, 'sort' => ['creado_en' => -1]]) as $doc) {
    echo $doc['evento'] . " - " . ($doc['creado_en'] ?? 'sin fecha') . "\n";
}
echo "</pre>";