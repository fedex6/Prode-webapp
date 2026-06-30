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

// Cargar partidos con predicciones del usuario
$stmt = $db->prepare('
    SELECT m.*,
           p.home_score  AS pred_home,
           p.away_score  AS pred_away,
           p.pred_penalty_home,
           p.pred_penalty_away,
           p.points      AS pred_points,
           p.id          AS pred_id
    FROM matches m
    LEFT JOIN predictions p ON p.match_id = m.id AND p.user_id = ?
    ORDER BY m.match_date ASC
');
$stmt->execute([$user['id']]);
$matches = $stmt->fetchAll();

// Estadísticas de predicciones por partido (para mostrar % tras finalizar)
$stmtStats = $db->prepare('
    SELECT match_id,
           COUNT(*) AS total,
           SUM(CASE WHEN home_score > away_score THEN 1 ELSE 0 END) AS home_wins,
           SUM(CASE WHEN home_score = away_score THEN 1 ELSE 0 END) AS draws,
           SUM(CASE WHEN home_score < away_score THEN 1 ELSE 0 END) AS away_wins
    FROM predictions
    GROUP BY match_id
');
$stmtStats->execute();
$matchStats = [];
foreach ($stmtStats->fetchAll() as $row) {
    $matchStats[$row['match_id']] = $row;
}

// Puntaje total del usuario
$stmtPts = $db->prepare('SELECT COALESCE(SUM(points), 0) as total FROM predictions WHERE user_id = ?');
$stmtPts->execute([$user['id']]);
$totalPoints = $stmtPts->fetchColumn();

// Agrupar por fase/grupo o por fecha según parámetro
$sortByDate = isset($_GET['sort']) && $_GET['sort'] === 'date';
$grouped = [];
foreach ($matches as $m) {
    if ($sortByDate) {
        $key = (new DateTime($m['match_date']))->format('l d/m/Y');
        // traducir día al español
        $dias = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
                 'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
        foreach ($dias as $en => $es) $key = str_replace($en, $es, $key);
    } else {
        $key = $m['stage'] . ($m['group_name'] ? ' — Grupo ' . $m['group_name'] : '');
    }
    $grouped[$key][] = $m;
}

$now = new DateTime();
$today = (clone $now)->setTime(0, 0, 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appTitle) ?></title>
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
        .score-badge { background: linear-gradient(135deg, var(--app-primary), var(--app-primary-d)); }
        .section-title { color: var(--app-primary-d); border-bottom-color: var(--app-primary-l); }
        .badge-done { background: var(--app-primary-l); color: var(--app-primary-d); }
        .btn-primary { background: var(--app-primary); }
        .btn-primary:hover { background: var(--app-primary-d); }
        .btn-save { background: var(--app-primary-l); }
        .btn-save:hover { background: #c6e8d6; }

        .btn-sort {
            text-align: center;
            background: transparent;
            border: 1.5px solid #d1d5db;
            color: var(--text);
            padding: .3rem .8rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, border-color .15s;
        }
        .btn-sort:hover { background: var(--gray-l); text-decoration: none; }
        body.hide-old-finished .match-old-finished { display: none; }
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
                <a href="profile.php" class="nav-user"><?= htmlspecialchars($user['avatar']) ?> <?= htmlspecialchars($user['display_name']) ?></a>
                <a href="logout.php" class="btn-logout">Salir</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <div>
                <h2>Mis Predicciones</h2>
                <p class="subtitle">Ingresá el resultado que esperás para cada partido antes de que empiece.</p>

                <div class="scoring-legend">
                    <strong>Sistema de puntos:</strong>
                    <span class="pill pill-gold">3 pts — Resultado exacto</span>
                    <span class="pill pill-blue">1 pt — Ganador/empate correcto</span>
                    <span class="pill pill-gray">0 pts — Fallo</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:.4rem">
                <div class="score-badge">
                    <span class="score-number"><?= $totalPoints ?></span>
                    <span class="score-label">puntos</span>
                </div>
                <?php if ($sortByDate): ?>
                <a href="dashboard.php" class="btn-sort">☠️<br />Por GRUPOS</a>
                <?php else: ?>
                <a href="dashboard.php?sort=date" class="btn-sort">📅<br />Por FECHAS</a>
                <?php endif; ?>
                <button type="button" id="btnHideOldFinished" class="btn-sort" onclick="toggleOldFinished()">🙈<br />Ocultar anteriores</button>
            </div>
        </div>

        <?php foreach ($grouped as $section => $sectionMatches): ?>
        <section class="match-section">
            <h3 class="section-title"><?= htmlspecialchars($section) ?></h3>
            <div class="matches-grid">
                <?php foreach ($sectionMatches as $m):
                    $matchDate  = new DateTime($m['match_date']);
                    $locked     = $matchDate <= $now;
                    $finished   = (bool)$m['is_finished'];
                    $hasPred    = $m['pred_id'] !== null;
                    $cardClass  = 'match-card';
                    if ($finished) $cardClass .= ' match-finished';
                    elseif ($locked) $cardClass .= ' match-locked';
                    $isOldFinished = $finished && $matchDate < $today;
                    if ($isOldFinished) $cardClass .= ' match-old-finished';
                ?>
                <div class="<?= $cardClass ?>" id="match-<?= $m['id'] ?>">
                    <div class="match-meta">
                        <span class="match-date"><?= $matchDate->format('d/m H:i') ?>hs</span>
                        <?php if ($finished): ?>
                            <span class="badge badge-done">Finalizado</span>
                        <?php elseif ($locked): ?>
                            <span class="badge badge-locked">Cerrado</span>
                        <?php else: ?>
                            <span class="badge badge-open">Abierto</span>
                        <?php endif; ?>
                    </div>

                    <div class="match-teams">
                        <div class="team team-home">
                            <span class="flag"><?= $m['home_flag'] ?></span>
                            <span class="team-name"><?= htmlspecialchars($m['home_team']) ?></span>
                        </div>

                        <div class="match-score-area">
                            <?php if ($finished): ?>
                                <div class="result-score">
                                    <?= $m['home_score'] ?> — <?= $m['away_score'] ?>
                                    <?php if ($m['went_penalties']): ?>
                                        <div class="penalty-score-sub">Pen. <?= $m['penalty_home'] ?>-<?= $m['penalty_away'] ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($locked && $hasPred): ?>
                                <div class="locked-pred">
                                    <?= $m['pred_home'] ?> — <?= $m['pred_away'] ?>
                                    <?php if ($m['pred_penalty_home'] !== null): ?>
                                        <div class="penalty-score-sub">Pen. <?= $m['pred_penalty_home'] ?>-<?= $m['pred_penalty_away'] ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($locked): ?>
                                <div class="locked-pred no-pred">Sin pred.</div>
                            <?php else: ?>
                                <div class="pred-inputs">
                                    <input type="number" class="score-input" min="0" max="20"
                                           id="ph_<?= $m['id'] ?>"
                                           value="<?= $hasPred ? $m['pred_home'] : '' ?>"
                                           placeholder="0"
                                           <?= $m['can_penalties'] ? 'oninput="togglePenaltyPred(' . $m['id'] . ')"' : '' ?>>
                                    <span class="vs-sep">:</span>
                                    <input type="number" class="score-input" min="0" max="20"
                                           id="pa_<?= $m['id'] ?>"
                                           value="<?= $hasPred ? $m['pred_away'] : '' ?>"
                                           placeholder="0"
                                           <?= $m['can_penalties'] ? 'oninput="togglePenaltyPred(' . $m['id'] . ')"' : '' ?>>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="team team-away">
                            <span class="team-name"><?= htmlspecialchars($m['away_team']) ?></span>
                            <span class="flag"><?= $m['away_flag'] ?></span>
                        </div>
                    </div>

                    <?php if (!$finished && !$locked && $m['can_penalties']): ?>
                    <div class="penalty-pred-area" id="penalty_area_<?= $m['id'] ?>" style="display:none;">
                        <span class="penalty-pred-label">🥅 Termina en penales — marcador:</span>
                        <div class="penalty-pred-scores">
                            <input type="number" class="score-input" min="0" max="30"
                                   id="pph_<?= $m['id'] ?>" placeholder="0"
                                   value="<?= $m['pred_penalty_home'] ?? '' ?>">
                            <span class="vs-sep">:</span>
                            <input type="number" class="score-input" min="0" max="30"
                                   id="ppa_<?= $m['id'] ?>" placeholder="0"
                                   value="<?= $m['pred_penalty_away'] ?? '' ?>">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="match-venue">📍 <?= htmlspecialchars($m['venue']) ?></div>
                    <?php if ($finished): ?>
                    <div class="match-result-summary">
                        <?php if ($hasPred): ?>
                        <span class="pred-score-inline">Tu pred: <?= $m['pred_home'] ?>—<?= $m['pred_away'] ?></span>
                        <span class="points-earned <?= $m['pred_points'] >= 3 ? 'pts-gold' : ($m['pred_points'] >= 1 ? 'pts-blue' : 'pts-gray') ?>">
                            +<?= $m['pred_points'] ?>pts
                        </span>
                        <?php else: ?>
                        <span class="pred-score-inline no-pred">Sin predicción</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($matchStats[$m['id']]) && $matchStats[$m['id']]['total'] > 0):
                        $st    = $matchStats[$m['id']];
                        $total = $st['total'];
                        $pHome = round($st['home_wins'] / $total * 100);
                        $pDraw = round($st['draws']     / $total * 100);
                        $pAway = 100 - $pHome - $pDraw;
                    ?>
                    <div class="pred-dist">
                        <div class="pred-dist-bar">
                            <?php if ($pHome > 0): ?>
                            <div class="pred-dist-seg seg-home" style="width:<?= $pHome ?>%"><?= $pHome ?>%</div>
                            <?php endif; ?>
                            <?php if ($pDraw > 0): ?>
                            <div class="pred-dist-seg seg-draw" style="width:<?= $pDraw ?>%"><?= $pDraw ?>%</div>
                            <?php endif; ?>
                            <?php if ($pAway > 0): ?>
                            <div class="pred-dist-seg seg-away" style="width:<?= $pAway ?>%"><?= $pAway ?>%</div>
                            <?php endif; ?>
                        </div>
                        <div class="pred-dist-labels">
                            <span class="pdl-home"><?= htmlspecialchars($m['home_team']) ?> <?= $pHome ?>%</span>
                            <span class="pdl-draw">Empate <?= $pDraw ?>%</span>
                            <span class="pdl-away"><?= $pAway ?>% <?= htmlspecialchars($m['away_team']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="save-status" id="status_<?= $m['id'] ?>"></div>
                    <?php if (!$finished && !$locked): ?>
                    <button class="btn-save" onclick="savePrediction(<?= $m['id'] ?>)"
                            title="Guardar predicción">
                        <?= $hasPred ? '✏️' : '💾' ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <script src="app.js?v=<?= filemtime(__DIR__ . '/app.js') ?>"></script>
</body>
</html>
