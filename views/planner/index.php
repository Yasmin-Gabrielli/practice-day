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
                                                    <div class="d-flex align-items-center gap-3 text-muted small mt-3">
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
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded-3">
                            <h6 class="fw-bold mb-3">Detalhes</h6>
                            <form id="form-update-status" method="post" action="" class="mb-3">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <label class="form-label small text-muted">Status</label>
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
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
            const formData = new FormData();
            formData.append('_csrf_token', '<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>');
            formData.append('status', newStatus);

            fetch("<?php echo Url::to('/planner/') ?>" + encodeURIComponent(taskId) + "/status", {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Falha ao atualizar o status');
            })
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
function viewTask(id) {
    fetch("<?php echo Url::to('/planner/') ?>" + encodeURIComponent(id) + "/view")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
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
                document.getElementById('view-task-due').textContent = task.data_vencimento ? new Date(task.data_vencimento).toLocaleString() : 'Sem data';

                const encodedId = encodeURIComponent(id);
                document.getElementById('form-update-status').action = "<?php echo Url::to('/planner/') ?>" + encodedId + "/status";
                document.getElementById('form-update-status').elements['status'].value = task.status;
                document.getElementById('form-add-checklist').action = "<?php echo Url::to('/planner/') ?>" + encodedId + "/checklist";
                document.getElementById('form-add-attachment').action = "<?php echo Url::to('/planner/') ?>" + encodedId + "/anexar";

                // Checklist
                let checklistHtml = '';
                if (task.checklist && task.checklist.length > 0) {
                    task.checklist.forEach(item => {
                        const isChecked = item.concluido == 1 ? 'checked' : '';
                        const textClass = item.concluido == 1 ? 'text-decoration-line-through text-muted' : '';
                        const nextStatus = item.concluido == 1 ? 0 : 1;
                        const itemId = encodeURIComponent(item.id);

                        checklistHtml += `
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <form method="post" action="<?php echo Url::to('/planner/checklist/') ?>${itemId}/toggle">
                                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="concluido" value="${nextStatus}">
                                        <input class="form-check-input mt-0" type="checkbox" onchange="this.form.submit()" ${isChecked}>
                                    </form>
                                    <span class="${textClass}">${escapeHtml(item.descricao)}</span>
                                </div>
                                <form method="post" action="<?php echo Url::to('/planner/checklist/') ?>${itemId}/excluir">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-sm text-danger p-0"><i class="bi bi-x"></i></button>
                                </form>
                            </div>`;
                    });
                } else {
                    checklistHtml = '<span class="text-muted small">Nenhum item na checklist.</span>';
                }
                document.getElementById('view-task-checklist').innerHTML = checklistHtml;

                // Anexos
                let anexosHtml = '';
                if (task.anexos && task.anexos.length > 0) {
                    task.anexos.forEach(anexo => {
                        const anexoId = encodeURIComponent(anexo.id);
                        const fileId = encodeURIComponent(anexo.arquivo_id || anexo.id);
                        const fileName = escapeHtml(anexo.nome_original || 'Arquivo');

                        anexosHtml += `
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded-3">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    <a href="<?php echo Url::to('/arquivos/') ?>${fileId}/download" class="text-decoration-none text-truncate small" title="${fileName}">
                                        ${fileName}
                                    </a>
                                </div>
                                <form method="post" action="<?php echo Url::to('/planner/anexos/') ?>${anexoId}/excluir">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>`;
                    });
                } else {
                    anexosHtml = '<span class="text-muted small">Nenhum anexo.</span>';
                }
                document.getElementById('view-task-anexos').innerHTML = anexosHtml;

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

function editTask(id) {
    fetch("<?php echo Url::to('/planner/') ?>" + encodeURIComponent(id) + "/view")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                const encodedId = encodeURIComponent(id);

                document.getElementById('form-editar-tarefa').action = "<?php echo Url::to('/planner/') ?>" + encodedId + "/editar";
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