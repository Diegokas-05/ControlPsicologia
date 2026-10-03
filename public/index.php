<?php
require_once '../vendor/autoload.php';
require_once '../app/models/Cita.php';
require_once '../app/Security/Sesion.php';
require_once '../app/Security/Csrf.php';

iniciar_sesion_segura();
exigir_login(); // Protege el listado[cite: 69]

$citaModel = new Cita();

// 1. Capturar los filtros de la URL por GET
$filtroTipo = $_GET['tipo'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';

// 2. Ejecutar la consulta en MongoDB
$citas = $citaModel->consultar($filtroTipo, $filtroEstado);
$citasArray = $citas->toArray(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUD Original - ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Listado CRUD Clásico</h1>

            <!-- Alerta de éxito dinámica -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success" role="status">
                    <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <?php $actual = usuario_actual(); ?>
            <div class="usuario">
                <span class="nombre">
                    <?= htmlspecialchars($actual['nombre'] ?? '') ?>
                    (Rol: <?= htmlspecialchars($actual['rol'] ?? '') ?>)
                </span>
                <form action="logout.php" method="post">
                    <?= campo_csrf() ?>
                    <button type="submit" class="salir">Salir</button>
                </form>
            </div>

            <div class="acciones">
                <a href="mvc.php" class="btn-link">Ir a la versión HTMX (MVC)</a>
                <?php if (tiene_rol('admin')): ?>
                    <a class="boton" href="crear.php">Nueva cita</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Formulario de Filtros Clásico -->
        <form action="index.php" method="GET" class="filtros">
            <label class="campo">
                <span>Tipo de Terapia:</span>
                <select name="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="Individual" <?= $filtroTipo == 'Individual' ? 'selected' : '' ?>>Individual</option>
                    <option value="Pareja" <?= $filtroTipo == 'Pareja' ? 'selected' : '' ?>>Pareja</option>
                    <option value="Infantil" <?= $filtroTipo == 'Infantil' ? 'selected' : '' ?>>Infantil</option>
                </select>
            </label>

            <label class="campo">
                <span>Estado:</span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente" <?= $filtroEstado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Completada" <?= $filtroEstado == 'Completada' ? 'selected' : '' ?>>Completada</option>
                    <option value="Cancelada" <?= $filtroEstado == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </label>
            
            <div class="botones">
                <button type="submit">Aplicar</button>
                <a href="index.php" class="btn-link">Limpiar</a>
            </div>
        </form>

        <!-- Tabla de Resultados -->
        <?php if (count($citasArray) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Motivo</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citasArray as $cita): ?>
                    <tr>
                        <td><?= htmlspecialchars($cita['motivo']) ?></td>
                        <td><?= htmlspecialchars($cita['tipo']) ?></td>
                        <td><?= htmlspecialchars($cita['estado']) ?></td>
                        <td>
                            <?php if (tiene_rol('admin')): ?>
                                <a href="editar.php?id=<?= htmlspecialchars((string)$cita['_id']) ?>" class="enlace" style="margin-right: 10px;">Editar</a>
                                <form action="eliminar.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta cita?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$cita['_id']) ?>">
                                    <?= campo_csrf() ?> <!-- OBLIGATORIO: Token en el botón eliminar[cite: 70] -->
                                    <button type="submit" class="boton btn-eliminar" style="padding: 4px 8px; min-height: 0;">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #6c757d; font-size: 0.9em; font-style: italic;">Solo lectura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                No se encontraron citas con los criterios seleccionados.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>