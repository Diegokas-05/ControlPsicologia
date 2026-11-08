<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Clínico — ControlPsicologia</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        :root {
            --bg-base: #f1f5f9;
            --brand-dark: #0f172a;
            --brand-primary: #0284c7;
            --brand-hover: #0369a1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: #e2e8f0;
        }

        body {
            margin: 0;
            background: radial-gradient(circle at 15% 15%, #e2e8f0 0%, #f1f5f9 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-main);
        }

        .contenedor-split {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            box-sizing: border-box;
        }

        .panel-doble {
            display: grid;
            grid-template-columns: 1fr;
            width: min(100%, 820px);
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        @media (min-width: 720px) {
            .panel-doble {
                grid-template-columns: 1fr 1.25fr;
            }
        }

        /* Panel Izquierdo: Institucional */
        .seccion-lateral {
            background: linear-gradient(155deg, #0b1329 0%, #1e293b 100%);
            color: #ffffff;
            padding: 3rem 2.2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .icono-clinico {
            width: 48px;
            height: 48px;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 14px;
            display: grid;
            place-items: center;
            margin-bottom: 1.8rem;
        }

        .seccion-lateral h2 {
            font-size: 1.5rem;
            margin: 0 0 0.75rem 0;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .seccion-lateral p {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }

        .detalles-seguridad {
            margin-top: 2.5rem;
            padding-top: 1.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.82rem;
            color: #cbd5e1;
        }

        .punto-activo {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 10px #10b981;
        }

        /* Panel Derecho: Formulario */
        .seccion-formulario {
            padding: 3rem 2.5rem;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .encabezado-form {
            margin-bottom: 2rem;
        }

        .badge-clinico {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .encabezado-form h1 {
            font-size: 1.5rem;
            margin: 0 0 0.3rem 0;
            color: var(--text-main);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .encabezado-form p {
            margin: 0;
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        /* Alertas */
        .alerta {
            padding: 0.8rem 1rem;
            border-radius: 10px;
            font-size: 0.88rem;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alerta-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
            border-left: 4px solid #ef4444;
        }

        .alerta-aviso {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        /* Campos con Iconos */
        .grupo-input {
            margin-bottom: 1.35rem;
        }

        .grupo-input label {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .input-contenedor {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-contenedor svg {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-contenedor input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border-soft);
            border-radius: 12px;
            font-size: 0.95rem;
            color: var(--text-main);
            background: #f8fafc;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .input-contenedor input:focus {
            outline: none;
            border-color: var(--brand-primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .input-contenedor input:focus + svg {
            color: var(--brand-primary);
        }

        /* Botón */
        .boton-acceso {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 13px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            font-size: 0.98rem;
            margin-top: 0.6rem;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            transition: all 0.2s ease;
        }

        .boton-acceso:hover {
            background: linear-gradient(180deg, #0369a1 0%, #075985 100%);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
            transform: translateY(-1px);
        }

        .boton-acceso:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body>
    <main class="contenedor-split">
        <div class="panel-doble">
            
            <!-- Panel Izquierdo -->
            <section class="seccion-lateral">
                <div>
                    <div class="icono-clinico" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h2>Control Psicología</h2>
                    <p>Módulo de gestión de expedientes clínicos y control de citas terapéuticas.</p>
                </div>

                <div class="detalles-seguridad">
                    <span class="punto-activo"></span>
                    <span>Acceso autenticado y protegido</span>
                </div>
            </section>

            <!-- Panel Derecho Formulario -->
            <section class="seccion-formulario">
                <header class="encabezado-form">
                    <span class="badge-clinico">Portal de Citas</span>
                    <h1>Iniciar Sesión</h1>
                    <p>Ingresa tus datos para acceder al sistema</p>
                </header>

                <?php if ($expirada): ?>
                    <div class="alerta alerta-aviso" role="alert">
                        <span>La sesión ha expirado por inactividad.</span>
                    </div>
                <?php endif; ?>

                <?php if ($error !== null): ?>
                    <div class="alerta alerta-error" role="alert">
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post" autocomplete="off">
                    <?= campo_csrf() ?>

                    <div class="grupo-input">
                        <label for="campo-correo">Correo electrónico</label>
                        <div class="input-contenedor">
                            <input id="campo-correo" type="email" name="correo" required maxlength="120" autocomplete="username" placeholder="usuario@psicologia.test">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                    </div>

                    <div class="grupo-input">
                        <label for="campo-clave">Contraseña</label>
                        <div class="input-contenedor">
                            <input id="campo-clave" type="password" name="clave" required autocomplete="current-password" placeholder="••••••••">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                    </div>

                    <button type="submit" class="boton-acceso">Entrar al Sistema</button>
                </form>
            </section>

        </div>
    </main>
</body>
</html>