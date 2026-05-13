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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, username, display_name, password, is_admin, avatar FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['display_name'] = $user['display_name'];
            $_SESSION['is_admin']     = $user['is_admin'];
            $_SESSION['avatar']       = $user['avatar'] ?? '👤';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Completá todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appTitle) ?> — Ingresar</title>
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
            <p>FIFA World Cup 2026</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="Tu nombre de usuario"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Ingresar</button>
        </form>

        <div class="auth-footer">
            ¿No tenés cuenta? <a href="register.php">Registrate acá</a>
        </div>
    </div>
</body>
</html>
