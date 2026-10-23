<?php
use App\Helpers\Url;
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Produtividade</p>
                    <h1>Pomodoro</h1>
                    <p>Mantenha o foco com a técnica Pomodoro.</p>
                </div>
            </section>

            <div class="row justify-content-center mt-5">
                <div class="col-md-6 col-lg-5">
                    <div class="card text-center shadow-sm border-0">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <label for="disciplina_id" class="form-label text-muted">Disciplina (Opcional)</label>
                                <select id="disciplina_id" class="form-select">
                                    <option value="">Selecione uma disciplina...</option>
                                    <?php foreach ($disciplines as $discipline): ?>
                                        <option value="<?= htmlspecialchars((string) $discipline['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="display-1 fw-bold mb-4" id="timer-display" style="font-variant-numeric: tabular-nums;">
                                25:00
                            </div>

                            <div class="mb-3">
                                <span id="timer-status" class="badge bg-secondary mb-3 fs-6">Parado</span>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <button type="button" class="btn btn-primary px-4 gap-3" id="btn-start">
                                    <i class="bi bi-play-fill"></i> Iniciar
                                </button>
                                <button type="button" class="btn btn-warning px-4 gap-3 d-none" id="btn-pause">
                                    <i class="bi bi-pause-fill"></i> Pausar
                                </button>
                                <button type="button" class="btn btn-danger px-4 gap-3 d-none" id="btn-stop">
                                    <i class="bi bi-stop-fill"></i> Parar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('timer-display');
    const statusLabel = document.getElementById('timer-status');
    const btnStart = document.getElementById('btn-start');
    const btnPause = document.getElementById('btn-pause');
    const btnStop = document.getElementById('btn-stop');
    const disciplinaSelect = document.getElementById('disciplina_id');
    const timer = window.PomodoroTimer;

    if (!timer) return;

    function formatTime(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    }

    function setStatus(text, className) {
        statusLabel.textContent = text;
        statusLabel.className = 'badge bg-' + className + ' mb-3 fs-6';
    }

    function render(snap) {
        const status = snap.state.status;
        const time = formatTime(snap.remaining);

        display.textContent = time;
        document.title = time + ' - Pomodoro - PracticeDay';

        if (snap.type === 'completed') {
            setStatus('Concluído', 'success');
        } else if (status === 'running') {
            setStatus('Executando', 'primary');
        } else if (status === 'paused') {
            setStatus('Pausado', 'warning');
        } else if (status === 'done') {
            setStatus('Concluído', 'success');
        } else {
            setStatus('Parado', 'secondary');
        }

        if (status === 'running') {
            btnStart.classList.add('d-none');
            btnPause.classList.remove('d-none');
            btnStop.classList.remove('d-none');
        } else if (status === 'paused') {
            btnStart.classList.remove('d-none');
            btnStart.innerHTML = '<i class="bi bi-play-fill"></i> Retomar';
            btnPause.classList.add('d-none');
            btnStop.classList.remove('d-none');
        } else {
            btnStart.classList.remove('d-none');
            btnStart.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar';
            btnPause.classList.add('d-none');
            btnStop.classList.add('d-none');
        }
    }

    btnStart.addEventListener('click', function () {
        timer.start(disciplinaSelect ? disciplinaSelect.value : '');
    });
    btnPause.addEventListener('click', function () { timer.pause(); });
    btnStop.addEventListener('click', function () { timer.stop(); });

    // Ao restaurar uma sessão que continuou em outra página, realinha a disciplina.
    timer.subscribe(function (snap) {
        if (snap.type === 'restore' && snap.state.disciplinaId && disciplinaSelect) {
            disciplinaSelect.value = snap.state.disciplinaId;
        }
        render(snap);
    });
});
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
