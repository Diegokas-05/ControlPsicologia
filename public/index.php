<?php
require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';

$citaModel = new Cita();

// 1. Capturar los filtros de la URL por GET (Requisito R06)
$filtroTipo = $_GET['tipo'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';

// 2. Ejecutar la consulta en MongoDB
$citas = $citaModel->consultar($filtroTipo, $filtroEstado);
// Convertimos el resultado a un arreglo para poder contarlo
$citasArray = $citas->toArray(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Citas - ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <h1>Control de Citas</h1>
        <a href="crear.php" class="btn-link" style="font-weight: bold; font-size: 18px;">+ Registrar Nueva Cita</a>

        <!-- Formulario de Filtros (Requisito R06) -->
        <form action="index.php" method="GET" class="filtros">
            <label style="margin: 0;">Filtrar por:</label>
            
            <select name="tipo" style="width: auto;">
                <option value="">Todos los tipos</option>
                <!-- Conservamos el filtro visible marcándolo como 'selected' -->
                <option value="Individual" <?= $filtroTipo == 'Individual' ? 'selected' : '' ?>>Individual</option>
                <option value="Pareja" <?= $filtroTipo == 'Pareja' ? 'selected' : '' ?>>Pareja</option>
                <option value="Infantil" <?= $filtroTipo == 'Infantil' ? 'selected' : '' ?>>Infantil</option>
            </select>

            <select name="estado" style="width: auto;">
                <option value="">Todos los estados</option>
                <option value="Pendiente" <?= $filtroEstado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Completada" <?= $filtroEstado == 'Completada' ? 'selected' : '' ?>>Completada</option>
                <option value="Cancelada" <?= $filtroEstado == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
            
            <button type="submit" style="padding: 8px;">Aplicar</button>
            <a href="index.php" class="btn-link" style="margin: 0;">Limpiar</a>
        </form>

        <!-- Tabla de Resultados (Requisito R02) -->
        <?php if (count($citasArray) > 0): ?>
            <table>
                <tr>
                    <th>Motivo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <?php foreach ($citasArray as $cita): ?>
                <tr>
                    <!-- htmlspecialchars previene ataques XSS escapando el HTML (Requisito R05) -->
                    <td><?= htmlspecialchars($cita['motivo']) ?></td>
                    <td><?= htmlspecialchars($cita['tipo']) ?></td>
                    <td><?= htmlspecialchars($cita['estado']) ?></td>
                    <td>
                        <!-- Enlaces para Editar y Formulario POST para Eliminar -->
                        <a href="editar.php?id=<?= $cita['_id'] ?>">Editar</a> | 
                        
                        <form action="eliminar.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta cita?');">
                            <input type="hidden" name="id" value="<?= $cita['_id'] ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                No se encontraron citas con los criterios seleccionados.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>