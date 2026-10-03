<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <!-- Carga de la biblioteca HTMX exigida por la guía -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js" defer></script>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= htmlspecialchars($titulo) ?></h1>

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
                <a href="index.php" class="btn-link">Volver al CRUD original</a>
                <?php if (tiene_rol('admin')): ?>
                    <a class="boton" href="crear.php">Nueva cita</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Formulario configurado con los eventos y sincronización HTMX -->
        <form id="filtros" action="mvc.php" method="get" class="filtros"
              hx-get="mvc.php"
              hx-target="#resultados"
              hx-swap="innerHTML"
              hx-replace-url="true"
              hx-indicator="#cargando"
              hx-sync="this:replace"
              hx-trigger="submit, change, keyup changed delay:400ms, reset delay:20ms">

            <label class="campo">
                <span>Buscar por motivo</span>
                <input type="search" name="q" maxlength="60"
                       value="<?= htmlspecialchars($busqueda) ?>"
                       placeholder="Ejemplo: Ansiedad" autocomplete="off">
            </label>

            <?php foreach ($campos as $campo => [$etiqueta, $opciones]): ?>
                <label class="campo">
                    <span><?= htmlspecialchars($etiqueta) ?></span>
                    <select name="<?= htmlspecialchars($campo) ?>">
                        <option value="">Todos los valores</option>
                        <?php foreach ($opciones as $valor): ?>
                            <option value="<?= htmlspecialchars($valor) ?>" <?= $seleccion[$campo] === $valor ? 'selected' : '' ?>>
                                <?= htmlspecialchars($valor) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>

            <div class="botones">
                <button type="submit">Aplicar filtros</button>
                <button type="reset" class="btn-link">Limpiar</button>
                <span id="cargando" class="htmx-indicator" role="status">Actualizando...</span>
            </div>
        </form>

        <!-- Identificador #contador necesario para <hx-partial> -->
        <p id="contador" role="status">Resultados: <?= $total ?></p>

        <!-- Identificador #resultados donde se inyectará la vista parcial -->
        <section id="resultados" aria-live="polite">
            <?php require __DIR__ . '/_tabla.php'; ?>
        </section>
    </div>
</body>
</html>