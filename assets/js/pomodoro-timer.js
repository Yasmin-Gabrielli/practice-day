'use strict';

/**
 * Cronômetro Pomodoro persistente.
 * Mantém o estado em localStorage com base em relógio absoluto (endsAt),
 * para continuar contando ao trocar de página, recarregar ou fechar a aba.
 * Carregado em todas as páginas via layouts/footer.php.
 */
(function () {
    const STORAGE_KEY = 'practiceday_pomodoro_v1';
    const DEFAULT_SECONDS = 25 * 60;

    function readState() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return null;
            if (parsed.status !== 'running' && parsed.status !== 'paused') return null;
            return parsed;
        } catch (e) {
            return null;
        }
    }

    function writeState() {
        try {
            if (state.status === 'running' || state.status === 'paused') {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            }
        } catch (e) { /* sem armazenamento disponível */ }
    }

    function clearState() {
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) { /* noop */ }
    }

    function freshState(status) {
        return {
            status: status || 'idle',
            remaining: DEFAULT_SECONDS,
            endsAt: null,
            startedAt: null,
            pausedTotalMs: 0,
            pauseAt: null,
            disciplinaId: ''
        };
    }

    let state = readState() || freshState('idle');
    let intervalId = null;
    const listeners = [];

    function remaining() {
        if (state.status === 'running' && state.endsAt) {
            return Math.max(0, Math.ceil((state.endsAt - Date.now()) / 1000));
        }
        return Math.max(0, state.remaining || 0);
    }

    function snapshot(type) {
        return {
            type: type || 'tick',
            state: { status: state.status, disciplinaId: state.disciplinaId || '' },
            remaining: remaining()
        };
    }

    function emit(type) {
        const snap = snapshot(type);
        listeners.forEach(function (fn) {
            try { fn(snap); } catch (e) { /* listener com erro não derruba o timer */ }
        });
    }

    function tick() {
        if (state.status === 'running') {
            state.remaining = remaining();
            writeState();
            emit('tick');
            if (state.remaining <= 0) {
                complete();
            }
        }
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function toLocalString(epochMs) {
        const d = new Date(epochMs);
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
            ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    async function complete() {
        const startedAt = state.startedAt || (Date.now() - DEFAULT_SECONDS * 1000);
        const endedAt = Date.now();
        const elapsedMs = Math.max(0, endedAt - startedAt - (state.pausedTotalMs || 0));
        const durationMin = Math.max(1, Math.round(elapsedMs / 60000));
        const disciplinaId = state.disciplinaId || null;

        state = freshState('done');
        clearState();
        emit('completed');

        const url = window.APP_CONFIG && window.APP_CONFIG.pomodoroUrl;
        if (!url) return;

        try {
            await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    inicio: toLocalString(startedAt),
                    fim: toLocalString(endedAt),
                    duracao: durationMin,
                    disciplina_id: disciplinaId,
                    tecnica: 'pomodoro'
                })
            });
        } catch (e) {
            // Sem conexão: a sessão concluída não pôde ser registrada.
            window.console && console.warn('Pomodoro: falha ao registrar a sessão.', e);
        }
    }

    function ensureTicking() {
        if (intervalId !== null) clearInterval(intervalId);
        intervalId = setInterval(tick, 1000);
        tick();
    }

    window.PomodoroTimer = {
        start: function (disciplinaId) {
            if (state.status === 'running') return;
            if (state.status === 'paused') {
                // Retomar: desconta o tempo pausado.
                if (state.pauseAt) {
                    state.pausedTotalMs = (state.pausedTotalMs || 0) + (Date.now() - state.pauseAt);
                    state.pauseAt = null;
                }
                state.status = 'running';
                state.endsAt = Date.now() + Math.max(1, state.remaining) * 1000;
            } else {
                state = freshState('running');
                state.startedAt = Date.now();
                state.endsAt = Date.now() + DEFAULT_SECONDS * 1000;
                state.remaining = DEFAULT_SECONDS;
                state.disciplinaId = disciplinaId || '';
            }
            if (disciplinaId !== undefined && disciplinaId !== null) {
                state.disciplinaId = disciplinaId || '';
            }
            writeState();
            ensureTicking();
            emit('state');
        },
        pause: function () {
            if (state.status !== 'running') return;
            state.remaining = remaining();
            state.status = 'paused';
            state.endsAt = null;
            state.pauseAt = Date.now();
            writeState();
            emit('state');
        },
        stop: function () {
            state = freshState('idle');
            clearState();
            emit('state');
        },
        getState: function () { return snapshot('state'); },
        subscribe: function (fn) {
            if (typeof fn !== 'function') return;
            listeners.push(fn);
            try { fn(snapshot('restore')); } catch (e) { /* noop */ }
        }
    };

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) tick();
    });
    window.addEventListener('focus', tick);

    // Inicializa lendo um estado persistido (ex.: sessão iniciada em outra página).
    ensureTicking();
})();
