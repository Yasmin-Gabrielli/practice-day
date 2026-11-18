<?php
use App\Helpers\Url;
use App\Services\BookCoverService;

$errors = $feedback['errors'] ?? [];
$success = $feedback['success'] ?? null;
$formatDate = static fn (?string $date): string => $date ? date('d/m/Y H:i', strtotime($date)) : 'Ainda não aberta';

$coverUrlFor = static fn (string $id): string => Url::to('/arquivos/' . rawurlencode($id) . '/capa');
$serveUrlFor = static fn (string $id): string => Url::to('/arquivos/' . rawurlencode($id) . '/servir');

$extraStyles = [Url::asset('assets/css/file-viewer.css')];

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>

        <main class="page-content library-page">
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Leitura</p>
                    <h1>Biblioteca</h1>
                    <p>Continue a leitura dos seus PDFs e EPUBs com progresso salvo automaticamente.</p>
                </div>
                <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#add-book">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Adicionar arquivo</span>
                </button>
            </section>

            <?php if ($success !== null): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2" role="status">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-danger border-0 shadow-sm" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($books === []): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-bookshelf fs-1 d-block mb-3" aria-hidden="true"></i>
                        <strong class="d-block text-body mb-1">Sua estante está vazia</strong>
                        <span>Adicione um PDF ou EPUB dos seus arquivos para começar a ler.</span>
                    </div>
                </div>
            <?php else: ?>
                <ul class="row g-4 list-unstyled mb-0">
                    <?php foreach ($books as $book): ?>
                        <?php
                        $bookId = (string) $book['arquivo_id'];
                        $ext = strtolower((string) $book['extensao']);
                        $isPdf = $ext === 'pdf';
                        $percent = max(0, min(100, (float) ($book['progresso_porcentagem'] ?? 0)));
                        $currentPage = max(1, (int) ($book['pagina_atual'] ?? 1));
                        $totalPages = (int) ($book['total_paginas'] ?? 0);
                        $readerUrl = Url::to('/arquivos/visualizar/' . rawurlencode($bookId));
                        $started = $percent > 0 || $totalPages > 0;
                        ?>
                        <li class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                            <article class="card border-0 shadow-sm h-100 library-book">
                                <a class="library-cover" href="<?= $readerUrl ?>" aria-label="Ler <?= htmlspecialchars((string) $book['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="library-cover-badge"><?= htmlspecialchars(strtoupper($ext), ENT_QUOTES, 'UTF-8') ?></span>
                                    <img src="<?= htmlspecialchars($coverUrlFor($bookId), ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Capa de <?= htmlspecialchars((string) $book['nome_original'], ENT_QUOTES, 'UTF-8') ?>"
                                         loading="lazy" decoding="async"
                                         <?php if ($isPdf): ?>
                                             data-pdf-cover
                                             data-cover-cached="<?= BookCoverService::isCached($bookId) ? '1' : '0' ?>"
                                             data-file-url="<?= htmlspecialchars($serveUrlFor($bookId), ENT_QUOTES, 'UTF-8') ?>"
                                             data-cover-url="<?= htmlspecialchars($coverUrlFor($bookId), ENT_QUOTES, 'UTF-8') ?>"
                                             data-save-url="<?= htmlspecialchars($coverUrlFor($bookId), ENT_QUOTES, 'UTF-8') ?>"
                                         <?php endif; ?>>
                                </a>

                                <div class="card-body p-3 d-flex flex-column">
                                    <p class="small text-muted mb-1">
                                        <i class="bi bi-mortarboard me-1 text-primary" aria-hidden="true"></i>
                                        <?= htmlspecialchars((string) ($book['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <h2 class="h6 fw-bold mb-2" title="<?= htmlspecialchars((string) $book['nome_original'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $book['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                    </h2>

                                    <div class="library-progress mb-2"
                                         role="progressbar"
                                         aria-label="Progresso de leitura"
                                         aria-valuenow="<?= (int) round($percent) ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        <span style="width: <?= $percent ?>%"></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span><?= number_format($percent, 0) ?>%</span>
                                        <span><?= $totalPages > 0 ? 'Pág. ' . $currentPage . '/' . $totalPages : 'Preparando leitura' ?></span>
                                    </div>

                                    <p class="small text-muted mb-3">
                                        <i class="bi bi-clock-history me-1" aria-hidden="true"></i><?= $formatDate($book['ultimo_acesso'] ?? null) ?>
                                    </p>

                                    <a class="btn btn-primary w-100 mt-auto d-inline-flex align-items-center justify-content-center gap-2" href="<?= $readerUrl ?>">
                                        <i class="bi <?= $started ? 'bi-book-half' : 'bi-play-fill' ?>" aria-hidden="true"></i>
                                        <span><?= $started ? 'Continuar leitura' : 'Ler agora' ?></span>
                                    </a>
                                </div>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<div class="modal fade" id="add-book" tabindex="-1" aria-labelledby="add-book-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" method="post" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-5" id="add-book-title">Adicionar à estante</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if ($available === []): ?>
                    <p class="text-muted mb-0">Não há PDFs ou EPUBs disponíveis nos seus arquivos.</p>
                <?php else: ?>
                    <label class="form-label fw-semibold" for="library-file">Arquivo compatível</label>
                    <select class="form-select" id="library-file" name="arquivo_id" required>
                        <option value="">Selecione um arquivo</option>
                        <?php foreach ($available as $file): ?>
                            <option value="<?= htmlspecialchars((string) $file['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $file['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars(strtoupper((string) $file['extensao']), ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit" <?= $available === [] ? 'disabled' : '' ?>>Adicionar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('#add-book form')?.addEventListener('submit', (event) => {
    const id = document.getElementById('library-file')?.value;
    if (!id) {
        event.preventDefault();
        return;
    }
    event.currentTarget.action = <?= json_encode(Url::to('/biblioteca/')) ?> + encodeURIComponent(id) + '/adicionar';
});
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
