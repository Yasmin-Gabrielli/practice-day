<?php
use App\Helpers\Url;

$errors  = $feedback['errors'] ?? [];
$success = $feedback['success'] ?? null;

$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
};

$getFileIcon = static function (?string $ext): string {
    return match (strtolower((string) $ext)) {
        'pdf'        => 'bi-filetype-pdf',
        'docx'       => 'bi-filetype-docx',
        'epub'       => 'bi-book-half',
        'txt'        => 'bi-filetype-txt',
        'png'        => 'bi-filetype-png',
        'jpg','jpeg' => 'bi-filetype-jpg',
        default      => 'bi-file-earmark-text',
    };
};

$getFileBadge = static function (?string $ext): string {
    return match (strtolower((string) $ext)) {
        'pdf'        => 'badge-pdf',
        'docx'       => 'badge-docx',
        'epub'       => 'badge-epub',
        'txt'        => 'badge-txt',
        'png','jpg','jpeg' => 'badge-img',
        default      => 'badge-file',
    };
};

$isViewable = static function (?string $ext): bool {
    return in_array(strtolower((string) $ext), ['pdf','epub','txt','png','jpg','jpeg','docx'], true);
};

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
                    <p class="eyebrow">Arquivos Marcados</p>
                    <h1><i class="bi bi-star-fill text-warning me-2"></i>Favoritos</h1>
                    <p>Seus <?= count($files) ?> arquivo(s) marcado(s) como favorito.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= Url::to('/arquivos') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-folder2-open"></i><span>Meus Arquivos</span>
                    </a>
                    <!-- Alternar view -->
                    <div class="btn-group" role="group" aria-label="Modo de visualização">
                        <a href="<?= Url::to('/favoritos?view=cards' . ($currentSearch !== '' ? '&busca=' . urlencode($currentSearch) : '')) ?>"
                           class="btn btn-sm <?= $currentView === 'cards' ? 'btn-primary' : 'btn-outline-secondary' ?>" title="Cards">
                            <i class="bi bi-grid"></i>
                        </a>
                        <a href="<?= Url::to('/favoritos?view=lista' . ($currentSearch !== '' ? '&busca=' . urlencode($currentSearch) : '')) ?>"
                           class="btn btn-sm <?= $currentView === 'lista' ? 'btn-primary' : 'btn-outline-secondary' ?>" title="Lista">
                            <i class="bi bi-list-ul"></i>
                        </a>
                    </div>
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

            <!-- Stats rápidos -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-warning"><i class="bi bi-star-fill"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_favoritos'] ?></div>
                        <div class="text-muted small">Favoritos</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-primary"><i class="bi bi-files"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_arquivos'] ?></div>
                        <div class="text-muted small">Total de Arquivos</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-success"><i class="bi bi-hdd"></i></div>
                        <div class="fw-bold fs-4"><?= $formatBytes((int) $stats['total_bytes']) ?></div>
                        <div class="text-muted small">Armazenado</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-danger"><i class="bi bi-trash2"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_lixeira'] ?></div>
                        <div class="text-muted small"><a href="<?= Url::to('/lixeira') ?>" class="text-reset text-decoration-none">Na lixeira</a></div>
                    </div>
                </div>
            </div>

            <!-- Busca -->
            <div class="mb-4">
                <form method="get" action="<?= Url::to('/favoritos') ?>" class="d-flex gap-2">
                    <input type="hidden" name="view" value="<?= htmlspecialchars($currentView, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" class="form-control"
                               placeholder="Buscar nos favoritos..."
                               value="<?= htmlspecialchars($currentSearch, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($currentSearch !== ''): ?>
                            <a href="<?= Url::to('/favoritos?view=' . urlencode($currentView)) ?>" class="btn btn-outline-secondary" title="Limpar">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>

            <!-- Lista/Cards -->
            <?php if ($files === []): ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-star display-1 text-warning opacity-50"></i>
                    <h3 class="mt-3 mb-2"><?= $currentSearch !== '' ? 'Nenhum resultado encontrado' : 'Nenhum arquivo favorito ainda' ?></h3>
                    <p class="text-muted">
                        <?php if ($currentSearch !== ''): ?>
                            Tente uma busca diferente.
                        <?php else: ?>
                            Marque arquivos como favoritos em <a href="<?= Url::to('/arquivos') ?>">Meus Arquivos</a> para que apareçam aqui.
                        <?php endif; ?>
                    </p>
                </div>
            <?php elseif ($currentView === 'cards'): ?>
                <div class="row g-3">
                    <?php foreach ($files as $f): ?>
                        <?php
                            $ext  = strtolower((string) ($f['extensao'] ?? ''));
                            $nome = htmlspecialchars((string) $f['nome_original'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <div class="card border-0 shadow-sm h-100 file-card position-relative">
                                <!-- Ícone / tipo -->
                                <div class="card-body d-flex flex-column gap-2 p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="file-icon-wrap">
                                            <i class="bi <?= htmlspecialchars($getFileIcon($ext), ENT_QUOTES, 'UTF-8') ?> fs-2 text-primary"></i>
                                        </div>
                                        <span class="badge <?= $getFileBadge($ext) ?> text-uppercase" style="font-size:.7rem;"><?= strtoupper($ext) ?></span>
                                    </div>
                                    <div class="fw-semibold text-truncate" title="<?= $nome ?>"><?= $nome ?></div>
                                    <div class="text-muted small">
                                        <?= $formatBytes((int) ($f['tamanho_bytes'] ?? 0)) ?>
                                        <?php if (!empty($f['disciplina_nome'])): ?>
                                            &nbsp;·&nbsp;<span class="badge" style="background:<?= htmlspecialchars((string)($f['disciplina_cor'] ?? '#6c757d'), ENT_QUOTES, 'UTF-8') ?>;font-size:.65rem;"><?= htmlspecialchars((string)$f['disciplina_nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($f['pasta_nome'])): ?>
                                        <div class="text-muted small"><i class="bi bi-folder2 me-1"></i><?= htmlspecialchars((string)$f['pasta_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                    <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime((string)($f['criado_em'] ?? ''))) ?></div>
                                </div>
                                <!-- Ações -->
                                <div class="card-footer bg-transparent border-top-0 pt-0 px-3 pb-3 d-flex gap-2 flex-wrap">
                                    <?php if ($isViewable($ext)): ?>
                                        <a href="<?= Url::to('/arquivos/visualizar/' . rawurlencode((string) $f['id'])) ?>"
                                           class="btn btn-sm btn-outline-primary flex-fill" title="Visualizar">
                                            <i class="bi bi-eye me-1"></i>Abrir
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= Url::to('/arquivos/' . rawurlencode((string)$f['id']) . '/download') ?>"
                                       class="btn btn-sm btn-outline-success flex-fill" title="Download">
                                        <i class="bi bi-download me-1"></i>Baixar
                                    </a>
                                    <form method="post" action="<?= Url::to('/favoritos/' . rawurlencode((string)$f['id']) . '/remover') ?>"
                                          onsubmit="return confirm('Remover dos favoritos?');" class="flex-fill">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Remover dos favoritos">
                                            <i class="bi bi-star-fill me-1"></i>Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Vista em lista -->
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Tamanho</th>
                                    <th>Disciplina</th>
                                    <th>Pasta</th>
                                    <th>Data</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $f): ?>
                                    <?php
                                        $ext  = strtolower((string)($f['extensao'] ?? ''));
                                        $nome = htmlspecialchars((string)$f['nome_original'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi <?= htmlspecialchars($getFileIcon($ext), ENT_QUOTES, 'UTF-8') ?> text-primary fs-5"></i>
                                                <span class="fw-medium text-truncate" style="max-width:220px;" title="<?= $nome ?>"><?= $nome ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge <?= $getFileBadge($ext) ?> text-uppercase"><?= strtoupper($ext) ?></span></td>
                                        <td class="text-nowrap text-muted small"><?= $formatBytes((int)($f['tamanho_bytes'] ?? 0)) ?></td>
                                        <td class="text-muted small"><?= !empty($f['disciplina_nome']) ? htmlspecialchars((string)$f['disciplina_nome'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                        <td class="text-muted small"><?= !empty($f['pasta_nome']) ? htmlspecialchars((string)$f['pasta_nome'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                        <td class="text-muted small text-nowrap"><?= date('d/m/Y', strtotime((string)($f['criado_em'] ?? ''))) ?></td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                                <?php if ($isViewable($ext)): ?>
                                                    <a href="<?= Url::to('/arquivos/visualizar/' . rawurlencode((string)$f['id'])) ?>"
                                                       class="btn btn-sm btn-outline-primary" title="Visualizar"><i class="bi bi-eye"></i></a>
                                                <?php endif; ?>
                                                <a href="<?= Url::to('/arquivos/' . rawurlencode((string)$f['id']) . '/download') ?>"
                                                   class="btn btn-sm btn-outline-success" title="Download"><i class="bi bi-download"></i></a>
                                                <form method="post" action="<?= Url::to('/favoritos/' . rawurlencode((string)$f['id']) . '/remover') ?>"
                                                      onsubmit="return confirm('Remover dos favoritos?');">
                                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover dos favoritos">
                                                        <i class="bi bi-star-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>