<?php
use App\Helpers\Lang;
use App\Helpers\Url;

$errors = $feedback['errors'] ?? [];
$success = $feedback['success'] ?? null;

require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">

            <!-- Cabeçalho -->
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Gestão de Tempo</p>
                    <h1>Calendário</h1>
                    <p>Acompanhe suas aulas, provas, trabalhos e tarefas.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#modal-novo-evento">
                        <i class="bi bi-plus-lg"></i><span>Novo Evento</span>
                    </button>
                </div>
            </section>

            <!-- Feedback -->
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>
            <?php if ($errors !== []): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <strong>Erro na operação:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $msg): ?>
                            <li><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Main Calendar -->
                <div class="col-12 col-xl-9">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>

                <!-- Próximos Eventos -->
                <div class="col-12 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                            <h5 class="mb-0 fw-bold">Próximos Eventos</h5>
                        </div>
                        <div class="card-body px-4">
                            <?php if (empty($upcoming)): ?>
                                <div class="text-muted text-center py-4">Nenhum evento próximo.</div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($upcoming as $evt): ?>
                                        <div class="d-flex gap-3 align-items-start">
                                            <div class="d-flex flex-column align-items-center justify-content-center bg-light rounded-3 px-2 py-1" style="min-width: 50px;">
                                                <span class="text-danger fw-bold fs-5 lh-1"><?= date('d', strtotime($evt['data_inicio'])) ?></span>
                                                <span class="text-muted small text-uppercase fw-medium" style="font-size: 0.7rem;"><?= date('M', strtotime($evt['data_inicio'])) ?></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: <?= htmlspecialchars($evt['disciplina_cor'] ?? '#6c757d', ENT_QUOTES, 'UTF-8') ?>"></span>
                                                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem;"><?= htmlspecialchars($evt['tipo'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                                <h6 class="mb-1 text-truncate fw-semibold" style="font-size: 0.9rem;" title="<?= htmlspecialchars($evt['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($evt['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                                </h6>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($evt['data_inicio'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Modal Novo Evento -->
<div class="modal fade" id="modal-novo-evento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="calendar-event-form" method="post" action="<?= Url::to('/calendario') ?>">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="event-form-title">Novo Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="event-titulo" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Disciplina</label>
                            <select name="disciplina_id" id="event-disciplina" class="form-select">
                                <option value="">Sem disciplina</option>
                                <?php foreach ($disciplines as $d): ?>
                                    <option value="<?= htmlspecialchars((string)$d['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Evento</label>
                            <select name="tipo" id="event-tipo" class="form-select" required>
                                <option value="evento" selected>Evento Genérico</option>
                                <option value="prova">Prova</option>
                                <option value="trabalho">Trabalho</option>
                                <option value="lembrete">Lembrete</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data de Início</label>
                            <input type="datetime-local" name="data_inicio" id="event-inicio" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Término</label>
                            <input type="datetime-local" name="data_fim" id="event-fim" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" id="event-descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Lembretes</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-reminder"><i class="bi bi-plus-lg"></i> Adicionar</button>
                        </div>
                        <div id="reminder-fields" class="d-flex flex-column gap-2"></div>
                        <div class="form-text">Os lembretes são salvos na sua agenda e vinculados ao evento.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="event-submit">Criar Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar Evento -->
<div class="modal fade" id="modal-view-evento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="view-evt-title">...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="view-evt-desc" class="text-muted mb-4"></p>
                <div class="bg-light p-3 rounded-3 mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="d-block small text-muted mb-1">Tipo</span>
                            <span id="view-evt-type" class="badge bg-primary text-uppercase"></span>
                        </div>
                        <div class="col-6">
                            <span class="d-block small text-muted mb-1">Disciplina</span>
                            <span id="view-evt-disc" class="fw-medium text-truncate"></span>
                        </div>
                        <div class="col-6">
                            <span class="d-block small text-muted mb-1">Início</span>
                            <span id="view-evt-start" class="fw-medium"></span>
                        </div>
                        <div class="col-6">
                            <span class="d-block small text-muted mb-1">Término</span>
                            <span id="view-evt-end" class="fw-medium"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <a href="<?= Url::to('/planner') ?>" class="btn btn-outline-primary d-none" id="open-task-planner">Abrir no planner</a>
                <button type="button" class="btn btn-primary" id="edit-event">Editar</button>
                <form id="delete-event-form" method="post" action="" onsubmit="return confirm('Excluir evento?');">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = <?= json_encode(Url::to('/calendario/'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const plannerUrl = <?= json_encode(Url::to('/planner'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const localeCode = <?= json_encode(Lang::locale(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const calendarLocale = localeCode === 'en-US' ? 'en' : 'pt-br';
    const labels = {
        noTime: <?= json_encode(Lang::get('calendar.no_time'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        newEvent: <?= json_encode(Lang::get('calendar.new_event'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        createEvent: <?= json_encode(Lang::get('calendar.create_event'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        editEvent: <?= json_encode(Lang::get('calendar.edit_event'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        saveChanges: <?= json_encode(Lang::get('calendar.save_changes'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        noDescription: <?= json_encode(Lang::get('calendar.no_description'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        none: <?= json_encode(Lang::get('calendar.none'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        loadError: <?= json_encode(Lang::get('calendar.load_error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        networkError: <?= json_encode(Lang::get('calendar.network_error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    };
    const eventForm = document.getElementById('calendar-event-form');
    const eventModal = new bootstrap.Modal(document.getElementById('modal-novo-evento'));
    const viewModal = new bootstrap.Modal(document.getElementById('modal-view-evento'));
    let selectedEvent = null;

    function localDateTime(value) {
        return value ? value.replace(' ', 'T').slice(0, 16) : '';
    }
    function displayDateTime(value) {
        return value ? new Date(value.replace(' ', 'T')).toLocaleString(localeCode) : labels.noTime;
    }
    function addReminder(value = '') {
        const row = document.createElement('div');
        row.className = 'input-group';
        row.innerHTML = '<input type="datetime-local" name="lembretes[]" class="form-control" aria-label="Data e horário do lembrete"><button type="button" class="btn btn-outline-danger" aria-label="Remover lembrete"><i class="bi bi-x-lg"></i></button>';
        row.querySelector('input').value = localDateTime(value);
        row.querySelector('button').addEventListener('click', () => row.remove());
        document.getElementById('reminder-fields').appendChild(row);
    }
    function resetEventForm() {
        selectedEvent = null;
        eventForm.reset();
        eventForm.action = <?= json_encode(Url::to('/calendario')) ?>;
        document.getElementById('event-form-title').textContent = labels.newEvent;
        document.getElementById('event-submit').textContent = labels.createEvent;
        document.getElementById('reminder-fields').replaceChildren();
    }
    document.querySelector('[data-bs-target="#modal-novo-evento"]').addEventListener('click', resetEventForm);
    document.getElementById('add-reminder').addEventListener('click', () => addReminder());

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: calendarLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: '<?= Url::to('/calendario/events') ?>',
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showViewItem(info.event.extendedProps.is_task === true, info.event.extendedProps.real_id);
        }
    });
    calendar.render();

    function showViewItem(isTask, id) {
        const endpoint = isTask ? 'tarefa/' + encodeURIComponent(id) + '/view' : encodeURIComponent(id) + '/view';
        fetch(baseUrl + endpoint)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const evt = data.event;
                    document.getElementById('view-evt-title').textContent = evt.titulo;
                    document.getElementById('view-evt-desc').textContent = evt.descricao || labels.noDescription;
                    document.getElementById('view-evt-type').textContent = evt.tipo;
                    document.getElementById('view-evt-disc').textContent = evt.disciplina_nome || labels.none;
                    document.getElementById('view-evt-start').textContent = displayDateTime(evt.data_inicio);
                    document.getElementById('view-evt-end').textContent = displayDateTime(evt.data_fim);
                    document.getElementById('edit-event').classList.toggle('d-none', isTask);
                    document.getElementById('delete-event-form').classList.toggle('d-none', isTask);
                    document.getElementById('open-task-planner').classList.toggle('d-none', !isTask);
                    if (!isTask) document.getElementById('delete-event-form').action = baseUrl + encodeURIComponent(evt.id) + '/excluir';
                    selectedEvent = isTask ? null : evt;
                    viewModal.show();
                } else {
                    alert(data.message || labels.loadError);
                }
            })
            .catch(err => {
                console.error(err);
                alert(labels.networkError);
            });
    }

    const deepParams = new URLSearchParams(window.location.search);
    const deepEvento = deepParams.get('evento');
    const deepTarefa = deepParams.get('tarefa');
    if (deepEvento || deepTarefa) {
        deepParams.delete('evento');
        deepParams.delete('tarefa');
        const deepQuery = deepParams.toString();
        history.replaceState(null, '', window.location.pathname + (deepQuery ? '?' + deepQuery : ''));
        showViewItem(!deepEvento, deepEvento || deepTarefa);
    }

    document.getElementById('edit-event').addEventListener('click', function() {
        if (!selectedEvent) return;
        eventForm.action = baseUrl + encodeURIComponent(selectedEvent.id) + '/editar';
        document.getElementById('event-form-title').textContent = labels.editEvent;
        document.getElementById('event-submit').textContent = labels.saveChanges;
        document.getElementById('event-titulo').value = selectedEvent.titulo || '';
        document.getElementById('event-disciplina').value = selectedEvent.disciplina_id || '';
        document.getElementById('event-tipo').value = selectedEvent.tipo || 'evento';
        document.getElementById('event-inicio').value = localDateTime(selectedEvent.data_inicio);
        document.getElementById('event-fim').value = localDateTime(selectedEvent.data_fim);
        document.getElementById('event-descricao').value = selectedEvent.descricao || '';
        document.getElementById('reminder-fields').replaceChildren();
        (selectedEvent.lembretes || []).forEach(reminder => addReminder(reminder.data_hora_lembrete));
        viewModal.hide();
        eventModal.show();
    });
});
</script>

<style>
#calendar {
    min-height: 600px;
}
@media (max-width: 575.98px) {
    #calendar { min-height: 460px; font-size: .82rem; }
    .fc .fc-toolbar { flex-wrap: wrap; gap: .5rem; }
    .fc .fc-toolbar-title { font-size: 1.1rem; }
}
.fc .fc-button-primary {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
.fc .fc-button-primary:hover,
.fc .fc-button-primary:active,
.fc .fc-button-primary.fc-button-active {
    background-color: #1e4fa9;
    border-color: #1e4fa9;
}
</style>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
