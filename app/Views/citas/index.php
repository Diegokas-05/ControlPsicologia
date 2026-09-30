<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="css/estilos.css">
    <!-- Carga de la biblioteca HTMX exigida por la guía -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js" defer></script>
    <style>
        .htmx-indicator { opacity: 0; transition: opacity 180ms ease-in; }
        .htmx-request .htmx-indicator, .htmx-request.htmx-indicator { opacity: 1; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <header>
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <?php $actual = usuario_actual(); ?>
            <div class="usuario" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                <span style="font-weight: bold; color: #075f5e;">
                    <?= htmlspecialchars($actual['nombre'] ?? '') ?> 
                    (Rol: <?= htmlspecialchars($actual['rol'] ?? '') ?>)
                </span>
                <form action="logout.php" method="post" style="margin: 0; display: inline;">
                    <?= campo_csrf() ?>
                    <button type="submit" style="border: none; background: transparent; cursor: pointer; text-decoration: underline; color: #d9534f; font-weight: bold;">Salir</button>
                </form>
            </div>
            <a href="index.php" class="btn-link">Volver al CRUD original</a>
        </header>

        <!-- Formulario configurado con los eventos y sincronización HTMX -->
        <form id="filtros" action="mvc.php" method="get" class="filtros" style="margin-top: 20px; flex-wrap: wrap;"
              hx-get="mvc.php" 
              hx-target="#resultados" 
              hx-swap="innerHTML" 
              hx-replace-url="true" 
              hx-indicator="#cargando" 
              hx-sync="this:replace" 
              hx-trigger="submit, change, keyup changed delay:400ms, reset delay:20ms">
            
            <label style="margin-right: 15px; font-weight: bold;">
                Buscar por motivo
                <input type="search" name="q" maxlength="60" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ejemplo: Ansiedad" autocomplete="off" style="padding: 5px; margin-left: 5px; border-radius: 4px; border: 1px solid #ccc;">
            </label>

            <?php foreach ($campos as $campo => [$etiqueta, $opciones]): ?>
                <label style="margin-right: 15px; font-weight: bold;">
                    <?= htmlspecialchars($etiqueta) ?>
                    <select name="<?= htmlspecialchars($campo) ?>" style="width: auto; margin-left: 5px;">
                        <option value="">Todos los valores</option>
                        <?php foreach ($opciones as $valor): ?>
                            <option value="<?= htmlspecialchars($valor) ?>" <?= $seleccion[$campo] === $valor ? 'selected' : '' ?>>
                                <?= htmlspecialchars($valor) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
            
            <button type="submit" style="padding: 8px 15px;">Aplicar filtros</button>
            <button type="reset" class="btn-link" style="margin-left: 15px; border: none; background: transparent; cursor: pointer;">Limpiar</button>
            
            <span id="cargando" class="htmx-indicator" role="status" style="margin-left: 15px; color: #007bff;">Actualizando...</span>
        </form>

        <!-- Identificador #contador necesario para <hx-partial> -->
        <p id="contador" role="status" style="font-weight: bold; margin-top: 20px;">Resultados: <?= $total ?></p>

        <!-- Identificador #resultados donde se inyectará la vista parcial -->
        <section id="resultados" aria-live="polite">
            <?php require __DIR__ . '/_tabla.php'; ?>
        </section>
    </div>
</body>
</html>