<?php
session_start();

if (isset($_SESSION["usuario_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechSolutions | Iniciar sesión</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">TS</div>

        <h1>TechSolutions</h1>

        <p class="subtitle">
            Sistema de Inventarios y Pedidos
        </p>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form action="auth/login.php" method="POST">

            <div class="form-group">
                <label>Correo electrónico</label>
                <input
                    type="email"
                    name="correo"
                    placeholder="Ingresa tu correo"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Iniciar sesión
            </button>

        </form>

        <div class="footer">
            © 2026 TechSolutions
        </div>

    </div>

</div>

</body>
</html>
