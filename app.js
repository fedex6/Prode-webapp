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

    const body = new URLSearchParams({
        action:     'save_prediction',
        match_id:   matchId,
        home_score: homeScore,
        away_score: awayScore,
    });

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
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const id = input.id.replace(/^p[ha]_/, '');
                savePrediction(parseInt(id, 10));
            }
        });
    });
});
