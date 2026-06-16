<?php
require_once 'auth.php';
require_once 'db.php';
requireAdmin();

$db      = getDB();
$user    = currentUser();
$message = '';
$error   = '';

// === AUTO-UPDATER ===
define('CURRENT_VERSION', trim(@file_get_contents(__DIR__ . '/version.txt') ?: 'v0.0'));
define('GITHUB_REPO', 'fedex6/Prode-webapp');

// Archivos que NUNCA se sobreescriben (contienen configuración local)
define('UPDATE_SKIP_FILES', ['db.php', 'version.txt', '.htaccess']);

function checkLatestRelease(): ?array {
    $cache = sys_get_temp_dir() . '/prode_update_cache.json';
    if (file_exists($cache) && (time() - filemtime($cache)) < 3600) {
        return json_decode(file_get_contents($cache), true) ?: null;
    }
    $ctx = stream_context_create(['http' => [
        'timeout' => 5,
        'header'  => "User-Agent: ProdeWebapp-Updater/1.0\r\n",
    ]]);
    $json = @file_get_contents('https://api.github.com/repos/' . GITHUB_REPO . '/releases/latest', false, $ctx);
    if (!$json) return null;
    $data = json_decode($json, true);
    if (empty($data['tag_name'])) return null;
    $result = [
        'tag'       => $data['tag_name'],
        'name'      => $data['name'] ?? $data['tag_name'],
        'zipball'   => $data['zipball_url'],
        'published' => $data['published_at'] ?? '',
        'body'      => $data['body'] ?? '',
        'url'       => $data['html_url'] ?? '',
    ];
    file_put_contents($cache, json_encode($result));
    return $result;
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

// Forzar re-chequeo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'check_update') {
    @unlink(sys_get_temp_dir() . '/prode_update_cache.json');
    header('Location: admin.php#update-section');
    exit;
}

// Ejecutar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'do_update') {
    $release = checkLatestRelease();
    if (!$release) {
        $error = 'No se pudo conectar con GitHub para obtener la última versión.';
    } elseif ($release['tag'] === CURRENT_VERSION) {
        $message = 'Ya tenés la última versión instalada (' . CURRENT_VERSION . ').';
    } else {
        $tmpZip = sys_get_temp_dir() . '/prode_update.zip';
        $tmpDir = sys_get_temp_dir() . '/prode_update_extract';

        $ctx = stream_context_create(['http' => [
            'timeout'         => 60,
            'header'          => "User-Agent: ProdeWebapp-Updater/1.0\r\nAuthorization: \r\n",
            'follow_location' => 1,
            'max_redirects'   => 5,
        ]]);
        $zipData = @file_get_contents($release['zipball'], false, $ctx);

        if (!$zipData || !file_put_contents($tmpZip, $zipData)) {
            $error = 'No se pudo descargar el archivo de actualización desde GitHub.';
        } else {
            $zip = new ZipArchive();
            if ($zip->open($tmpZip) !== true) {
                $error = 'No se pudo abrir el archivo ZIP descargado.';
            } else {
                rrmdir($tmpDir);
                mkdir($tmpDir, 0755, true);
                $zip->extractTo($tmpDir);
                $zip->close();
                @unlink($tmpZip);

                // GitHub genera una carpeta raíz tipo "fedex6-Prode-webapp-abc1234/"
                $subdirs = glob($tmpDir . '/*', GLOB_ONLYDIR);
                if (empty($subdirs)) {
                    $error = 'Estructura del ZIP inesperada.';
                } else {
                    $srcDir = rtrim($subdirs[0], '/') . '/';
                    $skip   = UPDATE_SKIP_FILES;
                    $copied = 0;

                    $iter = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach ($iter as $item) {
                        $rel  = substr($item->getPathname(), strlen($srcDir));
                        if (in_array($rel, $skip, true)) continue;

                        $dest = __DIR__ . '/' . $rel;
                        if ($item->isDir()) {
                            if (!is_dir($dest)) mkdir($dest, 0755, true);
                        } else {
                            copy($item->getPathname(), $dest);
                            $copied++;
                        }
                    }

                    rrmdir($tmpDir);
                    file_put_contents(__DIR__ . '/version.txt', $release['tag'] . "\n");
                    @unlink(sys_get_temp_dir() . '/prode_update_cache.json');

                    $message = "App actualizada a {$release['tag']} correctamente ({$copied} archivos actualizados).";
                }
            }
        }
    }
}

$latestRelease = checkLatestRelease();
$hasUpdate     = $latestRelease && $latestRelease['tag'] !== CURRENT_VERSION;

// Cargar resultado real de un partido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_result') {
    $matchId   = (int)$_POST['match_id'];
    $homeScore = (int)$_POST['home_score'];
    $awayScore = (int)$_POST['away_score'];

    if ($matchId && $homeScore >= 0 && $awayScore >= 0) {
        // Guardar resultado
        $db->prepare('UPDATE matches SET home_score = ?, away_score = ?, is_finished = 1 WHERE id = ?')
           ->execute([$homeScore, $awayScore, $matchId]);

        // Calcular puntos para cada predicción de este partido
        $preds = $db->prepare('SELECT id, home_score, away_score FROM predictions WHERE match_id = ?');
        $preds->execute([$matchId]);

        $realHome = $homeScore;
        $realAway = $awayScore;
        $realResult = $realHome <=> $realAway; // -1, 0, 1

        foreach ($preds->fetchAll() as $pred) {
            $predResult = $pred['home_score'] <=> $pred['away_score'];
            if ($pred['home_score'] == $realHome && $pred['away_score'] == $realAway) {
                $points = 3;
            } elseif ($predResult === $realResult) {
                $points = 1;
            } else {
                $points = 0;
            }
            $db->prepare('UPDATE predictions SET points = ?, scored = 1 WHERE id = ?')
               ->execute([$points, $pred['id']]);
        }

        $message = 'Resultado cargado y puntos calculados correctamente.';
    } else {
        $error = 'Datos inválidos.';
    }
}

// Agregar nuevo partido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_match') {
    $home  = trim($_POST['home_team'] ?? '');
    $away  = trim($_POST['away_team'] ?? '');
    $hflag = trim($_POST['home_flag'] ?? '');
    $aflag = trim($_POST['away_flag'] ?? '');
    $date  = $_POST['match_date'] ?? '';
    $stage = trim($_POST['stage'] ?? 'Fase de Grupos');
    $group = trim($_POST['group_name'] ?? '');
    $venue = trim($_POST['venue'] ?? '');

    if ($home && $away && $date) {
        $db->prepare('INSERT INTO matches (home_team, away_team, home_flag, away_flag, match_date, stage, group_name, venue)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
           ->execute([$home, $away, $hflag, $aflag, $date, $stage, $group ?: null, $venue]);
        $message = 'Partido agregado correctamente.';
    } else {
        $error = 'Completá los campos obligatorios del partido.';
    }
}

// Eliminar resultado (re-abrir partido)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reopen_match') {
    $matchId = (int)$_POST['match_id'];
    $db->prepare('UPDATE matches SET home_score = NULL, away_score = NULL, is_finished = 0 WHERE id = ?')
       ->execute([$matchId]);
    $db->prepare('UPDATE predictions SET points = 0, scored = 0 WHERE match_id = ?')
       ->execute([$matchId]);
    $message = 'Partido reabierto y puntos revertidos.';
}

// Actualizar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_settings') {
    $appTitle = trim($_POST['app_title'] ?? '');
    $primaryColor = trim($_POST['primary_color'] ?? '');
    $primaryColorDark = trim($_POST['primary_color_dark'] ?? '');
    $primaryColorLight = trim($_POST['primary_color_light'] ?? '');
    $accentColor = trim($_POST['accent_color'] ?? '');

    if ($appTitle && $primaryColor && $primaryColorDark && $primaryColorLight && $accentColor) {
        updateSetting('app_title', $appTitle);
        updateSetting('primary_color', $primaryColor);
        updateSetting('primary_color_dark', $primaryColorDark);
        updateSetting('primary_color_light', $primaryColorLight);
        updateSetting('accent_color', $accentColor);
        $message = 'Configuración actualizada correctamente.';
    } else {
        $error = 'Completá todos los campos de configuración.';
    }
}

// Cargar configuración actual
$appTitle = getSetting('app_title', 'Prode Mundial 2026');
$primaryColor = getSetting('primary_color', '#2964a0');
$primaryColorDark = getSetting('primary_color_dark', '#153c8f');
$primaryColorLight = getSetting('primary_color_light', '#e8ecf5');
$accentColor = getSetting('accent_color', '#f59e0b');

$matches = $db->query('SELECT * FROM matches ORDER BY match_date ASC')->fetchAll();

// Estadísticas rápidas
$stats = $db->query('
    SELECT
        (SELECT COUNT(DISTINCT user_id) FROM predictions) as users_count,
        (SELECT COUNT(*) FROM matches) as matches_count,
        (SELECT COUNT(*) FROM matches WHERE is_finished = 1) as finished_count,
        (SELECT COUNT(*) FROM predictions) as predictions_count
')->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= htmlspecialchars($appTitle) ?></title>
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
        .admin-section h3 { color: var(--app-primary-d); }
        .section-title { color: var(--app-primary-d); border-bottom-color: var(--app-primary-l); }
        .stat-card span { color: var(--app-primary-d); }
        .btn-primary { background: var(--app-primary); }
        .btn-primary:hover { background: var(--app-primary-d); }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="dashboard.php" class="site-logo">🏆 <?= htmlspecialchars($appTitle) ?></a>
            <nav class="site-nav">
                <a href="dashboard.php">⚽ Predicciones</a>
                <a href="leaderboard.php">🏅 Tabla</a>
                <span class="nav-user">⚙️ Admin</span>
                <a href="profile.php" class="nav-user"><?= htmlspecialchars($user['avatar']) ?> Perfil</a>
                <a href="logout.php" class="btn-logout">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <h2>Panel de Administración</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Stats rápidas -->
        <div class="stats-row">
            <div class="stat-card"><span><?= $stats['users_count'] ?></span>Participantes</div>
            <div class="stat-card"><span><?= $stats['matches_count'] ?></span>Partidos</div>
            <div class="stat-card"><span><?= $stats['finished_count'] ?></span>Finalizados</div>
            <div class="stat-card"><span><?= $stats['predictions_count'] ?></span>Predicciones</div>
        </div>

        <!-- Configuración de la App -->
        <section class="admin-section">
            <h3>🎨 Configuración de la Aplicación</h3>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-row">
                    <div class="form-group">
                        <label>Título de la App *</label>
                        <input type="text" name="app_title" value="<?= htmlspecialchars($appTitle) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Color Primario *</label>
                        <div class="color-input-group">
                            <input type="color" name="primary_color" value="<?= htmlspecialchars($primaryColor) ?>" required>
                            <input type="text" placeholder="#2964a0" value="<?= htmlspecialchars($primaryColor) ?>" class="color-hex-input" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Color Primario Oscuro *</label>
                        <div class="color-input-group">
                            <input type="color" name="primary_color_dark" value="<?= htmlspecialchars($primaryColorDark) ?>" required>
                            <input type="text" placeholder="#153c8f" value="<?= htmlspecialchars($primaryColorDark) ?>" class="color-hex-input" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Color Primario Claro *</label>
                        <div class="color-input-group">
                            <input type="color" name="primary_color_light" value="<?= htmlspecialchars($primaryColorLight) ?>" required>
                            <input type="text" placeholder="#e8ecf5" value="<?= htmlspecialchars($primaryColorLight) ?>" class="color-hex-input" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Color de Acento *</label>
                        <div class="color-input-group">
                            <input type="color" name="accent_color" value="<?= htmlspecialchars($accentColor) ?>" required>
                            <input type="text" placeholder="#f59e0b" value="<?= htmlspecialchars($accentColor) ?>" class="color-hex-input" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar configuración</button>
            </form>
        </section>

        <!-- Agregar partido -->
        <section class="admin-section">
            <h3>➕ Agregar partido</h3>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add_match">
                <div class="form-row">
                    <div class="form-group">
                        <label>Equipo Local *</label>
                        <input type="text" name="home_team" placeholder="Ej: Argentina" required>
                    </div>
                    <div class="form-group form-group-sm">
                        <label>Bandera 🏳️</label>
                        <input type="text" name="home_flag" placeholder="🇦🇷" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Equipo Visitante *</label>
                        <input type="text" name="away_team" placeholder="Ej: Brasil" required>
                    </div>
                    <div class="form-group form-group-sm">
                        <label>Bandera 🏳️</label>
                        <input type="text" name="away_flag" placeholder="🇧🇷" maxlength="10">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha y Hora *</label>
                        <input type="datetime-local" name="match_date" required>
                    </div>
                    <div class="form-group">
                        <label>Fase</label>
                        <input type="text" name="stage" value="Fase de Grupos" placeholder="Fase de Grupos">
                    </div>
                    <div class="form-group form-group-sm">
                        <label>Grupo</label>
                        <input type="text" name="group_name" placeholder="A" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>Estadio</label>
                        <input type="text" name="venue" placeholder="Nombre del estadio">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Agregar partido</button>
            </form>
        </section>

        <!-- Actualizaciones -->
        <section class="admin-section" id="update-section">
            <h3>🔄 Actualizaciones de la App</h3>
            <div class="update-info">
                <div class="update-version-row">
                    <div class="update-version-block">
                        <span class="update-label">Versión instalada</span>
                        <span class="update-tag update-tag-current"><?= htmlspecialchars(CURRENT_VERSION) ?></span>
                    </div>
                    <?php if ($latestRelease): ?>
                    <div class="update-version-block">
                        <span class="update-label">Última versión en GitHub</span>
                        <span class="update-tag <?= $hasUpdate ? 'update-tag-new' : 'update-tag-current' ?>">
                            <?= htmlspecialchars($latestRelease['tag']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!$latestRelease): ?>
                    <p class="update-status update-status-warn">⚠️ No se pudo conectar con GitHub. Verificá la conexión del servidor.</p>
                <?php elseif ($hasUpdate): ?>
                    <div class="update-banner">
                        <p class="update-status update-status-new">
                            🆕 <strong>Nueva versión disponible: <?= htmlspecialchars($latestRelease['tag']) ?></strong>
                            <?php if ($latestRelease['name'] !== $latestRelease['tag']): ?>
                                — <?= htmlspecialchars($latestRelease['name']) ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($latestRelease['body']): ?>
                        <details class="update-notes">
                            <summary>Ver novedades</summary>
                            <pre><?= htmlspecialchars($latestRelease['body']) ?></pre>
                        </details>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('¿Actualizar la app a <?= htmlspecialchars($latestRelease['tag']) ?>?\n\nEsto reemplazará los archivos de la app (db.php y .htaccess no se tocan).')">
                            <input type="hidden" name="action" value="do_update">
                            <button type="submit" class="btn btn-primary">⬇️ Instalar <?= htmlspecialchars($latestRelease['tag']) ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="update-status update-status-ok">✅ Tenés la última versión instalada.</p>
                <?php endif; ?>

                <form method="POST" style="margin-top: 0.75rem;">
                    <input type="hidden" name="action" value="check_update">
                    <button type="submit" class="btn btn-secondary btn-sm">🔍 Verificar actualizaciones ahora</button>
                </form>
            </div>
        </section>

        <!-- Cargar resultados -->
        <section class="admin-section">
            <h3>📋 Partidos y Resultados</h3>
            <div class="admin-matches">
                <?php foreach ($matches as $m):
                    $matchDate = new DateTime($m['match_date']);
                ?>
                <div class="admin-match-row <?= $m['is_finished'] ? 'row-finished' : '' ?>">
                    <div class="admin-match-info">
                        <span class="match-teams-compact">
                            <?= $m['home_flag'] ?> <?= htmlspecialchars($m['home_team']) ?>
                            vs
                            <?= htmlspecialchars($m['away_team']) ?> <?= $m['away_flag'] ?>
                        </span>
                        <span class="match-date-compact"><?= $matchDate->format('d/m/Y H:i') ?></span>
                        <span class="badge <?= $m['is_finished'] ? 'badge-done' : 'badge-open' ?>">
                            <?= $m['is_finished'] ? "✅ {$m['home_score']}–{$m['away_score']}" : 'Pendiente' ?>
                        </span>
                    </div>
                    <div class="admin-match-actions">
                        <?php if (!$m['is_finished']): ?>
                        <form method="POST" class="result-form">
                            <input type="hidden" name="action" value="set_result">
                            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                            <input type="number" name="home_score" min="0" max="20" placeholder="0" required class="score-input-sm">
                            <span>—</span>
                            <input type="number" name="away_score" min="0" max="20" placeholder="0" required class="score-input-sm">
                            <button type="submit" class="btn btn-sm btn-success">Cargar resultado</button>
                        </form>
                        <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="reopen_match">
                            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-warning"
                                    onclick="return confirm('¿Reabrir partido y revertir puntos?')">
                                Reabrir
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
