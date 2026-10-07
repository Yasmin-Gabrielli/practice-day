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
        'pdf'              => 'badge-pdf',
        'docx'             => 'badge-docx',
        'epub'             => 'badge-epub',
        'txt'              => 'badge-txt',
        'png','jpg','jpeg' => 'badge-img',
        default            => 'badge-file',
    };
};

$daysUntil = static function (?string $expira): ?int {
    if ($expira === null || $expira === '') return null;
    $diff = (new DateTimeImmutable($expira))->diff(new DateTimeImmutable());
    return (int) $diff->days * ($diff->invert ? 1 : -1);
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
                    <p class="eyebrow">Arquivos Excluídos</p>
                    <h1><i class="bi bi-trash2-fill text-danger me-2"></i>Lixeira</h1>
                    <p><?= count($files) ?> arquivo(s) aguardando restauração ou exclusão definitiva.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= Url::to('/arquivos') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-folder2-open"></i><span>Meus Arquivos</span>
                    </a>
                    <?php if ($files !== []): ?>
                        <form method="post" action="<?= Url::to('/lixeira/esvaziar') ?>"
                              onsubmit="return confirm('Tem certeza? Todos os arquivos serão EXCLUÍDOS PERMANENTEMENTE e não poderão ser recuperados.');">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2">
                                <i class="bi bi-trash2-fill"></i><span>Esvaziar Lixeira</span>
                            </button>
                        </form>
                    <?php endif; ?>
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
                            <li><?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Aviso -->
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
                <i class="bi bi-clock-history fs-4 mt-1 text-warning flex-shrink-0"></i>
                <div>
                    <strong>Atenção:</strong> Os arquivos na lixeira são excluídos automaticamente após <strong>30 dias</strong>
                    da data de exclusão. Restaure os que deseja manter.
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-danger"><i class="bi bi-trash2"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_lixeira'] ?></div>
                        <div class="text-muted small">Na lixeira</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-primary"><i class="bi bi-files"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_arquivos'] ?></div>
                        <div class="text-muted small">Arquivos Ativos</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-warning"><i class="bi bi-star-fill"></i></div>
                        <div class="fw-bold fs-4"><?= $stats['total_favoritos'] ?></div>
                        <div class="text-muted small"><a href="<?= Url::to('/favoritos') ?>" class="text-reset text-decoration-none">Favoritos</a></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="fs-1 text-success"><i class="bi bi-hdd"></i></div>
                        <div class="fw-bold fs-4"><?= $formatBytes((int)$stats['total_bytes']) ?></div>
                        <div class="text-muted small">Armazenado</div>
                    </div>
                </div>
            </div>

            <!-- Busca -->
            <div class="mb-4">
                <form method="get" action="<?= Url::to('/lixeira') ?>" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" class="form-control"
                               placeholder="Buscar na lixeira..."
                               value="<?= htmlspecialchars($currentSearch, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($currentSearch !== ''): ?>
                            <a href="<?= Url::to('/lixeira') ?>" class="btn btn-outline-secondary" title="Limpar">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>

            <!-- Tabela de arquivos na lixeira -->
            <?php if ($files === []): ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-trash2 display-1 text-muted opacity-40"></i>
                    <h3 class="mt-3 mb-2"><?= $currentSearch !== '' ? 'Nenhum resultado' : 'Lixeira vazia' ?></h3>
                    <p class="text-muted">
                        <?php if ($currentSearch !== ''): ?>
                            Tente uma busca diferente.
                        <?php else: ?>
                            Nenhum arquivo foi excluído. Arquivos excluídos em <a href="<?= Url::to('/arquivos') ?>">Meus Arquivos</a> aparecem aqui.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Tamanho</th>
                                    <th>Excluído em</th>
                                    <th>Expira em</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $f): ?>
                                    <?php
                                        $ext      = strtolower((string)($f['extensao'] ?? ''));
                                        $nome     = htmlspecialchars((string)$f['nome_original'], ENT_QUOTES, 'UTF-8');
                                        $exclData = !empty($f['excluido_em']) ? date('d/m/Y', strtotime((string)$f['excluido_em'])) : '—';
                                        $expData  = !empty($f['expira_em'])   ? date('d/m/Y', strtotime((string)$f['expira_em']))   : '—';
                                        $days     = $daysUntil(!empty($f['expira_em']) ? (string)$f['expira_em'] : null);
                                        $expClass = ($days !== null && $days <= 5) ? 'text-danger fw-semibold' : 'text-muted';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi <?= htmlspecialchars($getFileIcon($ext), ENT_QUOTES, 'UTF-8') ?> text-secondary fs-5"></i>
                                                <span class="fw-medium text-truncate text-muted" style="max-width:200px;" title="<?= $nome ?>"><?= $nome ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge <?= $getFileBadge($ext) ?> text-uppercase"><?= strtoupper($ext) ?></span></td>
                                        <td class="text-muted small text-nowrap"><?= $formatBytes((int)($f['tamanho_bytes'] ?? 0)) ?></td>
                                        <td class="text-muted small text-nowrap"><?= $exclData ?></td>
                                        <td class="small text-nowrap <?= $expClass ?>">
                                            <?= $expData ?>
                                            <?php if ($days !== null && $days <= 5): ?>
                                                <br><span class="badge bg-danger" style="font-size:.65rem;">Expira em <?= $days ?> dia(s)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <!-- Restaurar -->
                                                <form method="post"
                                                      action="<?= Url::to('/lixeira/' . rawurlencode((string)$f['id']) . '/restaurar') ?>">
                                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" title="Restaurar">
                                                        <i class="bi bi-arrow-counterclockwise"></i><span class="d-none d-md-inline">Restaurar</span>
                                                    </button>
                                                </form>
                                                <!-- Excluir definitivamente -->
                                                <form method="post"
                                                      action="<?= Url::to('/lixeira/' . rawurlencode((string)$f['id']) . '/destruir') ?>"
                                                      onsubmit="return confirm('Excluir DEFINITIVAMENTE o arquivo \'<?= addslashes($nome) ?>\'? Esta ação não pode ser desfeita.');">
                                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="Excluir definitivamente">
                                                        <i class="bi bi-trash3-fill"></i><span class="d-none d-md-inline">Excluir</span>
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