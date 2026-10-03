<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar - ControlPsicologia</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .acceso { min-height: 85vh; display: grid; place-items: center; font-family: sans-serif; }
        .acceso .tarjeta { width: min(100%, 430px); background: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .etiqueta { color: #075f5e; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; font-size: .8rem; }
        .error { background: #fee2e2; color: #991b1b; padding: .8rem 1rem; border-radius: .6rem; margin-bottom: 15px; }
        .aviso { background: #fef3c7; color: #92400e; padding: .8rem 1rem; border-radius: .6rem; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background: #075f5e; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
    </style>
</head>
<body>
    <main class="acceso">
        <form action="login.php" method="post" class="tarjeta">
            <p class="etiqueta">Acceso protegido</p>
            <h1 style="margin-top: 0;">Control Psicología</h1>
            
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