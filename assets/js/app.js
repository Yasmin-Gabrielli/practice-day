'use strict';

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
