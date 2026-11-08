<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <!-- Carga de la biblioteca HTMX exigida por la arquitectura -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js" defer></script>
</head>
<body>
    <!-- Header Global Full-Width pegado al borde superior -->
    <?php require dirname(__DIR__) . '/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <!-- Hero del Módulo A -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <h1>Dashboard de Trabajo Operativo</h1>
                <p>Gestión del flujo clínico y métricas porcentuales calculadas en tiempo real por el servicio Go.</p>
            </div>
            <?php if (tiene_rol('admin')): ?>
                <a href="crear.php" class="btn btn-primary">
                    + Registrar Nueva Cita
                </a>
            <?php endif; ?>
        </div>

        <!-- Alertas flash de éxito -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success" role="status">
                <span>&#10003;</span>
                <span><?= e($_SESSION['mensaje_exito']) ?></span>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <!-- Grid Horizontal Pulcro de 2 Columnas (Bandeja + Resumen Go) -->
        <div class="dashboard-grid">
            <!-- Columna 1 (Izquierda): Bandeja de Trabajo con HTMX -->
            <section class="card" aria-labelledby="titulo-bandeja">
                <div class="card-cabecera">
                    <div class="card-info">
                        <span class="card-icono">&#9881;</span>
                        <div>
                            <h2 id="titulo-bandeja" class="card-titulo">Bandeja de Trabajo</h2>
                            <p class="card-subtitulo">Transición interactiva de estados de citas clínicas</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <select id="estado-flujo" name="estado" class="select-sm" aria-label="Filtrar por estado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary"
                                hx-get="bandeja.php"
                                hx-include="#estado-flujo"
                                hx-target="#bandeja-flujo">
                            Actualizar
                        </button>
                    </div>
                </div>

                <div id="mensaje-flujo" aria-live="polite"></div>

                <div id="bandeja-flujo" class="panel-scroll"
                     hx-get="bandeja.php"
                     hx-trigger="load, change from:#estado-flujo, flujoActualizado from:body"
                     hx-include="#estado-flujo"
                     aria-live="polite">
                    <p style="color: var(--text-muted); font-style: italic;">Cargando citas de la bandeja...</p>
                </div>
            </section>

            <!-- Columna 2 (Derecha): Resumen Estadístico (Servicio Go) -->
            <section class="card" aria-labelledby="titulo-resumen">
                <div class="card-cabecera">
                    <div class="card-info">
                        <span class="card-icono">&#128202;</span>
                        <div>
                            <h2 id="titulo-resumen" class="card-titulo">Métricas en Tiempo Real</h2>
                            <p class="card-subtitulo">Microservicio Go (Puerto 8081 con clave HMAC)</p>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary"
                            hx-get="resumen.php"
                            hx-target="#resumen-go"
                            hx-swap="innerHTML"
                            hx-disabled-elt="this">
                        Recalcular
                    </button>
                </div>

                <div id="resumen-go" class="panel-scroll" aria-live="polite"
                     hx-get="resumen.php"
                     hx-trigger="load, flujoActualizado from:body"
                     hx-swap="innerHTML">
                    <p style="color: var(--text-muted); font-style: italic;">Conectando con el microservicio Go...</p>
                </div>
            </section>
        </div>
    </main>

    <script src="resumen-ui.js"></script>
    <script src="flujo-ui.js" defer></script>
</body>
</html>
