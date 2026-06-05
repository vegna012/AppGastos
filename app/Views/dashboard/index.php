<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Bienvenido <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h1>

    <form method="post" action="/logout">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
