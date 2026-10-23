'use strict';

/**
 * Cronômetro Pomodoro persistente.
 * Mantém o estado em localStorage com base em relógio absoluto (endsAt),
 * para continuar contando ao trocar de página, recarregar ou fechar a aba.
 * Carregado em todas as páginas via layouts/footer.php.
 *
 * Ao terminar o ciclo toca um jingle de jogo (Web Audio API) na aba
 * responsável e dispara uma notificação do navegador — o áudio é agendado
 * com precisão de amostra, então soa mesmo com a aba em segundo plano.
 */
(function () {
    const STORAGE_KEY = 'practiceday_pomodoro_v1';
    const DEFAULT_SECONDS = 25 * 60;

    /* Identidade estável da aba (persiste no recarregamento da aba). */
    const TAB_ID = (function () {
        try {
            let id = sessionStorage.getItem('practiceday_tab_id');
            if (!id) {
                id = (crypto.randomUUID && crypto.randomUUID()) ||
                    (Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10));
                sessionStorage.setItem('practiceday_tab_id', id);
            }
            return id;
        } catch (e) {
            return 'tab-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
        }
    })();

    const OWNER_KEY = 'practiceday_pomodoro_sound_owner';
    const DONE_KEY = 'practiceday_pomodoro_completed';

    function readJSON(key) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function writeJSON(key, value) {
        try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* sem storage */ }
    }

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

    /* =======================================================
       ÁUDIO — jingle de jogo agendado para o instante exato
       ======================================================= */
    let audioCtx = null;
    let victory = null;      // { endsAt, nodes: [], bus }

    function ensureAudio() {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        try {
            if (!audioCtx) audioCtx = new Ctx();
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(function () { /* aguarda gesto do usuário */ });
            }
            return audioCtx;
        } catch (e) {
            return null;
        }
    }

    /* Desbloqueia o áudio no primeiro gesto (política de autoplay). */
    ['pointerdown', 'mousedown', 'touchstart', 'keydown'].forEach(function (evt) {
        document.addEventListener(evt, function unlock() {
            ensureAudio();
        }, { passive: true });
    });

    function cancelVictory() {
        if (!victory) return;
        const ctx = audioCtx;
        victory.nodes.forEach(function (osc) {
            try { if (ctx) osc.stop(ctx.currentTime); } catch (e) { /* já parado */ }
            try { osc.disconnect(); } catch (e) { /* noop */ }
        });
        try { victory.bus.disconnect(); } catch (e) { /* noop */ }
        victory = null;
    }

    /**
     * Toca (ou agenda) o jingle de conclusão.
     * @param {number} startsAt epoch ms em que o som deve começar
     * @return {boolean} verdadeiro se o som foi agendado com sucesso
     */
    function playVictory(startsAt) {
        const ctx = ensureAudio();
        if (!ctx) return false;

        cancelVictory();

        const when = ctx.currentTime + Math.max(0.02, (startsAt - Date.now()) / 1000);
        const bus = ctx.createGain();
        bus.gain.value = 0.85;
        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 5200;
        bus.connect(filter);
        filter.connect(ctx.destination);

        const nodes = [];
        // Arpejo crescente estilo "level up" + nota final longa
        const notes = [
            [659.25, 0.00, 0.13],
            [783.99, 0.10, 0.13],
            [987.77, 0.20, 0.13],
            [1174.66, 0.30, 0.13],
            [1318.51, 0.40, 0.60]
        ];

        notes.forEach(function (n) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const t = when + n[1];
            const dur = n[2];

            osc.type = 'square';
            osc.frequency.setValueAtTime(n[0], t);

            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.22, t + 0.012);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);

            osc.connect(gain);
            gain.connect(bus);
            try { osc.start(t); osc.stop(t + dur + 0.06); } catch (e) { /* contexto inválido */ }
            nodes.push(osc);
        });

        // Acorde de vitória (sino) + batida grave para efeito de jogo
        [1046.50, 1318.51, 1567.98].forEach(function (freq) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const t = when + 0.44;
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, t);
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.16, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.9);
            osc.connect(gain);
            gain.connect(bus);
            try { osc.start(t); osc.stop(t + 0.95); } catch (e) { /* noop */ }
            nodes.push(osc);
        });

        const boom = ctx.createOscillator();
        const boomGain = ctx.createGain();
        const t = when + 0.42;
        boom.type = 'triangle';
        boom.frequency.setValueAtTime(220, t);
        boom.frequency.exponentialRampToValueAtTime(70, t + 0.35);
        boomGain.gain.setValueAtTime(0.0001, t);
        boomGain.gain.exponentialRampToValueAtTime(0.3, t + 0.02);
        boomGain.gain.exponentialRampToValueAtTime(0.0001, t + 0.4);
        boom.connect(boomGain);
        boomGain.connect(bus);
        try { boom.start(t); boom.stop(t + 0.45); } catch (e) { /* noop */ }
        nodes.push(boom);

        victory = { endsAt: startsAt, nodes: nodes, bus: bus };
        return true;
    }

    function scheduleVictoryFor(endsAt) {
        if (!endsAt || endsAt <= Date.now()) return false;
        if (victory && victory.endsAt === endsAt) return true;
        return playVictory(endsAt);
    }

    /* =======================================================
       ELEIÇÃO DA ABA RESPONSÁVEL PELO SOM
       ======================================================= */
    function isSoundOwner(endsAt) {
        const owner = readJSON(OWNER_KEY);
        return !!(owner && owner.endsAt === endsAt && owner.tabId === TAB_ID);
    }

    function claimSoundOwner(endsAt) {
        const owner = readJSON(OWNER_KEY);
        if (owner && owner.endsAt === endsAt && owner.tabId !== TAB_ID) {
            return false; // outra aba já é a responsável
        }
        writeJSON(OWNER_KEY, { endsAt: endsAt, tabId: TAB_ID, at: Date.now() });
        return true;
    }

    function claimCompletion(endsAt) {
        const done = readJSON(DONE_KEY);
        if (done && done.endsAt === endsAt && (Date.now() - (done.at || 0)) < 120000) {
            return false; // outra aba já tratou a conclusão
        }
        writeJSON(DONE_KEY, { endsAt: endsAt, tabId: TAB_ID, at: Date.now() });
        return true;
    }

    function notifyCompletion(endsAt) {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted') return;
        try {
            new Notification('⏰ Pomodoro concluído!', {
                body: 'Ciclo finalizado. Hora de comemorar e descansar!',
                tag: 'practiceday-pomodoro-' + endsAt,
                renotify: false
            });
        } catch (e) { /* notificação indisponível neste contexto */ }
    }

    function requestNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'default') return;
        try {
            const result = Notification.requestPermission();
            if (result && typeof result.catch === 'function') result.catch(function () { /* negado */ });
        } catch (e) { /* API antiga */ }
    }

    /* =======================================================
       TIMER
       ======================================================= */
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
            } else if (isSoundOwner(state.endsAt) && (!victory || victory.endsAt !== state.endsAt)) {
                // Garante o som mesmo se o áudio estava bloqueado quando agendamos.
                scheduleVictoryFor(state.endsAt);
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
        const endsAt = state.endsAt || Date.now();
        const startedAt = state.startedAt || (Date.now() - DEFAULT_SECONDS * 1000);
        const endedAt = Date.now();
        const elapsedMs = Math.max(0, endedAt - startedAt - (state.pausedTotalMs || 0));
        const durationMin = Math.max(1, Math.round(elapsedMs / 60000));
        const disciplinaId = state.disciplinaId || null;
        const owner = isSoundOwner(endsAt);
        const scheduledHere = !!(victory && victory.endsAt === endsAt);

        state = freshState('done');
        clearState();
        emit('completed');

        const finish = function () {
            if (!claimCompletion(endsAt)) return; // outra aba já tratou

            if (!owner || !scheduledHere) {
                // Aba dona do som não está mais aqui (ou o agendamento falhou).
                playVictory(endedAt);
            }
            notifyCompletion(endsAt);

            const url = window.APP_CONFIG && window.APP_CONFIG.pomodoroUrl;
            if (!url) return;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    inicio: toLocalString(startedAt),
                    fim: toLocalString(endedAt),
                    duracao: durationMin,
                    disciplina_id: disciplinaId,
                    tecnica: 'pomodoro'
                })
            }).catch(function (e) {
                window.console && console.warn('Pomodoro: falha ao registrar a sessão.', e);
            });
        };

        if (owner) {
            finish();
        } else {
            // Dá 1,5s para a aba responsável concluir; se ela não existir mais, assumimos.
            setTimeout(finish, 1500);
        }
    }

    function ensureTicking() {
        if (intervalId !== null) clearInterval(intervalId);
        intervalId = setInterval(tick, 1000);
        tick();
    }

    /* Agenda o som para este ciclo quando esta aba é a responsável. */
    function claimAndSchedule(endsAt) {
        if (!endsAt) return;
        if (claimSoundOwner(endsAt)) {
            scheduleVictoryFor(endsAt);
        }
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
            ensureAudio();
            requestNotificationPermission();
            claimAndSchedule(state.endsAt);
            emit('state');
        },
        pause: function () {
            if (state.status !== 'running') return;
            state.remaining = remaining();
            state.status = 'paused';
            state.endsAt = null;
            state.pauseAt = Date.now();
            cancelVictory();
            writeState();
            emit('state');
        },
        stop: function () {
            state = freshState('idle');
            cancelVictory();
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
        if (!document.hidden) {
            tick();
            if (state.status === 'running') claimAndSchedule(state.endsAt);
        }
    });
    window.addEventListener('focus', function () {
        tick();
        if (state.status === 'running') claimAndSchedule(state.endsAt);
    });

    // Inicializa lendo um estado persistido (ex.: sessão iniciada em outra página).
    ensureTicking();
    if (state.status === 'running' && state.endsAt) {
        claimAndSchedule(state.endsAt);
    }
})();
