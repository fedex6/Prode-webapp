<?php
require_once 'auth.php';
require_once 'db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$appTitle = getSetting('app_title', 'Prode Mundial 2026');
$primaryColor = getSetting('primary_color', '#2964a0');
$primaryColorDark = getSetting('primary_color_dark', '#153c8f');
$primaryColorLight = getSetting('primary_color_light', '#e8ecf5');
$accentColor = getSetting('accent_color', '#f59e0b');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $password     = $_POST['password'] ?? '';
    $password2    = $_POST['password2'] ?? '';

    if (!$username || !$display_name || !$password) {
        $error = 'Completá todos los campos.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'El usuario debe tener entre 3 y 20 caracteres (letras, números y guión bajo).';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Ese nombre de usuario ya está en uso.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare('INSERT INTO users (username, display_name, password) VALUES (?, ?, ?)')
               ->execute([$username, $display_name, $hash]);
            $success = '¡Cuenta creada! Ya podés <a href="index.php">ingresar</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appTitle) ?> — Registrarse</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --app-primary: <?= htmlspecialchars($primaryColor) ?>;
            --app-primary-d: <?= htmlspecialchars($primaryColorDark) ?>;
            --app-primary-l: <?= htmlspecialchars($primaryColorLight) ?>;
            --app-accent: <?= htmlspecialchars($accentColor) ?>;
        }
        .auth-page { background: linear-gradient(135deg, #2563eb 0%, var(--app-primary) 50%, var(--app-primary-d) 100%); }
        .auth-logo h1 { color: var(--app-primary-d); }
        .auth-logo h1 span { color: var(--app-accent); }
        .btn-primary { background: var(--app-primary); }
        .btn-primary:hover { background: var(--app-primary-d); }
        input:focus { border-color: var(--app-primary); box-shadow: 0 0 0 3px rgba(26,122,74,.15); }
    </style>
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="trophy">🏆</span>
            <h1><?= htmlspecialchars($appTitle) ?></h1>
            <p>Creá tu cuenta</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="display_name">Nombre para mostrar</label>
                <input type="text" id="display_name" name="display_name" placeholder="Ej: Tomy el 10"
                       value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="username">Usuario <small>(solo letras, números y _)</small></label>
                <input type="text" id="username" name="username" placeholder="Ej: tomy_gol"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required pattern="[a-zA-Z0-9_]{3,20}">
            </div>
            <div class="form-group">
                <label for="password">Contraseña <small>(mín. 6 caracteres)</small></label>
                <input type="password" id="password" name="password" placeholder="Tu contraseña" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password2">Repetir contraseña</label>
                <input type="password" id="password2" name="password2" placeholder="Repetí tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Crear cuenta</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            ¿Ya tenés cuenta? <a href="index.php">Ingresá acá</a>
        </div>
    </div>
</body>
</html>
