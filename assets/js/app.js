'use strict';

// ⌘K / Ctrl+K foca a barra de pesquisa global
document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
        const input = document.querySelector('.global-search input[type="search"]');
        if (input) {
            e.preventDefault();
            input.focus();
            input.select();
        }
    }
});

// Inicialização de modais que devem abrir no carregamento (ex: erros de validação)
document.querySelectorAll('[data-open-on-load="true"]').forEach((element) => {
    bootstrap.Modal.getOrCreateInstance(element).show();
});

// Alternância entre visualização em Cards e Lista no Gerenciador de Arquivos
const fileContainer = document.querySelector('[data-file-container]');
const viewButtons = document.querySelectorAll('[data-file-view]');

function setFileView(view) {
    if (!fileContainer) return;
    const cardsView = document.getElementById('files-view-cards');
    const listView = document.getElementById('files-view-list');

    if (view === 'lista') {
        if (cardsView) cardsView.classList.add('d-none');
        if (listView) listView.classList.remove('d-none');
    } else {
        if (listView) listView.classList.add('d-none');
        if (cardsView) cardsView.classList.remove('d-none');
    }

    fileContainer.dataset.view = view;
    viewButtons.forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.fileView === view);
    });

    try {
        localStorage.setItem('practiceday_file_view', view);
    } catch (_) {}
}

if (fileContainer && viewButtons.length > 0) {
    // Restaurar preferência salva se existir
    const savedView = localStorage.getItem('practiceday_file_view');
    const initialView = fileContainer.dataset.view || savedView || 'cards';
    setFileView(initialView);

    viewButtons.forEach((button) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const view = button.dataset.fileView;
            setFileView(view);
        });
    });
}

// Filtro rápido em tempo real para arquivos e pastas
const searchInput = document.getElementById('fm-live-search');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.file-card');
        const rows = document.querySelectorAll('.file-table-view tbody tr');
        const folders = document.querySelectorAll('.folder-card');

        let visibleCards = 0;
        cards.forEach((card) => {
            const text = (card.dataset.searchText || card.textContent).toLowerCase();
            const match = query === '' || text.includes(query);
            card.style.display = match ? '' : 'none';
            if (match) visibleCards++;
        });

        rows.forEach((row) => {
            const text = (row.dataset.searchText || row.textContent).toLowerCase();
            const match = query === '' || text.includes(query);
            row.style.display = match ? '' : 'none';
        });

        folders.forEach((folder) => {
            const text = (folder.dataset.searchText || folder.textContent).toLowerCase();
            const match = query === '' || text.includes(query);
            folder.style.display = match ? '' : 'none';
        });

        const emptySearchState = document.getElementById('fm-empty-search');
        if (emptySearchState) {
            if (query !== '' && visibleCards === 0 && cards.length > 0) {
                emptySearchState.classList.remove('d-none');
            } else {
                emptySearchState.classList.add('d-none');
            }
        }
    });
}

// Manipulação dinâmica de modais para ações de arquivos e pastas
document.addEventListener('click', (e) => {
    // 1. Renomear Arquivo
    const renameBtn = e.target.closest('[data-action="rename-file"]');
    if (renameBtn) {
        e.preventDefault();
        const form = document.getElementById('form-rename-file');
        const input = document.getElementById('rename-file-nome');
        if (form && input) {
            form.action = renameBtn.dataset.url;
            input.value = renameBtn.dataset.nome || '';
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-rename-file'));
            modal.show();
        }
        return;
    }

    // 2. Mover Arquivo
    const moveBtn = e.target.closest('[data-action="move-file"]');
    if (moveBtn) {
        e.preventDefault();
        const form = document.getElementById('form-move-file');
        const pastaSelect = document.getElementById('move-file-pasta');
        const discSelect = document.getElementById('move-file-disciplina');
        const nameLabel = document.getElementById('move-file-name-label');

        if (form) {
            form.action = moveBtn.dataset.url;
            if (pastaSelect) pastaSelect.value = moveBtn.dataset.pastaId || '';
            if (discSelect) discSelect.value = moveBtn.dataset.disciplinaId || '';
            if (nameLabel) nameLabel.textContent = moveBtn.dataset.nome || 'Arquivo';
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-move-file'));
            modal.show();
        }
        return;
    }

    // 3. Editar Tags do Arquivo
    const tagsBtn = e.target.closest('[data-action="edit-tags"]');
    if (tagsBtn) {
        e.preventDefault();
        const form = document.getElementById('form-edit-tags');
        const nameLabel = document.getElementById('edit-tags-name-label');
        if (form) {
            form.action = tagsBtn.dataset.url;
            if (nameLabel) nameLabel.textContent = tagsBtn.dataset.nome || 'Arquivo';
            const activeTags = (tagsBtn.dataset.tags || '').split(',').map(s => s.trim()).filter(Boolean);
            document.querySelectorAll('#form-edit-tags input[name="tags[]"]').forEach(chk => {
                chk.checked = activeTags.includes(chk.value);
            });
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-edit-tags'));
            modal.show();
        }
        return;
    }

    // 4. Histórico do Arquivo (AJAX)
    const historyBtn = e.target.closest('[data-action="view-history"]');
    if (historyBtn) {
        e.preventDefault();
        const modalEl = document.getElementById('modal-history');
        const titleEl = document.getElementById('history-file-title');
        const metaEl = document.getElementById('history-file-meta');
        const listEl = document.getElementById('history-timeline-list');
        const spinner = document.getElementById('history-spinner');

        if (modalEl && listEl && spinner) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            spinner.classList.remove('d-none');
            listEl.innerHTML = '';
            if (titleEl) titleEl.textContent = historyBtn.dataset.nome || 'Histórico do Arquivo';
            if (metaEl) metaEl.textContent = 'Carregando eventos...';

            fetch(historyBtn.dataset.url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    spinner.classList.add('d-none');
                    if (!data.success) {
                        listEl.innerHTML = '<div class="alert alert-danger mb-0">Não foi possível carregar o histórico.</div>';
                        return;
                    }
                    if (metaEl && data.file) {
                        metaEl.textContent = `${data.file.disciplina_nome} · ${data.file.pasta_nome} · ${(data.file.tamanho_bytes / 1024).toFixed(1)} KB`;
                    }
                    if (!data.history || data.history.length === 0) {
                        listEl.innerHTML = '<p class="text-secondary small mb-0">Nenhum evento registrado no histórico.</p>';
                        return;
                    }

                    const actionBadgeMap = {
                        'criado': { label: 'Criado', bg: '#ecfdf5', color: '#059669', bullet: '#10b981' },
                        'renomeado': { label: 'Renomeado', bg: '#eff6ff', color: '#2563eb', bullet: '#3b82f6' },
                        'movido': { label: 'Movido', bg: '#eef2ff', color: '#4f46e5', bullet: '#6366f1' },
                        'modificado': { label: 'Modificado', bg: '#fffbeb', color: '#d97706', bullet: '#f59e0b' },
                        'restaurado': { label: 'Restaurado', bg: '#faf5ff', color: '#9333ea', bullet: '#a855f7' }
                    };

                    let html = '<div class="timeline-list">';
                    data.history.forEach(item => {
                        const style = actionBadgeMap[item.acao] || { label: item.acao, bg: '#f1f5f9', color: '#475569', bullet: '#64748b' };
                        const dateFormatted = new Date(item.criado_em.replace(' ', 'T')).toLocaleString('pt-BR');
                        html += `
                            <div class="timeline-item">
                                <span class="timeline-bullet" style="--bullet-color: ${style.bullet}"></span>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-badge" style="background: ${style.bg}; color: ${style.color}">${style.label}</span>
                                        <span class="timeline-date"><i class="bi bi-clock me-1"></i>${dateFormatted}</span>
                                    </div>
                                    <p class="timeline-details">${item.detalhes ? escapeHtml(item.detalhes) : 'Sem detalhes adicionais'}</p>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    listEl.innerHTML = html;
                })
                .catch(err => {
                    spinner.classList.add('d-none');
                    listEl.innerHTML = `<div class="alert alert-danger mb-0">Erro ao carregar histórico: ${escapeHtml(err.message)}</div>`;
                });
        }
        return;
    }

    // 5. Renomear Pasta
    const renameFolderBtn = e.target.closest('[data-action="rename-folder"]');
    if (renameFolderBtn) {
        e.preventDefault();
        const form = document.getElementById('form-rename-folder');
        const input = document.getElementById('rename-folder-nome');
        if (form && input) {
            form.action = renameFolderBtn.dataset.url;
            input.value = renameFolderBtn.dataset.nome || '';
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-rename-folder'));
            modal.show();
        }
        return;
    }

    // 6. Mover Pasta
    const moveFolderBtn = e.target.closest('[data-action="move-folder"]');
    if (moveFolderBtn) {
        e.preventDefault();
        const form = document.getElementById('form-move-folder');
        const select = document.getElementById('move-folder-parent');
        const nameLabel = document.getElementById('move-folder-name-label');
        if (form) {
            form.action = moveFolderBtn.dataset.url;
            if (nameLabel) nameLabel.textContent = moveFolderBtn.dataset.nome || 'Pasta';
            if (select) {
                select.value = moveFolderBtn.dataset.parentId || '';
                // Desabilita a própria pasta no select para evitar ciclo óbvio
                Array.from(select.options).forEach(opt => {
                    opt.disabled = (opt.value === moveFolderBtn.dataset.id);
                });
            }
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-move-folder'));
            modal.show();
        }
        return;
    }

    // 7. Excluir Pasta
    const deleteFolderBtn = e.target.closest('[data-action="delete-folder"]');
    if (deleteFolderBtn) {
        e.preventDefault();
        const form = document.getElementById('form-delete-folder');
        const nameLabel = document.getElementById('delete-folder-name-label');
        if (form) {
            form.action = deleteFolderBtn.dataset.url;
            if (nameLabel) nameLabel.textContent = deleteFolderBtn.dataset.nome || 'Pasta';
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-delete-folder'));
            modal.show();
        }
        return;
    }

    // 8. Nova Subpasta (predefine a pasta pai)
    const subfolderBtn = e.target.closest('[data-action="new-subfolder"]');
    if (subfolderBtn) {
        e.preventDefault();
        const parentSelect = document.getElementById('new-folder-parent');
        const discSelect = document.getElementById('new-folder-disciplina');
        if (parentSelect) parentSelect.value = subfolderBtn.dataset.parentId || '';
        if (discSelect && subfolderBtn.dataset.disciplinaId) discSelect.value = subfolderBtn.dataset.disciplinaId;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-new-folder'));
        modal.show();
        return;
    }
});

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Garante que o item ativo da barra de navegação lateral esteja visível ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    const activeSidebarLink = document.querySelector('.sidebar-scroll .sidebar-link.active');
    if (activeSidebarLink) {
        activeSidebarLink.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
});

// Seletor de ícone de disciplina (mostra o ícone em vez do nome da classe)
document.querySelectorAll('[data-icon-picker]').forEach((picker) => {
    const input = document.getElementById(picker.dataset.iconPicker);
    if (!input) return;

    const items = picker.querySelectorAll('.icon-picker-item');
    const sync = () => {
        items.forEach((item) => item.classList.toggle('is-active', item.dataset.icon === input.value));
    };

    items.forEach((item) => {
        item.addEventListener('click', () => {
            input.value = item.dataset.icon;
            sync();
        });
    });

    if (!input.value && items.length) {
        input.value = items[0].dataset.icon;
    }
    sync();
});

/* =========================================================
   MODAL DE NOTIFICAÇÕES (sino do topo)
   O feed é carregado via JSON quando o modal abre, evitando
   consultas extras em toda página.
   ========================================================= */
(function () {
    const modalEl = document.getElementById('modal-notifications');
    if (!modalEl) return;

    const bodyEl = document.getElementById('notif-modal-body');
    const subtitleEl = document.getElementById('notif-modal-subtitle');
    const markAllBtn = document.getElementById('notif-mark-all');

    const feedUrl = modalEl.dataset.feedUrl;
    const markUrl = modalEl.dataset.markUrl;
    const markAllUrl = modalEl.dataset.markAllUrl;
    const csrfToken = modalEl.dataset.csrfToken || '';

    /* Base do app (quando hospedado em subdiretório). */
    const footerLink = modalEl.querySelector('.modal-footer a');
    const basePath = ((footerLink && footerLink.getAttribute('href')) || '/notificacoes').replace(/\/notificacoes$/, '');
    const appUrl = (path) => basePath + path;

    const GROUP_META = {
        tarefas_vencidas: { label: 'Tarefas Vencidas', icon: 'bi-exclamation-circle-fill', color: 'danger' },
        tarefas_proximas: { label: 'Vencimento Próximo', icon: 'bi-calendar-event-fill', color: 'warning' },
        eventos: { label: 'Eventos Próximos', icon: 'bi-calendar3', color: 'primary' },
        lembretes: { label: 'Lembretes', icon: 'bi-bell-fill', color: 'info' }
    };

    function fmtRelDate(raw) {
        const ts = raw ? Date.parse(String(raw).replace(' ', 'T')) : NaN;
        if (isNaN(ts)) return raw ? escapeHtml(String(raw)) : '—';

        const now = Date.now();
        const diff = Math.round((ts - now) / 1000);
        const abs = Math.abs(diff);

        if (abs < 60) return diff < 0 ? 'Agora mesmo' : 'Em instantes';
        if (abs < 3600) { const m = Math.round(abs / 60); return diff < 0 ? 'Há ' + m + ' min' : 'Em ' + m + ' min'; }
        if (abs < 86400) { const h = Math.round(abs / 3600); return diff < 0 ? 'Há ' + h + 'h' : 'Em ' + h + 'h'; }
        if (abs < 86400 * 7) { const d = Math.round(abs / 86400); return diff < 0 ? 'Há ' + d + ' dias' : 'Em ' + d + ' dias'; }
        return new Date(ts).toLocaleDateString();
    }

    function notifyPost(url, data) {
        const body = new FormData();
        body.append('_csrf_token', csrfToken);
        Object.keys(data || {}).forEach((key) => body.append(key, data[key]));

        return fetch(url, {
            method: 'POST',
            body: body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(async (res) => {
            const payload = await res.json().catch(() => null);
            if (!res.ok || !payload || payload.success !== true) {
                const errors = payload && payload.errors ? payload.errors : null;
                throw new Error(errors ? Object.values(errors).join(' ') : 'Não foi possível concluir a ação.');
            }
            return payload;
        });
    }

    function updateBell(count) {
        const bell = document.querySelector('.notif-bell-btn');
        if (!bell) return;
        const icon = bell.querySelector('i');
        let badge = bell.querySelector('.notif-badge');

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notif-badge';
                bell.appendChild(badge);
            }
            badge.textContent = count <= 99 ? String(count) : '99+';
            bell.classList.add('has-pending');
            if (icon) icon.className = 'bi bi-bell-fill';
        } else {
            if (badge) badge.remove();
            bell.classList.remove('has-pending');
            if (icon) icon.className = 'bi bi-bell';
        }
    }

    function updateSubtitle(pendingCount) {
        if (!subtitleEl) return;
        subtitleEl.textContent = pendingCount > 0
            ? (pendingCount === 1 ? '1 notificação pendente' : pendingCount + ' notificações pendentes')
            : 'Tudo em dia!';
    }

    function cardHtml(item, meta) {
        const isRead = !!item.read;
        const key = escapeHtml(String(item.key || ''));
        const url = appUrl(String(item.url || '/'));
        const sub = escapeHtml(String(item.subtitle || ''));
        const label = escapeHtml(String(item.label || ''));
        const title = escapeHtml(String(item.title || 'Notificação'));

        return '<div class="notif-card ' + (isRead ? 'notif-card--read' : 'notif-card--unread') + '" data-notif-key="' + key + '">' +
            '<div class="notif-card-icon-wrap text-' + meta.color + ' bg-' + meta.color + ' bg-opacity-10">' +
            '<i class="bi ' + escapeHtml(String(item.icon || meta.icon)) + '"></i></div>' +
            '<div class="notif-card-body">' +
            '<div class="notif-card-label">' + label + '</div>' +
            '<div class="notif-card-title"><a href="' + escapeHtml(url) + '" class="notif-card-link">' + title + '</a>' +
            (isRead ? '' : '<span class="notif-unread-dot" title="Não lida"></span>') + '</div>' +
            (sub ? '<div class="notif-card-sub">' + sub + '</div>' : '') +
            '<div class="notif-card-date"><i class="bi bi-clock me-1"></i>' + fmtRelDate(String(item.occurred_at || '')) + '</div>' +
            '</div>' +
            '<div class="notif-card-actions">' +
            '<a href="' + escapeHtml(url) + '" class="btn btn-sm btn-outline-secondary notif-action-btn" title="Ver detalhes"><i class="bi bi-arrow-right"></i></a>' +
            (isRead ? '' :
                '<button type="button" class="btn btn-sm btn-outline-primary notif-action-btn js-notif-read" title="Marcar como lida"><i class="bi bi-check2"></i></button>') +
            '</div></div>';
    }

    function render(feed) {
        const groups = feed.groups || {};
        const pendingCount = Number(feed.pending_count || 0);
        let html = '';
        let total = 0;

        Object.keys(GROUP_META).forEach((key) => {
            const items = groups[key] || [];
            if (!items.length) return;
            const meta = GROUP_META[key];
            total += items.length;
            html += '<div class="notif-group-section">' +
                '<div class="notif-group-header">' +
                '<span class="notif-group-icon text-' + meta.color + '"><i class="bi ' + meta.icon + '"></i></span>' +
                '<h3 class="notif-group-title">' + meta.label + '</h3>' +
                '<span class="notif-group-count badge bg-' + meta.color + ' bg-opacity-15 text-' + meta.color + '">' + items.length + '</span>' +
                '</div><div class="notif-card-list">' +
                items.map((item) => cardHtml(item, meta)).join('') +
                '</div></div>';
        });

        if (total === 0) {
            html = '<div class="notif-empty-state py-3">' +
                '<div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>' +
                '<h3 class="fs-6 fw-bold mb-1">Sem notificações</h3>' +
                '<p class="small text-muted mb-0">Nenhuma tarefa vencida, evento próximo ou lembrete agendado.</p>' +
                '</div>';
        }

        if (bodyEl) bodyEl.innerHTML = html;
        updateSubtitle(pendingCount);
        if (markAllBtn) markAllBtn.classList.toggle('d-none', pendingCount <= 0);
        updateBell(pendingCount);
    }

    function showLoading() {
        if (!bodyEl) return;
        bodyEl.innerHTML = '<div class="text-center text-muted small py-4">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Carregando notificações...</div>';
    }

    function loadFeed() {
        showLoading();
        fetch(feedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((res) => res.json())
            .then((payload) => {
                if (payload && payload.success) {
                    render(payload);
                } else {
                    throw new Error('Não foi possível carregar as notificações.');
                }
            })
            .catch(() => {
                if (bodyEl) {
                    bodyEl.innerHTML = '<div class="text-center text-muted small py-4">' +
                        '<i class="bi bi-exclamation-circle me-1"></i>Não foi possível carregar as notificações.</div>';
                }
            });
    }

    modalEl.addEventListener('show.bs.modal', loadFeed);

    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            markAllBtn.disabled = true;
            notifyPost(markAllUrl, {})
                .then((payload) => {
                    updateBell(Number(payload.pending_count || 0));
                    render({ groups: {}, pending_count: 0 });
                })
                .catch((err) => window.alert(err.message))
                .finally(() => { markAllBtn.disabled = false; });
        });
    }

    document.addEventListener('click', (e) => {
        const readBtn = e.target.closest('.js-notif-read');
        if (!readBtn || !modalEl.contains(readBtn)) return;

        const card = readBtn.closest('.notif-card');
        if (!card) return;
        const key = card.dataset.notifKey || '';
        if (!key) return;

        readBtn.disabled = true;
        notifyPost(markUrl, { notification_key: key, redirect: '/notificacoes' })
            .then((payload) => {
                card.classList.remove('notif-card--unread');
                card.classList.add('notif-card--read');
                const dot = card.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
                readBtn.remove();

                const pending = Number(payload.pending_count || 0);
                updateBell(pending);
                updateSubtitle(pending);
                if (markAllBtn) markAllBtn.classList.toggle('d-none', pending <= 0);

                if (pending === 0 && card.closest('.notif-group-section')) {
                    // Última da seção: recarrega para exibir o estado vazio.
                    loadFeed();
                }
            })
            .catch((err) => { readBtn.disabled = false; window.alert(err.message); });
    });
})();

/* =========================================================
   ARQUIVOS — na raiz exibe apenas pastas; os arquivos só
   aparecem dentro de uma pasta (ou quando o usuário busca).
   ========================================================= */
(function () {
    const container = document.querySelector('[data-file-container]');
    if (!container || container.dataset.rootFoldersOnly !== '1') return;

    const toggleBtn = document.getElementById('fm-root-files-toggle');
    const searchInput = document.getElementById('fm-live-search');
    const count = Number(container.dataset.filesCount || 0);
    let expanded = false;

    function sync() {
        const hasQuery = !!(searchInput && searchInput.value.trim() !== '');
        const show = expanded || hasQuery;
        container.classList.toggle('d-none', !show);
        if (toggleBtn) {
            toggleBtn.innerHTML = show
                ? '<i class="bi bi-eye-slash me-1"></i>Ocultar arquivos'
                : '<i class="bi bi-folder2-open me-1"></i>Ver todos os arquivos (' + count + ')';
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            expanded = !expanded;
            sync();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', sync);
    }

    sync();
})();
