function togglePenaltyPred(matchId) {
    const homeInput = document.getElementById('ph_' + matchId);
    const awayInput = document.getElementById('pa_' + matchId);
    const area      = document.getElementById('penalty_area_' + matchId);
    if (!area) return;

    const home = homeInput.value.trim();
    const away = awayInput.value.trim();
    const isDraw = home !== '' && away !== '' && home === away;

    area.style.display = isDraw ? '' : 'none';
    if (!isDraw) {
        document.getElementById('pph_' + matchId).value = '';
        document.getElementById('ppa_' + matchId).value = '';
    }
}

function savePrediction(matchId) {
    const homeInput = document.getElementById('ph_' + matchId);
    const awayInput = document.getElementById('pa_' + matchId);
    const statusEl  = document.getElementById('status_' + matchId);

    const homeScore = homeInput.value.trim();
    const awayScore = awayInput.value.trim();

    if (homeScore === '' || awayScore === '') {
        showStatus(statusEl, '⚠️ Ingresá ambos puntajes.', 'status-warn');
        return;
    }

    const btn = document.querySelector('#match-' + matchId + ' .btn-save');
    if (btn) btn.disabled = true;

    const params = {
        action:     'save_prediction',
        match_id:   matchId,
        home_score: homeScore,
        away_score: awayScore,
    };

    const penaltyArea = document.getElementById('penalty_area_' + matchId);
    if (penaltyArea && penaltyArea.style.display !== 'none') {
        const penHome = document.getElementById('pph_' + matchId).value.trim();
        const penAway = document.getElementById('ppa_' + matchId).value.trim();
        if (penHome === '' || penAway === '') {
            showStatus(statusEl, '⚠️ Este partido puede ir a penales: ingresá el marcador de penales.', 'status-warn');
            return;
        }
        params.penalty_home = penHome;
        params.penalty_away = penAway;
    }

    const body = new URLSearchParams(params);

    fetch('api.php', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showStatus(statusEl, '✅ ' + data.msg, 'status-ok');
                if (btn) btn.textContent = '✏️';
            } else {
                showStatus(statusEl, '❌ ' + data.msg, 'status-err');
            }
        })
        .catch(() => showStatus(statusEl, '❌ Error de conexión.', 'status-err'))
        .finally(() => { if (btn) btn.disabled = false; });
}

function toggleOldFinished() {
    const hidden = document.body.classList.toggle('hide-old-finished');
    localStorage.setItem('hideOldFinished', hidden ? '1' : '0');
    updateHideOldFinishedBtn(hidden);
}

function updateHideOldFinishedBtn(hidden) {
    const btn = document.getElementById('btnHideOldFinished');
    if (!btn) return;
    btn.innerHTML = hidden ? '👁️<br />Mostrar anteriores' : '🙈<br />Ocultar anteriores';
}

function showStatus(el, msg, cls) {
    el.textContent  = msg;
    el.className    = 'save-status ' + cls;
    clearTimeout(el._timer);
    el._timer = setTimeout(() => {
        el.textContent = '';
        el.className   = 'save-status';
    }, 3000);
}

// Guardar con Enter en los inputs
document.addEventListener('DOMContentLoaded', () => {
    const hideOld = localStorage.getItem('hideOldFinished') === '1';
    if (hideOld) document.body.classList.add('hide-old-finished');
    updateHideOldFinishedBtn(hideOld);

    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const id = input.id.replace(/^p[ha]_/, '');
                savePrediction(parseInt(id, 10));
            }
        });
    });

    document.querySelectorAll('[id^="penalty_area_"]').forEach(area => {
        const matchId = area.id.replace('penalty_area_', '');
        togglePenaltyPred(parseInt(matchId, 10));
    });
});
