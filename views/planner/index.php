<?php
    use App\Helpers\Url;

    $errors  = $feedback['errors'] ?? [];
    $success = $feedback['success'] ?? null;

    require dirname(__DIR__) . '/layouts/header.php';
?>

<style>
/* Estilos para Drag and Drop */
.task-card {
    cursor: grab;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.task-card:active {
    cursor: grabbing;
}
.task-card.dragging {
    opacity: 0.4;
    transform: scale(0.98);
}
.task-dropzone {
    min-height: 120px;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    border: 2px dashed transparent;
    border-radius: 0.5rem;
}
.task-dropzone.drag-over {
    background-color: rgba(13, 110, 253, 0.08) !important;
    border-color: #0d6efd !important;
}
</style>

<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">

            <!-- Cabeçalho -->
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Gerenciamento de Tarefas</p>
                    <h1>Planner</h1>
                    <p>Organize suas tarefas, prioridades e checklists.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#modal-nova-tarefa">
                        <i class="bi bi-plus-lg"></i><span>Nova Tarefa</span>
                    </button>
                </div>
            </section>

            <!-- Feedback -->
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
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
                            <li><?php echo htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Kanban Board -->
            <div class="row g-4">
                <?php $colunas = ['a_fazer' => 'A Fazer', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído']; ?>
                <?php foreach ($colunas as $statusKey => $statusTitle): ?>
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
                                <h5 class="mb-0 fw-bold"><?php echo $statusTitle ?></h5>
                            </div>
                            <div class="card-body px-3 pb-4">
                                <!-- ZONA DE SOLTURA (DROPZONE) -->
                                <div class="d-flex flex-column gap-3 task-dropzone" data-status="<?php echo $statusKey ?>">
                                    <?php $tasksCount = 0; ?>
                                    <?php foreach ($tasks as $task): ?>
                                        <?php if ($task['status'] === $statusKey): ?>
                                            <?php $tasksCount++; ?>
                                            <!-- CARD ARRASTÁVEL -->
                                            <div class="card border-0 shadow-sm task-card" draggable="true" data-task-id="<?php echo htmlspecialchars((string)$task['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <?php
                                                            $badgeClass = match ($task['prioridade']) {
                                                                'alta'  => 'bg-danger-subtle text-danger',
                                                                'media' => 'bg-warning-subtle text-warning-emphasis',
                                                                'baixa' => 'bg-success-subtle text-success',
                                                                default => 'bg-secondary-subtle text-secondary'
                                                            };
                                                        ?>
                                                        <span class="badge rounded-pill <?php echo $badgeClass ?>"><?php echo ucfirst($task['prioridade']) ?></span>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                                <li><a class="dropdown-item" href="#" onclick="viewTask('<?php echo htmlspecialchars((string)$task['id'], ENT_QUOTES, 'UTF-8') ?>'); return false;"><i class="bi bi-eye"></i> Visualizar</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="editTask('<?php echo htmlspecialchars((string)$task['id'], ENT_QUOTES, 'UTF-8') ?>'); return false;"><i class="bi bi-pencil"></i> Editar</a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="post" action="<?php echo Url::to('/planner/' . rawurlencode((string)$task['id']) . '/excluir') ?>" onsubmit="return confirm('Deseja realmente excluir esta tarefa?')">
                                                                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Excluir</button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($task['titulo'], ENT_QUOTES, 'UTF-8') ?></h6>
                                                    <?php if (! empty($task['disciplina_nome'])): ?>
                                                        <div class="text-muted small mb-2">
                                                            <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background-color:<?php echo htmlspecialchars($task['disciplina_cor'] ?? '#6c757d', ENT_QUOTES, 'UTF-8') ?>"></span>
                                                            <?php echo htmlspecialchars($task['disciplina_nome'], ENT_QUOTES, 'UTF-8') ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex align-items-center gap-3 text-muted small mt-3 task-meta">
                                                        <?php if (! empty($task['data_vencimento'])): ?>
                                                            <div title="Vencimento">
                                                                <i class="bi bi-calendar me-1"></i><?php echo date('d/m/Y', strtotime($task['data_vencimento'])) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ((int) ($task['total_checklist'] ?? 0) > 0): ?>
                                                            <div title="Checklist">
                                                                <i class="bi bi-list-check me-1"></i><?php echo $task['concluidos_checklist'] ?>/<?php echo $task['total_checklist'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ((int) ($task['total_anexos'] ?? 0) > 0): ?>
                                                            <div title="Anexos">
                                                                <i class="bi bi-paperclip me-1"></i><?php echo $task['total_anexos'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($tasksCount === 0): ?>
                                        <div class="text-center text-muted p-4 border border-dashed rounded-3 bg-white bg-opacity-50 empty-tasks-msg">
                                            Nenhuma tarefa
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>
    </div>
</div>

<!-- Modal Nova Tarefa -->
<div class="modal fade" id="modal-nova-tarefa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?php echo Url::to('/planner') ?>">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Nova Tarefa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Disciplina</label>
                            <select name="disciplina_id" class="form-select">
                                <option value="">Sem disciplina</option>
                                <?php foreach ($disciplines as $d): ?>
                                    <option value="<?php echo htmlspecialchars((string)$d['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php echo htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prioridade</label>
                            <select name="prioridade" class="form-select">
                                <option value="baixa">Baixa</option>
                                <option value="media" selected>Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="a_fazer" selected>A Fazer</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluido">Concluído</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Vencimento</label>
                            <input type="datetime-local" name="data_vencimento" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="recorrente" value="1" id="recorrenteCheck">
                        <label class="form-check-label" for="recorrenteCheck">
                            Tarefa recorrente
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Tarefa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Tarefa -->
<div class="modal fade" id="modal-editar-tarefa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="form-editar-tarefa" method="post" action="">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Editar Tarefa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="edit-titulo" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Disciplina</label>
                            <select name="disciplina_id" id="edit-disciplina_id" class="form-select">
                                <option value="">Sem disciplina</option>
                                <?php foreach ($disciplines as $d): ?>
                                    <option value="<?php echo htmlspecialchars((string)$d['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php echo htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prioridade</label>
                            <select name="prioridade" id="edit-prioridade" class="form-select">
                                <option value="baixa">Baixa</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="a_fazer">A Fazer</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluido">Concluído</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Vencimento</label>
                            <input type="datetime-local" name="data_vencimento" id="edit-data_vencimento" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" id="edit-descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="recorrente" value="1" id="edit-recorrente">
                        <label class="form-check-label" for="edit-recorrente">
                            Tarefa recorrente
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar Tarefa -->
<div class="modal fade" id="modal-view-task" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="view-task-title">...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <p id="view-task-desc" class="text-muted mb-4"></p>

                        <h6 class="fw-bold mb-3">Checklist</h6>
                        <div id="view-task-checklist" class="mb-4"></div>
                        <form id="form-add-checklist" method="post" action="" class="d-flex gap-2 mb-4">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="text" name="descricao" class="form-control form-control-sm" placeholder="Novo item..." required>
                            <button type="submit" class="btn btn-sm btn-primary">Adicionar</button>
                        </form>

                        <h6 class="fw-bold mb-3">Anexos</h6>
                        <div id="view-task-anexos" class="mb-3"></div>
                        <form id="form-add-attachment" method="post" action="" class="d-flex gap-2">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <select name="arquivo_id" class="form-select form-select-sm" required>
                                <option value="">Selecionar arquivo...</option>
                                <?php foreach ($files as $f): ?>
                                    <option value="<?php echo htmlspecialchars((string)$f['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php echo htmlspecialchars((string)$f['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Anexar</button>
                        </form>

                        <form id="form-upload-attachment" method="post" action="" enctype="multipart/form-data" class="mt-3">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <label for="attachment-file-input" class="form-label small text-muted mb-1">Ou envie um arquivo do seu dispositivo</label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="arquivo" id="attachment-file-input" class="form-control" accept=".pdf,.epub,.docx,.txt,.png,.jpg,.jpeg">
                                <button type="submit" class="btn btn-outline-success" id="btn-upload-attachment">
                                    <i class="bi bi-cloud-arrow-up me-1"></i>Enviar
                                </button>
                            </div>
                            <div class="form-text">PDF, EPUB, DOCX, TXT, PNG ou JPG de até 20 MB.</div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded-3">
                            <h6 class="fw-bold mb-3">Detalhes</h6>
                            <form id="form-update-status" method="post" action="" class="mb-3">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <label class="form-label small text-muted">Status</label>
                                <select name="status" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                                    <option value="a_fazer">A Fazer</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluido">Concluído</option>
                                </select>
                            </form>
                            <div class="mb-3">
                                <span class="d-block small text-muted mb-1">Prioridade</span>
                                <span id="view-task-priority" class="badge rounded-pill"></span>
                            </div>
                            <div class="mb-3">
                                <span class="d-block small text-muted mb-1">Disciplina</span>
                                <span id="view-task-discipline" class="fw-medium"></span>
                            </div>
                            <div>
                                <span class="d-block small text-muted mb-1">Vencimento</span>
                                <span id="view-task-due" class="fw-medium"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

const CSRF_TOKEN = '<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>';
const PLANNER_URL = "<?php echo Url::to('/planner/') ?>";

/* =======================================================
   REQUISICOES AJAX (MANTÉM O MODAL ABERTO E ATUALIZA)
   ======================================================= */
function postForm(url, data) {
    const body = new FormData();
    body.append('_csrf_token', CSRF_TOKEN);
    Object.keys(data || {}).forEach(key => body.append(key, data[key]));

    return fetch(url, {
        method: 'POST',
        body: body,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(async res => {
        const payload = await res.json().catch(() => null);
        if (!res.ok || !payload || payload.success !== true) {
            const errors = payload && payload.errors ? payload.errors : null;
            const message = errors
                ? Object.values(errors).join(' ')
                : (payload && payload.message) ? payload.message : 'A solicitação expirou. Atualize a página.';
            throw new Error(message);
        }
        return payload;
    });
}

/* Reabre a tarefa no modal usando os dados já devolvidos pela resposta. */
function refreshTaskModal(payload) {
    if (!payload || !payload.task) {
        if (currentTaskId) viewTask(currentTaskId);
        return;
    }
    renderTaskModal(payload.task);
    updateKanbanCounters(payload.task);
}

/* =======================================================
   LOGICA DE ARRASTAR E SOLTAR (DRAG AND DROP)
   ======================================================= */
document.addEventListener('DOMContentLoaded', () => {
    let draggedCard = null;

    // Inicializa eventos nas tarefas
    function initTaskCard(card) {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.dataset.taskId);
            e.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });
    }

    document.querySelectorAll('.task-card').forEach(initTaskCard);

    // Inicializa eventos nas colunas (Dropzones)
    document.querySelectorAll('.task-dropzone').forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', (e) => {
            // Remove destaque apenas se sair da zona principal
            if (!zone.contains(e.relatedTarget)) {
                zone.classList.remove('drag-over');
            }
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');

            if (!draggedCard) return;

            const newStatus = zone.dataset.status;
            const taskId = draggedCard.dataset.taskId;
            const oldZone = draggedCard.parentElement;

            // Se for solto na mesma coluna, cancela
            if (oldZone === zone) return;

            // 1. Move a tarefa visualmente na interface (Update Otimista)
            zone.appendChild(draggedCard);

            // Remodela os blocos de "Nenhuma tarefa"
            const emptyMsgInZone = zone.querySelector('.empty-tasks-msg');
            if (emptyMsgInZone) emptyMsgInZone.remove();

            if (oldZone.querySelectorAll('.task-card').length === 0) {
                oldZone.innerHTML = '<div class="text-center text-muted p-4 border border-dashed rounded-3 bg-white bg-opacity-50 empty-tasks-msg">Nenhuma tarefa</div>';
            }

            // 2. Dispara a atualização silenciosa no banco via Fetch
            postForm(PLANNER_URL + encodeURIComponent(taskId) + "/status", { status: newStatus })
            .catch(err => {
                console.error(err);
                alert('Erro ao mover a tarefa. A página será atualizada.');
                location.reload();
            });
        });
    });
});

/* =======================================================
   FUNÇÕES DOS MODAIS (VIEW E EDIT)
   ======================================================= */
let currentTaskId = null;

function renderTaskModal(task) {
    document.getElementById('view-task-title').textContent = task.titulo;
    document.getElementById('view-task-desc').textContent = task.descricao || 'Sem descrição.';

    let priorityClass = 'bg-secondary-subtle text-secondary';
    if (task.prioridade === 'alta') priorityClass = 'bg-danger-subtle text-danger';
    if (task.prioridade === 'media') priorityClass = 'bg-warning-subtle text-warning-emphasis';
    if (task.prioridade === 'baixa') priorityClass = 'bg-success-subtle text-success';

    const elPriority = document.getElementById('view-task-priority');
    elPriority.className = 'badge rounded-pill ' + priorityClass;
    elPriority.textContent = task.prioridade ? (task.prioridade.charAt(0).toUpperCase() + task.prioridade.slice(1)) : 'Média';

    document.getElementById('view-task-discipline').textContent = task.disciplina_nome || 'Nenhuma';
    document.getElementById('view-task-due').textContent = task.data_vencimento ? new Date(task.data_vencimento.replace(' ', 'T')).toLocaleString() : 'Sem data';

    const encodedId = encodeURIComponent(task.id);
    document.getElementById('form-update-status').action = PLANNER_URL + encodedId + "/status";
    document.getElementById('form-update-status').elements['status'].value = task.status;
    document.getElementById('form-add-checklist').action = PLANNER_URL + encodedId + "/checklist";
    document.getElementById('form-add-attachment').action = PLANNER_URL + encodedId + "/anexar";
    const uploadForm = document.getElementById('form-upload-attachment');
    if (uploadForm) uploadForm.action = PLANNER_URL + encodedId + "/anexar-upload";
    const fileInput = document.getElementById('attachment-file-input');
    if (fileInput) fileInput.value = '';

    // Checklist — cada item é um form com a URL correta (/planner/{tarefa}/checklist/{item}/...)
    let checklistHtml = '';
    if (task.checklist && task.checklist.length > 0) {
        task.checklist.forEach(item => {
            const isChecked = item.concluido == 1 ? 'checked' : '';
            const textClass = item.concluido == 1 ? 'text-decoration-line-through text-muted' : '';
            const nextStatus = item.concluido == 1 ? 0 : 1;
            const itemId = encodeURIComponent(item.id);

            checklistHtml += `
                <div class="d-flex align-items-center justify-content-between mb-2 checklist-row" data-item-id="${itemId}">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0 js-checklist-toggle" type="checkbox"
                               data-item-id="${itemId}" data-next-status="${nextStatus}" ${isChecked}>
                        <span class="${textClass} js-checklist-label">${escapeHtml(item.descricao)}</span>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 js-checklist-delete" data-item-id="${itemId}" title="Remover item">
                        <i class="bi bi-x"></i>
                    </button>
                </div>`;
        });
    } else {
        checklistHtml = '<span class="text-muted small">Nenhum item na checklist.</span>';
    }
    document.getElementById('view-task-checklist').innerHTML = checklistHtml;

    // Anexos — imagens exibem preview; clique abre o arquivo. URL correta: /planner/{tarefa}/anexos/{anexo}/excluir
    let anexosHtml = '';
    if (task.anexos && task.anexos.length > 0) {
        task.anexos.forEach(anexo => {
            const anexoId = encodeURIComponent(anexo.id);
            const fileId = encodeURIComponent(anexo.arquivo_id || '');
            const fileName = escapeHtml(anexo.nome_original || 'Arquivo');
            const isImage = /\\.(png|jpe?g|gif|webp)$/i.test(anexo.nome_original || '');
            const serveUrl = '<?php echo Url::to('/arquivos/') ?>' + (anexo.arquivo_id || '') + '/servir';
            const viewUrl = '<?php echo Url::to('/arquivos/visualizar/') ?>' + (anexo.arquivo_id || '');

            if (isImage) {
                anexosHtml += `
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded-3 js-anexo-row" data-anexo-id="${anexoId}">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <a href="${viewUrl}" target="_blank" rel="noopener" title="Abrir imagem">
                                <img src="${serveUrl}" alt="${fileName}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                            </a>
                            <a href="${viewUrl}" target="_blank" rel="noopener" class="text-decoration-none text-truncate small" title="${fileName}">
                                ${fileName}
                            </a>
                        </div>
                        <button type="button" class="btn btn-sm text-danger p-0 js-anexo-delete" data-anexo-id="${anexoId}" title="Remover anexo">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`;
            } else {
                anexosHtml += `
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded-3 js-anexo-row" data-anexo-id="${anexoId}">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            <a href="<?php echo Url::to('/arquivos/') ?>${fileId}/download" class="text-decoration-none text-truncate small" title="${fileName}">
                                ${fileName}
                            </a>
                        </div>
                        <button type="button" class="btn btn-sm text-danger p-0 js-anexo-delete" data-anexo-id="${anexoId}" title="Remover anexo">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`;
            }
        });
    } else {
        anexosHtml = '<span class="text-muted small">Nenhum anexo.</span>';
    }
    document.getElementById('view-task-anexos').innerHTML = anexosHtml;
}

function viewTask(id) {
    currentTaskId = id;
    fetch(PLANNER_URL + encodeURIComponent(id) + "/view", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderTaskModal(data.task);
                const modalEl = document.getElementById('modal-view-task');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                alert(data.message || 'Erro ao carregar tarefa.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de rede ao carregar tarefa.');
        });
}

/* Delegação de eventos: o conteúdo do modal é re-renderizado dinamicamente,
   então os handlers são registrados uma única vez no documento. */
document.addEventListener('change', (e) => {
    const toggle = e.target.closest('.js-checklist-toggle');
    if (!toggle || !currentTaskId) return;

    const itemId = toggle.dataset.itemId;
    const concluido = toggle.checked ? '1' : '0';
    toggle.disabled = true;

    postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/checklist/' + itemId + '/toggle', { concluido: concluido })
        .then(refreshTaskModal)
        .catch(err => {
            toggle.checked = !toggle.checked;
            alert(err.message);
        })
        .finally(() => { toggle.disabled = false; });
});

document.addEventListener('click', (e) => {
    const delItem = e.target.closest('.js-checklist-delete');
    if (delItem && currentTaskId) {
        if (!confirm('Remover este item da checklist?')) return;
        const itemId = delItem.dataset.itemId;
        delItem.disabled = true;

        postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/checklist/' + itemId + '/excluir', {})
            .then(refreshTaskModal)
            .catch(err => { delItem.disabled = false; alert(err.message); });
        return;
    }

    const delAnexo = e.target.closest('.js-anexo-delete');
    if (delAnexo && currentTaskId) {
        if (!confirm('Remover este anexo da tarefa?')) return;
        const anexoId = delAnexo.dataset.anexoId;
        delAnexo.disabled = true;

        postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/anexos/' + anexoId + '/excluir', {})
            .then(refreshTaskModal)
            .catch(err => { delAnexo.disabled = false; alert(err.message); });
    }
});

/* Impede o submit tradicional (que recarregava a página e fechava o modal). */
document.addEventListener('submit', (e) => {
    const form = e.target;

    if (form.id === 'form-add-checklist') {
        e.preventDefault();
        if (!currentTaskId) return;
        const input = form.elements['descricao'];
        const descricao = (input.value || '').trim();
        if (!descricao) return;

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;

        postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/checklist', { descricao: descricao })
            .then(payload => { input.value = ''; refreshTaskModal(payload); })
            .catch(err => alert(err.message))
            .finally(() => { button.disabled = false; });
        return;
    }

    if (form.id === 'form-add-attachment') {
        e.preventDefault();
        if (!currentTaskId) return;
        const select = form.elements['arquivo_id'];
        if (!select.value) {
            alert('Selecione um arquivo para anexar.');
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;

        postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/anexar', { arquivo_id: select.value })
            .then(payload => {
                select.value = '';
                refreshTaskModal(payload);
            })
            .catch(err => alert(err.message))
            .finally(() => { button.disabled = false; });
        return;
    }

    if (form.id === 'form-upload-attachment') {
        e.preventDefault();
        if (!currentTaskId) return;

        const fileInput = form.querySelector('input[type="file"]');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            alert('Selecione um arquivo para enviar.');
            return;
        }

        const button = document.getElementById('btn-upload-attachment');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

        const body = new FormData();
        body.append('_csrf_token', CSRF_TOKEN);
        body.append('arquivo', fileInput.files[0]);

        fetch(PLANNER_URL + encodeURIComponent(currentTaskId) + '/anexar-upload', {
            method: 'POST',
            body: body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(async (res) => {
                const payload = await res.json().catch(() => null);
                if (!res.ok || !payload || payload.success !== true) {
                    const errors = payload && payload.errors ? payload.errors : null;
                    const message = errors
                        ? Object.values(errors).join(' ')
                        : (payload && payload.message) ? payload.message : 'Não foi possível enviar o arquivo.';
                    throw new Error(message);
                }
                return payload;
            })
            .then((payload) => {
                fileInput.value = '';
                refreshTaskModal(payload);
            })
            .catch((err) => alert(err.message))
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i>Enviar';
            });
        return;
    }

    if (form.id === 'form-update-status') {
        e.preventDefault();
        if (!currentTaskId) return;
        const select = form.elements['status'];

        postForm(PLANNER_URL + encodeURIComponent(currentTaskId) + '/status', { status: select.value })
            .then(payload => refreshTaskModal(payload))
            .catch(err => alert(err.message));
    }
});

/* Atualiza os contadores de checklist/anexos exibidos no card do Kanban. */
function updateKanbanCounters(task) {
    if (!task || !task.id) return;
    const card = document.querySelector('.task-card[data-task-id="' + CSS.escape(String(task.id)) + '"]');
    if (!card) return;

    const meta = card.querySelector('.task-meta');
    if (!meta) return;

    const total = Array.isArray(task.checklist) ? task.checklist.length : 0;
    const done = Array.isArray(task.checklist) ? task.checklist.filter(i => i.concluido == 1).length : 0;
    const anexos = Array.isArray(task.anexos) ? task.anexos.length : 0;

    syncMetaIndicator(meta, 'Checklist', total > 0, () => {
        const box = meta.querySelector('[title="Checklist"]');
        box.innerHTML = '<i class="bi bi-list-check me-1"></i>' + done + '/' + total;
    });

    syncMetaIndicator(meta, 'Anexos', anexos > 0, () => {
        const box = meta.querySelector('[title="Anexos"]');
        box.innerHTML = '<i class="bi bi-paperclip me-1"></i>' + anexos;
    });
}

function syncMetaIndicator(meta, title, visible, update) {
    let box = meta.querySelector('[title="' + title + '"]');

    if (!visible) {
        if (box) box.remove();
        return;
    }

    if (!box) {
        box = document.createElement('div');
        box.title = title;
        meta.appendChild(box);
    }
    update();
}

function editTask(id) {
    fetch(PLANNER_URL + encodeURIComponent(id) + "/view")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                const encodedId = encodeURIComponent(id);

                document.getElementById('form-editar-tarefa').action = PLANNER_URL + encodedId + "/editar";
                document.getElementById('edit-titulo').value = task.titulo || '';
                document.getElementById('edit-disciplina_id').value = task.disciplina_id || '';
                document.getElementById('edit-prioridade').value = task.prioridade || 'media';
                document.getElementById('edit-status').value = task.status || 'a_fazer';
                document.getElementById('edit-descricao').value = task.descricao || '';
                document.getElementById('edit-recorrente').checked = (task.recorrente == 1 || task.recorrente === true);

                if (task.data_vencimento) {
                    document.getElementById('edit-data_vencimento').value = task.data_vencimento.replace(' ', 'T').substring(0, 16);
                } else {
                    document.getElementById('edit-data_vencimento').value = '';
                }

                const modalEl = document.getElementById('modal-editar-tarefa');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                alert(data.message || 'Erro ao carregar dados para edição.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de rede ao carregar tarefa para edição.');
        });
}

(function() {
    const params = new URLSearchParams(window.location.search);
    const tarefaId = params.get('tarefa');
    if (!tarefaId) return;
    params.delete('tarefa');
    const query = params.toString();
    history.replaceState(null, '', window.location.pathname + (query ? '?' + query : ''));
    viewTask(tarefaId);
})();
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>