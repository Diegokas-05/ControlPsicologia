<?php
declare(strict_types=1);

$usuarioActual = usuario_actual();
$scriptActual = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$esFlujo = ($scriptActual === 'flujo.php');
$esCitas = ($scriptActual === 'citas.php' || $scriptActual === 'mvc.php');
?>
<header class="navbar-global">
    <div class="navbar-interior">
        <!-- 1. Logo / Identidad Clínica -->
        <a href="flujo.php" class="navbar-marca" title="Panel Principal">
            <span class="navbar-icono" aria-hidden="true">&#9764;</span>
            <div class="navbar-textos">
                <span class="navbar-titulo">ControlPsicología</span>
                <span class="navbar-subtitulo">Clinical Management SaaS</span>
            </div>
        </a>

        <!-- 2. Navegación entre Módulos -->
        <nav class="navbar-links" aria-label="Navegación de módulos clínicos">
            <a href="flujo.php" class="nav-item <?= $esFlujo ? 'activo' : '' ?>">
                <span class="nav-item-icono">&#9881;</span>
                <span>Flujo Operativo</span>
            </a>
            <a href="citas.php" class="nav-item <?= $esCitas ? 'activo' : '' ?>">
                <span class="nav-item-icono">&#128196;</span>
                <span>Expediente de Citas</span>
            </a>
            <?php if (tiene_rol('admin')): ?>
                <a href="crear.php" class="nav-item <?= ($scriptActual === 'crear.php') ? 'activo' : '' ?>">
                    <span class="nav-item-icono">+</span>
                    <span>Nueva Cita</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- 3. Perfil del Usuario / Rol / Salir con CSRF -->
        <div class="navbar-usuario">
            <?php if ($usuarioActual !== null): ?>
                <div class="usuario-info">
                    <span class="usuario-nombre"><?= e($usuarioActual['nombre'] ?? '') ?></span>
                    <span class="usuario-rol usuario-rol-<?= e(strtolower((string)($usuarioActual['rol'] ?? ''))) ?>">
                        <?= e(strtoupper((string)($usuarioActual['rol'] ?? ''))) ?>
                    </span>
                </div>
                <form action="logout.php" method="post" class="form-logout">
                    <?= campo_csrf() ?>
                    <button type="submit" class="btn btn-sm btn-logout" title="Cerrar sesión de forma segura">
                        Salir
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</header>
