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
    <!-- 1. Header Global Full-Width pegado al borde superior -->
    <?php require dirname(__DIR__) . '/layouts/header.php'; ?>

    <main class="dashboard-contenedor">
        <!-- Hero del Módulo B -->
        <div class="modulo-hero">
            <div class="modulo-hero-titulos">
                <h1>Expediente Clínico y Búsqueda de Citas</h1>
                <p>Consulta avanzada de pacientes, estado psicoterapéutico y acceso al expediente individual.</p>
            </div>
            <?php if (tiene_rol('admin')): ?>
                <a href="crear.php" class="btn btn-primary">+ Nueva Cita</a>
            <?php endif; ?>
        </div>

        <!-- Alertas flash del sistema -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success" role="status">
                <span>&#10003;</span>
                <span><?= e($_SESSION['mensaje_exito']) ?></span>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <!-- 2. Buscador Avanzado y Barra de Filtros -->
        <?php $rutaActual = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'citas.php')); ?>
        <section class="card" aria-labelledby="titulo-filtros">
            <div class="card-cabecera">
                <div class="card-info">
                    <span class="card-icono">&#128269;</span>
                    <div>
                        <h2 id="titulo-filtros" class="card-titulo">Buscador y Filtros Avanzados</h2>
                        <p class="card-subtitulo">Búsqueda reactiva por palabras clave, modalidad de terapia y estado</p>
                    </div>
                </div>
                <div class="contador-badge" id="contador" role="status">
                    Resultados: <strong><?= e((string)$total) ?></strong>
                </div>
            </div>

            <form id="filtros" action="<?= e($rutaActual) ?>" method="get" class="filtros-barra"
                  hx-get="<?= e($rutaActual) ?>" hx-target="#resultados" hx-swap="innerHTML"
                  hx-replace-url="true" hx-indicator="#cargando" hx-sync="this:replace"
                  hx-trigger="submit, change, keyup changed delay:400ms, reset delay:20ms">

                <div class="campo-busqueda">
                    <input type="search" name="q" maxlength="60"
                           value="<?= e($busqueda) ?>"
                           placeholder="Buscar por motivo clínico (ej. Ansiedad, Duelo, Pareja)..."
                           autocomplete="off">
                </div>

                <?php foreach ($campos as $campo => [$etiqueta, $opciones]): ?>
                    <div class="campo-filtro">
                        <select name="<?= e($campo) ?>" aria-label="<?= e($etiqueta) ?>">
                            <option value="">Todos los <?= strtolower(e($etiqueta)) ?></option>
                            <?php foreach ($opciones as $valor): ?>
                                <option value="<?= e($valor) ?>" <?= $seleccion[$campo] === $valor ? 'selected' : '' ?>>
                                    <?= e($valor) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                <div class="filtros-acciones">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                    <span id="cargando" class="htmx-indicator" role="status">Actualizando...</span>
                </div>
            </form>

            <p id="error-entrada" class="alert alert-danger" role="alert" hidden></p>
        </section>

        <!-- 3. Tabla de Resultados Dinámica con HTMX -->
        <section class="card" aria-labelledby="titulo-tabla">
            <div class="card-cabecera">
                <div class="card-info">
                    <span class="card-icono">&#128196;</span>
                    <div>
                        <h2 id="titulo-tabla" class="card-titulo">Expediente de Citas Psicológicas</h2>
                        <p class="card-subtitulo">Resultados sincronizados de la base de datos MongoDB</p>
                    </div>
                </div>
            </div>

            <div id="resultados" aria-live="polite">
                <?php require __DIR__ . '/_tabla.php'; ?>
            </div>
        </section>
    </main>

    <!-- Modal CSS/HTML5 Limpio de Confirmación de Eliminación -->
    <dialog id="modal-eliminar" class="modal-dialog">
        <div class="modal-cabecera modal-cabecera-peligro">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem; color: var(--danger); line-height: 1;">&#9888;</span>
                <h3 style="color: var(--danger); font-size: 1.15rem; margin: 0;">Confirmar Eliminación</h3>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="cerrarModalEliminar()" aria-label="Cerrar modal">&times;</button>
        </div>

        <div class="modal-cuerpo">
            <p style="margin: 0; font-size: 0.95rem; color: var(--text-main);">
                ¿Estás seguro de que deseas eliminar permanentemente la siguiente cita clínica?
            </p>
            <div class="modal-seccion" style="margin: 12px 0 6px;">
                <span class="modal-seccion-titulo">Paciente / Motivo</span>
                <p id="eliminar-motivo" style="font-weight: 700; color: var(--text-main); font-size: 1.05rem; margin: 0;"></p>
            </div>
            <p style="margin: 6px 0 0; font-size: 0.8rem; color: var(--text-muted);">
                Esta acción es irreversible y será registrada en la bitácora de auditoría.
            </p>
        </div>

        <div class="modal-pie">
            <form action="eliminar.php" method="POST" id="form-confirmar-eliminar" style="display: flex; gap: 10px; margin: 0;">
                <?= campo_csrf() ?>
                <input type="hidden" name="id" id="eliminar-id" value="">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="submit" class="btn btn-danger">&#128465; Sí, Eliminar Cita</button>
            </form>
        </div>
    </dialog>

    <script>
        function confirmarEliminar(id, motivo) {
            const modal = document.getElementById('modal-eliminar');
            if (!modal) return;
            document.getElementById('eliminar-id').value = id;
            document.getElementById('eliminar-motivo').textContent = motivo;
            modal.showModal();
        }

        function cerrarModalEliminar() {
            document.getElementById('modal-eliminar')?.close();
        }

        // Cerrar modal al hacer clic en el backdrop exterior
        document.getElementById('modal-eliminar')?.addEventListener('click', (e) => {
            const dialog = document.getElementById('modal-eliminar');
            const rect = dialog.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                                rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) dialog.close();
        });
    </script>
</body>
</html>
