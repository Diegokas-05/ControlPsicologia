<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar - ControlPsicologia</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .acceso { min-height: 85vh; display: grid; place-items: center; }
        .acceso .tarjeta { width: min(100%, 430px); }
        .etiqueta { color: #075f5e; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; font-size: .8rem; }
        .error, .aviso { padding: .8rem 1rem; border-radius: .6rem; }
        .error { background: #fee2e2; color: #991b1b; }
        .aviso { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <main class="acceso">
        <form action="login.php" method="post" class="tarjeta">
            <p class="etiqueta">Acceso protegido</p>
            <h1>Control Psicología</h1>
            
            <?php if ($expirada): ?>
                <p class="aviso">La sesión venció por inactividad.</p>
            <?php endif; ?>
            
            <?php if ($error !== null): ?>
                <p class="error" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            
            <?= campo_csrf() ?>
            
            <div class="form-group">
                <label>Correo
                    <input type="email" name="correo" required maxlength="120" autocomplete="username">
                </label>
            </div>
            
            <div class="form-group">
                <label>Contraseña
                    <input type="password" name="clave" required autocomplete="current-password">
                </label>
            </div>
            
            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>