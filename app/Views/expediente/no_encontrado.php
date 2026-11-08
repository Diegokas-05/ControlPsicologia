<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php require dirname(__DIR__) . '/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
            <span style="font-size: 3rem; color: var(--danger);">&#9888;</span>
            <h1 style="margin: 1rem 0 0.5rem;">Expediente Clínico No Encontrado</h1>
            <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto 1.5rem;">
                El registro de la cita solicitada no existe o fue eliminado de la base de datos de MongoDB.
            </p>
            <a href="citas.php" class="btn btn-primary">&larr; Volver a Búsqueda y Citas</a>
        </div>
    </main>
</body>
</html>
