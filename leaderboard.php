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

$rankings = $db->query('
    SELECT
        u.id,
        u.display_name,
        u.username,
        u.avatar,
        COALESCE(SUM(p.points), 0)                                                       AS total_points,
        COUNT(CASE WHEN p.points >= 1 THEN 1 END)                                        AS hits,
        COUNT(CASE WHEN p.points = 3 THEN 1 END)                                         AS exact_hits,
        COUNT(CASE WHEN m.is_finished = 1 AND p.id IS NOT NULL THEN 1 END)               AS played,
        COUNT(CASE WHEN m.is_finished = 0 AND m.match_date > NOW() AND p.id IS NOT NULL THEN 1 END) AS pending
    FROM users u
    LEFT JOIN predictions p ON p.user_id = u.id
    LEFT JOIN matches m ON m.id = p.match_id
    WHERE u.is_admin = 0 OR EXISTS (SELECT 1 FROM predictions WHERE user_id = u.id)
    GROUP BY u.id, u.display_name, u.username, u.avatar
    ORDER BY total_points DESC, exact_hits DESC, hits DESC
')->fetchAll();

$finishedCount = $db->query('SELECT COUNT(*) FROM matches WHERE is_finished = 1')->fetchColumn();
$totalMatches  = $db->query('SELECT COUNT(*) FROM matches')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Posiciones — <?= htmlspecialchars($appTitle) ?></title>
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
        .leaderboard-table th { background: var(--app-primary-d); }
        .leaderboard-table tr:hover td { background: var(--app-primary-l); }
        .pts-cell { color: var(--app-primary-d); }
        .btn-primary { background: var(--app-primary); }
        .btn-primary:hover { background: var(--app-primary-d); }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="dashboard.php" class="site-logo">🏆 <?= htmlspecialchars($appTitle) ?></a>
            <nav class="site-nav">
                <a href="dashboard.php">⚽ Mis Predicciones</a>
                <?php if ($user['is_admin']): ?>
                <a href="admin.php">⚙️ Admin</a>
                <?php endif; ?>
                <a href="profile.php" class="nav-user"><?= htmlspecialchars($user['avatar']) ?> <?= htmlspecialchars($user['display_name']) ?></a>
                <a href="logout.php" class="btn-logout">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <div>
                <h2>🏅 Tabla de Posiciones</h2>
                <p class="subtitle">
                    Partidos jugados: <strong><?= $finishedCount ?></strong> de <strong><?= $totalMatches ?></strong>
                </p>
            </div>
        </div>

        <div class="leaderboard-wrap">
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Participante</th>
                        <th title="Puntos totales">Pts</th>
                        <th title="Resultados exactos">Exactos</th>
                        <th title="Aciertos (resultado correcto)">Aciertos</th>
                        <th title="Partidos predichos que ya terminaron">Jugados</th>
                        <th title="Predicciones pendientes">Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $i => $r):
                        $isMe    = $r['id'] == $user['id'];
                        $medals  = ['🥇', '🥈', '🥉'];
                        $rankStr = $medals[$i] ?? ($i + 1);
                    ?>
                    <tr class="<?= $isMe ? 'row-me' : '' ?>">
                        <td class="rank-cell"><?= $rankStr ?></td>
                        <td class="name-cell">
                            <?= htmlspecialchars($r['avatar'] ?? '👤') ?> <?= htmlspecialchars($r['display_name']) ?>
                            <?php if ($isMe): ?><span class="you-badge">Vos</span><?php endif; ?>
                        </td>
                        <td class="pts-cell"><?= $r['total_points'] ?></td>
                        <td><?= $r['exact_hits'] ?></td>
                        <td><?= $r['hits'] ?></td>
                        <td><?= $r['played'] ?></td>
                        <td><?= $r['pending'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rankings)): ?>
                    <tr><td colspan="7" class="empty-row">Todavía no hay participantes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="scoring-legend" style="margin-top:1.5rem">
            <strong>Desempate:</strong> puntos totales → exactos → aciertos
        </div>
    </main>
</body>
</html>
