<?php
use App\Helpers\Url;

$errors = $feedback['errors'] ?? [];
$success = $feedback['success'] ?? null;
$currentFolderId = $currentFolder['id'] ?? null;
$isTrash = ($currentFilter === 'lixeira');
$isFavorites = ($currentFilter === 'favoritos');

// Helper para formatar tamanho em bytes
$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
};

// Helper para classe CSS do tipo de arquivo
$getFileTypeClass = static function (?string $ext): string {
    return match (strtolower((string) $ext)) {
        'pdf' => 'file-pdf',
        'docx' => 'file-docx',
        'epub' => 'file-epub',
        'txt' => 'file-txt',
        'png' => 'file-png',
        'jpg', 'jpeg' => 'file-jpg',
        default => 'file-docx',
    };
};

// Helper para ícone Bootstrap do tipo de arquivo
$getFileIcon = static function (?string $ext): string {
    return match (strtolower((string) $ext)) {
        'pdf' => 'bi-filetype-pdf',
        'docx' => 'bi-filetype-docx',
        'epub' => 'bi-book-half',
        'txt' => 'bi-filetype-txt',
        'png' => 'bi-filetype-png',
        'jpg', 'jpeg' => 'bi-filetype-jpg',
        default => 'bi-file-earmark-text',
    };
};

$isViewable = static function (?string $ext): bool {
    return in_array(strtolower((string) $ext), ['pdf', 'epub', 'txt', 'png', 'jpg', 'jpeg', 'docx'], true);
};

require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        
        <main class="page-content">
            <!-- Cabeçalho Principal -->
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Acervo Digital & Organização</p>
                    <h1>Meus Arquivos</h1>
                    <p>Materiais armazenados com segurança, pastas organizadas e histórico de modificações.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#modal-new-tag">
                        <i class="bi bi-tag"></i><span>Nova Tag</span>
                    </button>
                    <button class="btn btn-light d-inline-flex align-items-center gap-2 border" type="button" data-bs-toggle="modal" data-bs-target="#modal-new-folder">
                        <i class="bi bi-folder-plus text-warning"></i><span>Nova Pasta</span>
                    </button>
                    <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#modal-upload">
                        <i class="bi bi-cloud-arrow-up"></i><span>Upload</span>
                    </button>
                </div>
            </section>

            <!-- Alertas e Feedback -->
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
                        <strong>Não foi possível concluir a operação:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $field => $msg): ?>
                            <li><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Estatísticas de Armazenamento -->
            <section class="fm-stats-row">
                <article class="fm-stat-card">
                    <span class="fm-stat-icon icon-blue"><i class="bi bi-files"></i></span>
                    <div>
                        <strong><?= (int) ($stats['total_arquivos'] ?? 0) ?></strong>
                        <small>Arquivos ativos</small>
                    </div>
                </article>
                <article class="fm-stat-card">
                    <span class="fm-stat-icon icon-purple"><i class="bi bi-hdd-network"></i></span>
                    <div>
                        <strong><?= $formatBytes((int) ($stats['total_bytes'] ?? 0)) ?></strong>
                        <small>Espaço utilizado</small>
                    </div>
                </article>
                <article class="fm-stat-card">
                    <span class="fm-stat-icon icon-orange"><i class="bi bi-star-fill text-warning"></i></span>
                    <div>
                        <strong><?= (int) ($stats['total_favoritos'] ?? 0) ?></strong>
                        <small>Favoritos</small>
                    </div>
                </article>
                <article class="fm-stat-card">
                    <span class="fm-stat-icon icon-green"><i class="bi bi-trash3 text-danger"></i></span>
                    <div>
                        <strong><?= (int) ($stats['total_lixeira'] ?? 0) ?></strong>
                        <small>Itens na lixeira</small>
                    </div>
                </article>
            </section>

            <!-- Breadcrumbs de Navegação -->
            <nav class="fm-breadcrumbs" aria-label="Navegação em pastas">
                <a class="fm-breadcrumb-item" href="<?= Url::to('/arquivos') ?>">
                    <i class="bi bi-house-door-fill text-primary"></i><span>Raiz</span>
                </a>
                <?php if ($ancestors !== []): ?>
                    <?php foreach ($ancestors as $index => $anc): ?>
                        <span class="fm-breadcrumb-separator"><i class="bi bi-chevron-right"></i></span>
                        <?php if ($index === count($ancestors) - 1): ?>
                            <span class="fm-breadcrumb-item active" aria-current="page">
                                <i class="bi bi-folder2-open text-warning me-1"></i><?= htmlspecialchars($anc['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php else: ?>
                            <a class="fm-breadcrumb-item" href="<?= Url::to('/arquivos?pasta=' . rawurlencode($anc['id'])) ?>">
                                <i class="bi bi-folder-fill text-warning me-1"></i><?= htmlspecialchars($anc['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <!-- Abas Rápidas (Todos, Favoritos, Lixeira) -->
            <div class="fm-tabs-bar">
                <div class="fm-tabs">
                    <a class="fm-tab <?= ($currentFilter === 'todos' && !$currentFolderId) ? 'active' : '' ?>" href="<?= Url::to('/arquivos') ?>">
                        <i class="bi bi-grid-fill"></i><span>Todos</span>
                        <span class="fm-tab-count"><?= (int) ($stats['total_arquivos'] ?? 0) ?></span>
                    </a>
                    <a class="fm-tab <?= $isFavorites ? 'active' : '' ?>" href="<?= Url::to('/arquivos?filtro=favoritos') ?>">
                        <i class="bi bi-star-fill text-warning"></i><span>Favoritos</span>
                        <span class="fm-tab-count"><?= (int) ($stats['total_favoritos'] ?? 0) ?></span>
                    </a>
                    <a class="fm-tab <?= $isTrash ? 'active' : '' ?>" href="<?= Url::to('/arquivos?filtro=lixeira') ?>">
                        <i class="bi bi-trash3 text-danger"></i><span>Lixeira</span>
                        <span class="fm-tab-count"><?= (int) ($stats['total_lixeira'] ?? 0) ?></span>
                    </a>
                </div>

                <!-- Barra de Ações e Filtros -->
                <div class="d-flex align-items-center gap-2">
                    <div class="fm-view-switcher" role="group" aria-label="Visualização">
                        <button class="fm-view-btn <?= ($currentView === 'cards') ? 'active' : '' ?>" type="button" data-file-view="cards" title="Visualização em cards">
                            <i class="bi bi-grid"></i>
                        </button>
                        <button class="fm-view-btn <?= ($currentView === 'lista') ? 'active' : '' ?>" type="button" data-file-view="lista" title="Visualização em lista">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toolbar de Busca e Disciplina -->
            <div class="fm-toolbar">
                <div class="fm-search-wrap">
                    <i class="bi bi-search"></i>
                    <input id="fm-live-search" type="search" placeholder="Buscar por nome, extensão ou disciplina..." value="<?= htmlspecialchars($currentSearch, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="fm-filters">
                    <form method="get" action="<?= Url::to('/arquivos') ?>" class="d-flex gap-2 m-0">
                        <?php if ($currentFolderId): ?>
                            <input type="hidden" name="pasta" value="<?= htmlspecialchars($currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                        <?php if ($currentFilter !== 'todos'): ?>
                            <input type="hidden" name="filtro" value="<?= htmlspecialchars($currentFilter, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                        <select class="fm-select" name="disciplina" onchange="this.form.submit()">
                            <option value="">Todas as disciplinas</option>
                            <?php foreach ($disciplines as $d): ?>
                                <option value="<?= htmlspecialchars((string) $d['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentDiscipline === (string) $d['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $d['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($currentDiscipline) || !empty($currentSearch)): ?>
                            <a class="btn btn-sm btn-outline-secondary d-flex align-items-center" href="<?= Url::to('/arquivos' . ($currentFolderId ? '?pasta=' . rawurlencode($currentFolderId) : ($currentFilter !== 'todos' ? '?filtro=' . rawurlencode($currentFilter) : ''))) ?>" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Banner Informativo da Lixeira -->
            <?php if ($isTrash): ?>
                <div class="trash-alert-banner">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        <strong>Lixeira Segura:</strong> Arquivos aqui presentes podem ser restaurados para a listagem normal ou destruídos em definitivo.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Seção de Pastas (Exibida na Raiz ou Dentro de Pasta, exceto Lixeira/Favoritos) -->
            <?php if (!$isTrash && !$isFavorites && $folders !== []): ?>
                <section class="folder-section">
                    <div class="folder-section-header">
                        <h2>Pastas <?= $currentFolderId ? 'e Subpastas' : '' ?> (<?= count($folders) ?>)</h2>
                    </div>
                    <div class="folder-grid">
                        <?php foreach ($folders as $folder): ?>
                            <?php
                            $fColor = !empty($folder['disciplina_cor']) && preg_match('/^#[0-9a-fA-F]{6}$/', $folder['disciplina_cor'])
                                ? $folder['disciplina_cor']
                                : '#e5aa31';
                            ?>
                            <div class="folder-card" data-search-text="<?= htmlspecialchars($folder['nome'] . ' ' . ($folder['disciplina_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <a class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-grow-1 min-w-0" href="<?= Url::to('/arquivos?pasta=' . rawurlencode($folder['id'])) ?>">
                                    <span class="folder-card-icon" style="background: color-mix(in srgb, <?= htmlspecialchars($fColor, ENT_QUOTES, 'UTF-8') ?> 15%, white); color: <?= htmlspecialchars($fColor, ENT_QUOTES, 'UTF-8') ?>;">
                                        <i class="bi bi-folder-fill"></i>
                                    </span>
                                    <div class="folder-card-info">
                                        <span class="folder-card-title"><?= htmlspecialchars($folder['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <div class="folder-card-meta">
                                            <span><i class="bi bi-file-earmark"></i><?= (int) ($folder['total_arquivos'] ?? 0) ?></span>
                                            <?php if (!empty($folder['total_subpastas'])): ?>
                                                <span>· <i class="bi bi-folder2"></i><?= (int) $folder['total_subpastas'] ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($folder['disciplina_nome'])): ?>
                                                <span class="text-truncate">· <?= htmlspecialchars($folder['disciplina_nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>

                                <div class="folder-card-actions dropdown">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opções da pasta">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="<?= Url::to('/arquivos?pasta=' . rawurlencode($folder['id'])) ?>">
                                                <i class="bi bi-box-arrow-in-right"></i>Abrir pasta
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button" data-action="new-subfolder" data-parent-id="<?= htmlspecialchars($folder['id'], ENT_QUOTES, 'UTF-8') ?>" data-disciplina-id="<?= htmlspecialchars((string) ($folder['disciplina_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-folder-plus text-warning"></i>Nova subpasta
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button" data-action="rename-folder" data-url="<?= Url::to('/arquivos/pastas/' . rawurlencode($folder['id']) . '/renomear') ?>" data-nome="<?= htmlspecialchars($folder['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-pencil"></i>Renomear
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button" data-action="move-folder" data-id="<?= htmlspecialchars($folder['id'], ENT_QUOTES, 'UTF-8') ?>" data-url="<?= Url::to('/arquivos/pastas/' . rawurlencode($folder['id']) . '/mover') ?>" data-nome="<?= htmlspecialchars($folder['nome'], ENT_QUOTES, 'UTF-8') ?>" data-parent-id="<?= htmlspecialchars((string) ($folder['pasta_pai_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-arrows-move"></i>Mover
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" type="button" data-action="delete-folder" data-url="<?= Url::to('/arquivos/pastas/' . rawurlencode($folder['id']) . '/excluir') ?>" data-nome="<?= htmlspecialchars($folder['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-trash3"></i>Excluir pasta
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Mensagem de Busca Vazia (Dinâmica) -->
            <div id="fm-empty-search" class="empty-module-state d-none mb-4">
                <span><i class="bi bi-search"></i></span>
                <h2>Nenhum resultado encontrado</h2>
                <p>Nenhum arquivo ou pasta corresponde ao termo pesquisado.</p>
            </div>

            <!-- Listagem de Arquivos -->
            <div data-file-container data-view="<?= htmlspecialchars($currentView, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($files === []): ?>
                    <!-- Estados Vazios Específicos -->
                    <?php if ($isTrash): ?>
                        <section class="empty-module-state">
                            <span><i class="bi bi-trash3 text-muted"></i></span>
                            <h2>Sua lixeira está vazia</h2>
                            <p>Nenhum arquivo excluído recentemente. Arquivos descartados permanecerão aqui por até 30 dias.</p>
                            <a class="btn btn-outline-primary" href="<?= Url::to('/arquivos') ?>">Voltar aos arquivos</a>
                        </section>
                    <?php elseif ($isFavorites): ?>
                        <section class="empty-module-state">
                            <span><i class="bi bi-star text-warning"></i></span>
                            <h2>Nenhum arquivo favorito</h2>
                            <p>Marque arquivos com a estrela para acessá-los rapidamente nesta seção.</p>
                            <a class="btn btn-outline-primary" href="<?= Url::to('/arquivos') ?>">Explorar todos os arquivos</a>
                        </section>
                    <?php elseif ($currentFolderId): ?>
                        <section class="empty-module-state">
                            <span><i class="bi bi-folder2-open text-warning"></i></span>
                            <h2>Esta pasta está vazia</h2>
                            <p>Faça upload de materiais diretamente nesta pasta ou crie uma nova subpasta.</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light border" type="button" data-action="new-subfolder" data-parent-id="<?= htmlspecialchars($currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-folder-plus me-1 text-warning"></i>Nova subpasta
                                </button>
                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-upload">
                                    <i class="bi bi-cloud-arrow-up me-1"></i>Enviar arquivo
                                </button>
                            </div>
                        </section>
                    <?php else: ?>
                        <section class="empty-module-state">
                            <span><i class="bi bi-cloud-arrow-up text-primary"></i></span>
                            <h2>Você ainda não possui arquivos</h2>
                            <p>Envie PDFs, EPUBs, DOCX, TXT ou imagens para organizar seus estudos com disciplinas e pastas.</p>
                            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-upload">
                                <i class="bi bi-plus-lg me-1"></i>Fazer primeiro upload
                            </button>
                        </section>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- VISUALIZAÇÃO EM CARDS (GRID) -->
                    <section id="files-view-cards" class="files-grid <?= ($currentView === 'lista') ? 'd-none' : '' ?>">
                        <?php foreach ($files as $file): ?>
                            <?php
                            $typeClass = $getFileTypeClass($file['extensao']);
                            $iconName = $getFileIcon($file['extensao']);
                            $isFav = !empty($file['favorito_real']) || !empty($file['favorito']);
                            $tagIdsString = implode(',', array_column($file['tags'] ?? [], 'id'));
                            ?>
                            <article class="file-card" data-search-text="<?= htmlspecialchars($file['nome_original'] . ' ' . $file['extensao'] . ' ' . ($file['disciplina_nome'] ?? '') . ' ' . ($file['pasta_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="file-card-top">
                                    <span class="file-badge-type <?= $typeClass ?>">
                                        <i class="bi <?= $iconName ?>"></i><?= htmlspecialchars(strtoupper((string) $file['extensao']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    
                                    <?php if (!$isTrash): ?>
                                        <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/favorito') ?>" class="m-0">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="favorito" value="<?= $isFav ? '0' : '1' ?>">
                                            <button class="btn-fav <?= $isFav ? 'active' : '' ?>" type="submit" title="<?= $isFav ? 'Remover dos favoritos' : 'Favoritar arquivo' ?>">
                                                <i class="bi bi-star<?= $isFav ? '-fill' : '' ?>"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div class="file-card-visual <?= $typeClass ?>">
                                    <i class="bi <?= $iconName ?>"></i>
                                </div>

                                <strong class="file-card-title" title="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                </strong>

                                <div class="file-card-meta">
                                    <div class="file-meta-row">
                                        <span>
                                            <i class="bi bi-mortarboard me-1 text-primary"></i>
                                            <?= htmlspecialchars((string) ($file['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span><?= $formatBytes((int) $file['tamanho_bytes']) ?></span>
                                    </div>
                                    <div class="file-meta-row">
                                        <span>
                                            <i class="bi bi-folder me-1 text-warning"></i>
                                            <?= htmlspecialchars((string) ($file['pasta_nome'] ?? 'Raiz'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span><?= htmlspecialchars((new \DateTimeImmutable($file['criado_em']))->format('d/m/Y'), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>

                                <!-- Tags do Arquivo -->
                                <div class="file-card-tags">
                                    <?php foreach (($file['tags'] ?? []) as $t): ?>
                                        <span class="file-tag" style="background: color-mix(in srgb, <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?> 15%, white); color: <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?>;">
                                            <i style="background: <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?>"></i><?= htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Rodapé do Card com Ações -->
                                <div class="file-card-footer">
                                    <?php if ($isTrash): ?>
                                        <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/restaurar') ?>" class="m-0">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" type="submit" title="Restaurar arquivo">
                                                <i class="bi bi-arrow-counterclockwise"></i>Restaurar
                                            </button>
                                        </form>
                                        <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/destruir') ?>" class="m-0" onsubmit="return confirm('Deseja excluir permanentemente este arquivo? Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir definitivamente">
                                                <i class="bi bi-x-circle me-1"></i>Destruir
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <?php if ($isViewable($file['extensao'])): ?>
                                            <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="<?= Url::to('/arquivos/visualizar/' . rawurlencode($file['id'])) ?>" title="Visualizar no site">
                                                <i class="bi bi-eye"></i><span>Abrir</span>
                                            </a>
                                        <?php endif; ?>
                                        <a class="btn-download" href="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/download') ?>" title="Baixar arquivo">
                                            <i class="bi bi-download"></i><span>Baixar</span>
                                        </a>

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border rounded-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Mais opções">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <?php if ($isViewable($file['extensao'])): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= Url::to('/arquivos/visualizar/' . rawurlencode($file['id'])) ?>">
                                                        <i class="bi bi-eye"></i>Visualizar
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/download') ?>">
                                                        <i class="bi bi-download"></i>Download seguro
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" data-action="rename-file" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/renomear') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-pencil-square"></i>Renomear
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" data-action="move-file" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/mover') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>" data-pasta-id="<?= htmlspecialchars((string) ($file['pasta_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-disciplina-id="<?= htmlspecialchars((string) ($file['disciplina_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-folder-symlink"></i>Mover para pasta
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" data-action="edit-tags" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/tags') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>" data-tags="<?= htmlspecialchars($tagIdsString, ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-tags"></i>Gerenciar tags
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" data-action="view-history" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/historico') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-clock-history"></i>Ver histórico
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/excluir') ?>" class="m-0" onsubmit="return confirm('Mover este arquivo para a lixeira?');">
                                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="bi bi-trash3"></i>Mover para lixeira
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <!-- VISUALIZAÇÃO EM LISTA (TABLE) -->
                    <section id="files-view-list" class="file-table-container <?= ($currentView === 'cards') ? 'd-none' : '' ?>">
                        <div class="table-responsive">
                            <table class="file-table-view">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Nome do Arquivo</th>
                                        <th>Tipo</th>
                                        <th>Tamanho</th>
                                        <th>Disciplina</th>
                                        <th>Pasta</th>
                                        <th>Data</th>
                                        <th class="text-center" style="width: 50px;">Favorito</th>
                                        <th class="text-end" style="width: 120px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                        <?php
                                        $typeClass = $getFileTypeClass($file['extensao']);
                                        $iconName = $getFileIcon($file['extensao']);
                                        $isFav = !empty($file['favorito_real']) || !empty($file['favorito']);
                                        $tagIdsString = implode(',', array_column($file['tags'] ?? [], 'id'));
                                        ?>
                                        <tr data-search-text="<?= htmlspecialchars($file['nome_original'] . ' ' . $file['extensao'] . ' ' . ($file['disciplina_nome'] ?? '') . ' ' . ($file['pasta_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <td>
                                                <div class="file-col-name">
                                                    <span class="file-col-icon <?= $typeClass ?>">
                                                        <i class="bi <?= $iconName ?>"></i>
                                                    </span>
                                                    <div class="file-name-text">
                                                        <strong title="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                                        </strong>
                                                        <div class="file-col-tags">
                                                            <?php foreach (($file['tags'] ?? []) as $t): ?>
                                                                <span class="file-tag" style="background: color-mix(in srgb, <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?> 15%, white); color: <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?>;">
                                                                    <i style="background: <?= htmlspecialchars($t['cor'], ENT_QUOTES, 'UTF-8') ?>"></i><?= htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="file-badge-type <?= $typeClass ?>">
                                                    <?= htmlspecialchars(strtoupper((string) $file['extensao']), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-nowrap"><?= $formatBytes((int) $file['tamanho_bytes']) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-nowrap">
                                                    <i class="bi bi-mortarboard me-1 text-primary"></i>
                                                    <?= htmlspecialchars((string) ($file['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-nowrap">
                                                    <i class="bi bi-folder text-warning me-1"></i>
                                                    <?= htmlspecialchars((string) ($file['pasta_nome'] ?? 'Raiz'), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-nowrap text-secondary small">
                                                    <?= htmlspecialchars((new \DateTimeImmutable($file['criado_em']))->format('d/m/Y H:i'), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!$isTrash): ?>
                                                    <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/favorito') ?>" class="m-0">
                                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="favorito" value="<?= $isFav ? '0' : '1' ?>">
                                                        <button class="btn-fav <?= $isFav ? 'active' : '' ?>" type="submit" title="<?= $isFav ? 'Desfavoritar' : 'Favoritar' ?>">
                                                            <i class="bi bi-star<?= $isFav ? '-fill' : '' ?>"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <?php if ($isTrash): ?>
                                                    <div class="d-inline-flex gap-1">
                                                        <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/restaurar') ?>" class="m-0">
                                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                            <button class="btn btn-sm btn-outline-success" type="submit" title="Restaurar">
                                                                <i class="bi bi-arrow-counterclockwise"></i>
                                                            </button>
                                                        </form>
                                                        <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/destruir') ?>" class="m-0" onsubmit="return confirm('Deseja excluir permanentemente este arquivo?');">
                                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Destruir">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <?php if ($isViewable($file['extensao'])): ?>
                                                        <a class="btn btn-sm btn-primary border-0" href="<?= Url::to('/arquivos/visualizar/' . rawurlencode($file['id'])) ?>" title="Visualizar">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <a class="btn btn-sm btn-light border" href="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/download') ?>" title="Baixar">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opções">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                <?php if ($isViewable($file['extensao'])): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="<?= Url::to('/arquivos/visualizar/' . rawurlencode($file['id'])) ?>">
                                                                        <i class="bi bi-eye"></i>Visualizar
                                                                    </a>
                                                                </li>
                                                                <?php endif; ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/download') ?>">
                                                                        <i class="bi bi-download"></i>Download
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" data-action="rename-file" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/renomear') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="bi bi-pencil-square"></i>Renomear
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" data-action="move-file" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/mover') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>" data-pasta-id="<?= htmlspecialchars((string) ($file['pasta_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-disciplina-id="<?= htmlspecialchars((string) ($file['disciplina_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="bi bi-folder-symlink"></i>Mover
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" data-action="edit-tags" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/tags') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>" data-tags="<?= htmlspecialchars($tagIdsString, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="bi bi-tags"></i>Tags
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" data-action="view-history" data-url="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/historico') ?>" data-nome="<?= htmlspecialchars($file['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="bi bi-clock-history"></i>Histórico
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider my-1"></li>
                                                                <li>
                                                                    <form method="post" action="<?= Url::to('/arquivos/' . rawurlencode($file['id']) . '/excluir') ?>" class="m-0" onsubmit="return confirm('Mover este arquivo para a lixeira?');">
                                                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <button class="dropdown-item text-danger" type="submit">
                                                                            <i class="bi bi-trash3"></i>Mover para lixeira
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<!-- ==========================================================================
     MODAIS DO GERENCIADOR DE ARQUIVOS
     ========================================================================== -->

<!-- 1. Modal Upload de Arquivo -->
<div class="modal fade" id="modal-upload" tabindex="-1" aria-labelledby="modal-upload-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="post" enctype="multipart/form-data" action="<?= Url::to('/arquivos/upload') ?>">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_filtro" value="<?= htmlspecialchars($currentFilter, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5" id="modal-upload-title">Upload de Arquivo</h2>
                    <p class="text-secondary small mb-0 mt-1">Formatos aceitos: PDF, EPUB, DOCX, TXT, PNG, JPG (máx. 20 MB).</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="upload-file">Selecionar arquivo</label>
                    <input class="form-control" id="upload-file" type="file" name="arquivo" accept=".pdf,.epub,.docx,.txt,.png,.jpg,.jpeg" required>
                    <div class="form-text">A validação de tipo real MIME e assinatura é realizada no servidor.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="upload-disciplina">Disciplina <span class="text-danger">*</span></label>
                    <select class="form-select" id="upload-disciplina" name="disciplina_id" required>
                        <option value="">Selecione uma disciplina...</option>
                        <?php foreach ($disciplines as $d): ?>
                            <option value="<?= htmlspecialchars((string) $d['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($currentDiscipline === (string) $d['id'] || count($disciplines) === 1) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $d['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="upload-pasta">Pasta de destino</label>
                    <select class="form-select" id="upload-pasta" name="pasta_id">
                        <option value="">Raiz (sem pasta)</option>
                        <?php foreach ($allFolders as $f): ?>
                            <option value="<?= htmlspecialchars((string) $f['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentFolderId === (string) $f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $f['nome'], ENT_QUOTES, 'UTF-8') ?>
                                <?= !empty($f['disciplina_nome']) ? ' (' . htmlspecialchars((string) $f['disciplina_nome'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-semibold mb-0">Tags para o arquivo</label>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-new-tag">+ Criar nova tag</button>
                    </div>
                    <?php if ($tags === []): ?>
                        <p class="text-secondary small mb-0">Nenhuma tag cadastrada ainda. Você pode criar tags para categorizar arquivos.</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            <?php foreach ($tags as $tag): ?>
                                <label class="border rounded-3 px-2 py-1 small d-inline-flex align-items-center gap-1 cursor-pointer bg-light">
                                    <input type="checkbox" name="tags[]" value="<?= htmlspecialchars((string) $tag['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background: <?= htmlspecialchars((string) $tag['cor'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                    <span><?= htmlspecialchars((string) $tag['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="submit">
                    <i class="bi bi-cloud-arrow-up"></i>Enviar arquivo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Nova Pasta / Subpasta -->
<div class="modal fade" id="modal-new-folder" tabindex="-1" aria-labelledby="modal-new-folder-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="post" action="<?= Url::to('/arquivos/pastas') ?>">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5" id="modal-new-folder-title">Nova Pasta</h2>
                    <p class="text-secondary small mb-0 mt-1">Crie pastas ou subpastas para organizar seus materiais.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new-folder-nome">Nome da pasta <span class="text-danger">*</span></label>
                    <input class="form-control" id="new-folder-nome" name="nome" maxlength="150" placeholder="Ex: Livros de Cálculo, Resumos..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new-folder-disciplina">Disciplina associada</label>
                    <select class="form-select" id="new-folder-disciplina" name="disciplina_id">
                        <option value="">Geral / Sem disciplina específica</option>
                        <?php foreach ($disciplines as $d): ?>
                            <option value="<?= htmlspecialchars((string) $d['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentDiscipline === (string) $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $d['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new-folder-parent">Pasta Pai (hierarquia)</label>
                    <select class="form-select" id="new-folder-parent" name="pasta_pai_id">
                        <option value="">Pasta Raiz</option>
                        <?php foreach ($allFolders as $f): ?>
                            <option value="<?= htmlspecialchars((string) $f['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentFolderId === (string) $f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $f['nome'], ENT_QUOTES, 'UTF-8') ?>
                                <?= !empty($f['disciplina_nome']) ? ' (' . htmlspecialchars((string) $f['disciplina_nome'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Criar pasta</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Renomear Pasta -->
<div class="modal fade" id="modal-rename-folder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-rename-folder" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5">Renomear Pasta</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="rename-folder-nome">Novo nome da pasta</label>
                    <input class="form-control" id="rename-folder-nome" name="nome" maxlength="150" required>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Salvar novo nome</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal Mover Pasta -->
<div class="modal fade" id="modal-move-folder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-move-folder" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5">Mover Pasta</h2>
                    <p class="text-secondary small mb-0 mt-1">Pasta: <strong id="move-folder-name-label"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="move-folder-parent">Mover para dentro de:</label>
                    <select class="form-select" id="move-folder-parent" name="pasta_pai_id">
                        <option value="">Raiz (sem pasta pai)</option>
                        <?php foreach ($allFolders as $f): ?>
                            <option value="<?= htmlspecialchars((string) $f['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $f['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Mover pasta</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal Excluir Pasta -->
<div class="modal fade" id="modal-delete-folder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-delete-folder" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5 text-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i>Excluir Pasta
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <p>Tem certeza que deseja excluir a pasta <strong id="delete-folder-name-label"></strong>?</p>
                <div class="alert alert-warning border-0 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>Os arquivos contidos nela serão preservados e movidos para a raiz.
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" type="submit">Excluir definitivamente</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Modal Renomear Arquivo -->
<div class="modal fade" id="modal-rename-file" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-rename-file" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_filtro" value="<?= htmlspecialchars($currentFilter, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5">Renomear Arquivo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="rename-file-nome">Novo nome do arquivo</label>
                    <input class="form-control" id="rename-file-nome" name="nome" maxlength="255" required>
                    <div class="form-text">A alteração será registrada no histórico de versões do arquivo.</div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Salvar novo nome</button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Modal Mover Arquivo -->
<div class="modal fade" id="modal-move-file" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-move-file" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_filtro" value="<?= htmlspecialchars($currentFilter, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5">Mover Arquivo</h2>
                    <p class="text-secondary small mb-0 mt-1">Arquivo: <strong id="move-file-name-label"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="move-file-pasta">Mover para a pasta:</label>
                    <select class="form-select" id="move-file-pasta" name="pasta_id">
                        <option value="">Raiz (sem pasta)</option>
                        <?php foreach ($allFolders as $f): ?>
                            <option value="<?= htmlspecialchars((string) $f['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $f['nome'], ENT_QUOTES, 'UTF-8') ?>
                                <?= !empty($f['disciplina_nome']) ? ' (' . htmlspecialchars((string) $f['disciplina_nome'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="move-file-disciplina">Mudar disciplina (opcional):</label>
                    <select class="form-select" id="move-file-disciplina" name="disciplina_id">
                        <option value="">Manter disciplina atual</option>
                        <?php foreach ($disciplines as $d): ?>
                            <option value="<?= htmlspecialchars((string) $d['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $d['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Mover arquivo</button>
            </div>
        </form>
    </div>
</div>

<!-- 8. Modal Gerenciar Tags do Arquivo -->
<div class="modal fade" id="modal-edit-tags" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-edit-tags" class="modal-content border-0 rounded-4 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_filtro" value="<?= htmlspecialchars($currentFilter, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5">Tags do Arquivo</h2>
                    <p class="text-secondary small mb-0 mt-1">Arquivo: <strong id="edit-tags-name-label"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <?php if ($tags === []): ?>
                    <p class="text-secondary small mb-0">Nenhuma tag cadastrada no seu perfil. Crie uma nova tag primeiro.</p>
                <?php else: ?>
                    <label class="form-label fw-semibold mb-2">Selecione as tags aplicadas:</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <label class="border rounded-3 px-2 py-1 small d-inline-flex align-items-center gap-1 cursor-pointer bg-light">
                                <input type="checkbox" name="tags[]" value="<?= htmlspecialchars((string) $tag['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background: <?= htmlspecialchars((string) $tag['cor'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                <span><?= htmlspecialchars((string) $tag['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Salvar tags</button>
            </div>
        </form>
    </div>
</div>

<!-- 9. Modal Histórico do Arquivo (Carregado Dinamicamente) -->
<div class="modal fade" id="modal-history" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title fs-5 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        <span id="history-file-title">Histórico de Alterações</span>
                    </h2>
                    <p class="text-secondary small mb-0 mt-1" id="history-file-meta">Carregando...</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div id="history-spinner" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div id="history-timeline-list"></div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- 10. Modal Nova Tag -->
<div class="modal fade" id="modal-new-tag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" method="post" action="<?= Url::to('/arquivos/tags') ?>">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_pasta" value="<?= htmlspecialchars((string) $currentFolderId, ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5">Criar Nova Tag</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new-tag-nome">Nome da tag <span class="text-danger">*</span></label>
                    <input class="form-control" id="new-tag-nome" name="nome" maxlength="80" placeholder="Ex: Importante, Trabalho 1, Prova..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new-tag-cor">Cor da tag</label>
                    <input class="form-control form-control-color w-100" id="new-tag-cor" type="color" name="cor" value="#2563eb">
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">Salvar tag</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
