<?php
require_once 'auth.php';
require_once 'db.php';
requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user   = currentUser();
$db     = getDB();

switch ($action) {
    case 'save_prediction':
        $matchId   = (int)($_POST['match_id'] ?? 0);
        $homeScore = (int)($_POST['home_score'] ?? 0);
        $awayScore = (int)($_POST['away_score'] ?? 0);

        if (!$matchId) {
            echo json_encode(['ok' => false, 'msg' => 'Partido inválido.']);
            exit;
        }

        // Verificar que el partido no haya empezado
        $stmt = $db->prepare('SELECT match_date, is_finished, can_penalties FROM matches WHERE id = ?');
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();

        if (!$match) {
            echo json_encode(['ok' => false, 'msg' => 'Partido no encontrado.']);
            exit;
        }

        if (new DateTime($match['match_date']) <= new DateTime()) {
            echo json_encode(['ok' => false, 'msg' => 'El partido ya empezó, no podés editar tu predicción.']);
            exit;
        }

        if ($homeScore < 0 || $awayScore < 0 || $homeScore > 20 || $awayScore > 20) {
            echo json_encode(['ok' => false, 'msg' => 'Puntaje inválido.']);
            exit;
        }

        $penaltyHome = null;
        $penaltyAway = null;
        if ($match['can_penalties'] && $homeScore === $awayScore) {
            if (!isset($_POST['penalty_home'], $_POST['penalty_away'])
                || $_POST['penalty_home'] === '' || $_POST['penalty_away'] === '') {
                echo json_encode(['ok' => false, 'msg' => 'Este partido puede ir a penales: ingresá el marcador de penales.']);
                exit;
            }
            $penaltyHome = (int)$_POST['penalty_home'];
            $penaltyAway = (int)$_POST['penalty_away'];
            if ($penaltyHome < 0 || $penaltyAway < 0 || $penaltyHome === $penaltyAway) {
                echo json_encode(['ok' => false, 'msg' => 'Resultado de penales inválido.']);
                exit;
            }
        }

        $db->prepare('
            INSERT INTO predictions (user_id, match_id, home_score, away_score, pred_penalty_home, pred_penalty_away)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE home_score = VALUES(home_score), away_score = VALUES(away_score),
                                    pred_penalty_home = VALUES(pred_penalty_home), pred_penalty_away = VALUES(pred_penalty_away)
        ')->execute([$user['id'], $matchId, $homeScore, $awayScore, $penaltyHome, $penaltyAway]);

        echo json_encode(['ok' => true, 'msg' => '¡Predicción guardada!']);
        break;

    default:
        echo json_encode(['ok' => false, 'msg' => 'Acción desconocida.']);
}
