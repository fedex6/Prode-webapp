<?php
require_once 'auth.php';
require_once 'db.php';
requireLogin();

$user = currentUser();
$db   = getDB();
$appTitle = getSetting('app_title', 'Prode Mundial 2026');
$primaryColor = getSetting('primary_color', '#2964a0');
$primaryColorDark = getSetting('primary_color_dark', '#153c8f');
$primaryColorLight = getSetting('primary_color_light', '#e8ecf5');
$accentColor = getSetting('accent_color', '#f59e0b');

// Obtener datos actuales del usuario
$stmt = $db->prepare('SELECT id, username, display_name, avatar FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $displayName = trim($_POST['display_name'] ?? '');

        if (strlen($displayName) < 3) {
            $error = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (strlen($displayName) > 100) {
            $error = 'El nombre no puede superar 100 caracteres.';
        } else {
            $db->prepare('UPDATE users SET display_name = ? WHERE id = ?')
                ->execute([$displayName, $user['id']]);

            $_SESSION['display_name'] = $displayName;
            $userData['display_name'] = $displayName;
            $success = '✅ Nombre actualizado correctamente.';
        }
    } elseif ($action === 'update_avatar') {
        $avatar = trim($_POST['avatar'] ?? '');

        if (strlen($avatar) < 1) {
            $error = 'Debe seleccionar o escribir un emoji.';
        } elseif (strlen($avatar) > 10) {
            $error = 'El emoji no puede ser tan largo.';
        } else {
            $db->prepare('UPDATE users SET avatar = ? WHERE id = ?')
                ->execute([$avatar, $user['id']]);

            $_SESSION['avatar'] = $avatar;
            $userData['avatar'] = $avatar;
            $success = '✅ Avatar actualizado correctamente.';
        }
    } elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validar contraseña actual
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $storedHash = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $storedHash)) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $db->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([$hashedPassword, $user['id']]);

            $success = '✅ Contraseña actualizada correctamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?= htmlspecialchars($appTitle) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --app-primary: <?= htmlspecialchars($primaryColor) ?>;
            --app-primary-d: <?= htmlspecialchars($primaryColorDark) ?>;
            --app-primary-l: <?= htmlspecialchars($primaryColorLight) ?>;
            --app-accent: <?= htmlspecialchars($accentColor) ?>;
        }
        .site-header { background: var(--app-primary-d); }
        .site-logo { color: var(--white); }
        .site-logo span { color: var(--app-accent); }
        .btn-primary { background: var(--app-primary); }
        .btn-primary:hover { background: var(--app-primary-d); }
        .profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            flex-shrink: 0;
        }

        .profile-info h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            color: #333;
        }

        .profile-info p {
            margin: 0;
            color: #999;
            font-size: 0.9rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
            color: #333;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-submit {
            flex: 1;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .btn-back {
            flex: 1;
            padding: 0.75rem 1.5rem;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .help-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.3rem;
        }

        #avatar {
            font-size: 1.5rem;
            text-align: center;
            font-family: 'Segoe UI Symbol', 'Apple Color Emoji', sans-serif;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="dashboard.php" class="site-logo">🏆 <?= htmlspecialchars($appTitle) ?></a>
            <nav class="site-nav">
                <a href="leaderboard.php">🏅 Tabla</a>
                <?php if ($user['is_admin']): ?>
                <a href="admin.php">⚙️ Admin</a>
                <?php endif; ?>
                <span class="nav-user">👤 <?= htmlspecialchars($user['display_name']) ?></span>
                <a href="logout.php" class="btn-logout">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar"><?= htmlspecialchars($userData['avatar'] ?? '👤') ?></div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($userData['display_name']) ?></h2>
                        <p>@<?= htmlspecialchars($userData['username']) ?></p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Actualizar Nombre -->
                <div class="form-section">
                    <h3>📝 Nombre de Perfil</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label for="display_name">Nombre para mostrar</label>
                            <input type="text" id="display_name" name="display_name"
                                   value="<?= htmlspecialchars($userData['display_name']) ?>"
                                   maxlength="100" required>
                            <div class="help-text">Entre 3 y 100 caracteres</div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">💾 Guardar Cambios</button>
                        </div>
                    </form>
                </div>

                <!-- Actualizar Avatar -->
                <div class="form-section">
                    <h3>🎨 Avatar</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_avatar">
                        <div class="form-group">
                            <label for="avatar">Elige o escribe tu emoji</label>
                            <input type="text" id="avatar" name="avatar"
                                   value="<?= htmlspecialchars($userData['avatar'] ?? '👤') ?>"
                                   maxlength="10" required placeholder="⚽">
                            <div class="help-text">Ejemplos: ⚽ 🏀 🎮 🚀 ⭐ 💪 🔥 😎 🎯 🏆</div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">💾 Guardar Avatar</button>
                        </div>
                    </form>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="form-section">
                    <h3>🔐 Cambiar Contraseña</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="form-group">
                            <label for="current_password">Contraseña Actual</label>
                            <input type="password" id="current_password" name="current_password"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña</label>
                            <input type="password" id="new_password" name="new_password"
                                   required>
                            <div class="help-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Contraseña</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">🔐 Cambiar Contraseña</button>
                        </div>
                    </form>
                </div>

                <!-- Acciones -->
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
