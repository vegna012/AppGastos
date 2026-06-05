<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
</head>
<body>
    <h1>Iniciar sesión</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="/login">
        <div>
            <label for="correo">Correo</label><br>
            <input type="email" id="correo" name="correo" required autocomplete="username">
        </div>
        <br>
        <div>
            <label for="password">Contraseña</label><br>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
