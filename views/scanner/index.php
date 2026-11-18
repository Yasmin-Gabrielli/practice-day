<?php
use App\Helpers\Url;

$errors = $feedback['errors'] ?? [];
$success = $feedback['success'] ?? null;

$pageImageUrl = static fn (string $pageId): string => Url::to('/scanner/paginas/' . rawurlencode($pageId) . '/imagem');

$extraStyles = [Url::asset('assets/css/file-viewer.css')];

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>

        <main class="page-content scanner-page">
            <section class="page-heading scan-hero">
                <div>
                    <p class="eyebrow">Documentos físicos</p>
                    <h1>Câmera</h1>
                    <p>Fotografe páginas com a câmera traseira, revise as capturas e salve tudo como uma digitalização organizada.</p>
                </div>
                <a class="btn btn-primary d-inline-flex align-items-center gap-2" href="#scan-studio" data-camera-open>
                    <i class="bi bi-camera" aria-hidden="true"></i><span>Abrir câmera</span>
                </a>
            </section>

            <?php if ($success !== null): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2" role="status">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-danger border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-exclamation-triangle-fill text-danger" aria-hidden="true"></i>
                        <strong>Não foi possível salvar a digitalização:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="scan-grid">
                <section class="scan-studio" id="scan-studio" aria-labelledby="scan-studio-title">
                    <header class="scan-studio-head">
                        <div>
                            <h2 id="scan-studio-title"><i class="bi bi-camera-video me-1 text-primary" aria-hidden="true"></i>Estúdio de captura</h2>
                            <p>Enquadre a página inteira e mantenha o documento bem iluminado.</p>
                        </div>
                        <span class="badge text-bg-light border" data-camera-state>Desligada</span>
                    </header>

                    <div class="scan-stage" data-scan-stage>
                        <video class="scan-video" data-camera-video autoplay playsinline muted hidden aria-label="Prévia da câmera"></video>
                        <div class="scan-stage-placeholder" data-camera-placeholder>
                            <i class="bi bi-camera-video-off" aria-hidden="true"></i>
                            <p>Abra a câmera para capturar páginas do seu material. Em celulares usamos a câmera traseira por padrão.</p>
                        </div>
                        <div class="scan-flash" data-scan-flash aria-hidden="true"></div>
                        <span class="scan-status" data-camera-status hidden>Pronta para capturar</span>
                    </div>

                    <p class="scan-error" data-scan-error role="alert" hidden></p>

                    <div class="scan-controls">
                        <button class="btn btn-outline-primary" type="button" data-camera-open>
                            <i class="bi bi-camera-video" aria-hidden="true"></i> Abrir câmera
                        </button>
                        <button class="scan-shutter" type="button" data-camera-capture disabled>
                            <i class="bi bi-camera-fill" aria-hidden="true"></i> Capturar página
                        </button>
                        <button class="btn btn-light border" type="button" data-camera-flip title="Alternar entre câmera frontal e traseira">
                            <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Virar câmera
                        </button>
                        <label class="btn btn-light border mb-0">
                            <i class="bi bi-images" aria-hidden="true"></i> Selecionar imagens
                            <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-camera-files>
                        </label>
                        <button class="btn btn-outline-secondary ms-auto" type="button" data-camera-stop disabled>
                            <i class="bi bi-stop-circle" aria-hidden="true"></i> Desligar
                        </button>
                    </div>

                    <p class="scan-hint">
                        Dica: no celular, mantenha o aparelho na vertical para páginas retrato. A captura espera o foco da câmera antes de fotografar.
                    </p>
                </section>

                <section class="scan-form-card" aria-labelledby="scan-form-title">
                    <form method="post" action="<?= Url::to('/scanner') ?>" enctype="multipart/form-data" data-scan-form>
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                        <header class="scan-form-head">
                            <div>
                                <h2 id="scan-form-title">Salvar digitalização</h2>
                                <p>Dê um nome e escolha a disciplina do material.</p>
                            </div>
                            <span class="badge text-bg-primary" data-scan-count>0 páginas</span>
                        </header>

                        <div class="scan-form-grid">
                            <label class="scan-field" for="scan-title">
                                Título
                                <input id="scan-title" name="titulo" maxlength="255" required placeholder="Ex.: Anotações de cálculo — capítulo 3">
                            </label>

                            <label class="scan-field" for="scan-discipline">
                                Disciplina
                                <select id="scan-discipline" name="disciplina_id">
                                    <option value="">Sem disciplina</option>
                                    <?php foreach ($disciplines as $discipline): ?>
                                        <option value="<?= htmlspecialchars((string) $discipline['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <div class="scan-thumbs-wrap">
                            <div class="scan-thumbs-head">
                                <span>Páginas capturadas</span>
                                <button class="btn btn-sm btn-link text-decoration-none p-0" type="button" data-scan-clear hidden>Limpar tudo</button>
                            </div>
                            <div class="scan-thumbs" data-scan-thumbs></div>
                            <p class="scan-empty" data-scan-empty>
                                <i class="bi bi-camera me-1" aria-hidden="true"></i>
                                Nenhuma página ainda. Capture pela câmera ou selecione imagens do computador.
                            </p>
                        </div>

                        <div class="scan-form-actions">
                            <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="submit" data-scan-submit disabled>
                                <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Salvar digitalização
                            </button>
                            <button class="btn btn-light border" type="reset" data-scan-reset>Cancelar</button>
                        </div>
                    </form>
                </section>
            </div>

            <section class="scan-gallery" aria-labelledby="scan-gallery-title">
                <div class="scan-gallery-head">
                    <h2 id="scan-gallery-title">Digitalizações salvas</h2>
                    <span class="text-muted small"><?= count($scans) ?> documento(s)</span>
                </div>

                <?php if ($scans === []): ?>
                    <div class="empty-module-state">
                        <span><i class="bi bi-camera text-primary" aria-hidden="true"></i></span>
                        <h2>Nenhuma digitalização</h2>
                        <p>Abra a câmera acima para capturar seu primeiro documento.</p>
                    </div>
                <?php else: ?>
                    <div class="scan-gallery-grid">
                        <?php foreach ($scans as $scan): ?>
                            <?php
                            $pages = is_array($scan['paginas'] ?? null) ? $scan['paginas'] : [];
                            $firstPage = $pages[0] ?? null;
                            $totalPages = max((int) ($scan['total_paginas'] ?? 0), count($pages));
                            ?>
                            <article class="scan-card">
                                <div class="scan-card-cover">
                                    <?php if ($firstPage !== null): ?>
                                        <img src="<?= htmlspecialchars($pageImageUrl((string) $firstPage['id']), ENT_QUOTES, 'UTF-8') ?>"
                                             alt="Primeira página de <?= htmlspecialchars((string) $scan['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                             loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <div class="scan-stage-placeholder">
                                            <i class="bi bi-file-earmark-image" aria-hidden="true"></i>
                                            <p>Sem prévia disponível</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="scan-card-body">
                                    <h3 title="<?= htmlspecialchars((string) $scan['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $scan['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <div class="scan-card-meta">
                                        <span><i class="bi bi-mortarboard me-1 text-primary" aria-hidden="true"></i><?= htmlspecialchars((string) ($scan['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span><i class="bi bi-files me-1" aria-hidden="true"></i><?= $totalPages ?> página(s)</span>
                                        <span><i class="bi bi-clock-history me-1" aria-hidden="true"></i><?= date('d/m/Y H:i', strtotime((string) $scan['criado_em'])) ?></span>
                                    </div>
                                </div>

                                <?php if ($pages !== []): ?>
                                    <div class="scan-card-pages" aria-label="Páginas de <?= htmlspecialchars((string) $scan['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php foreach ($pages as $page): ?>
                                            <button class="scan-page-thumb" type="button"
                                                    data-scan-page-preview
                                                    data-page-url="<?= htmlspecialchars($pageImageUrl((string) $page['id']), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-page-label="<?= htmlspecialchars((string) $scan['titulo'] . ' — página ' . (int) $page['numero_pagina'], ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Ampliar página <?= (int) $page['numero_pagina'] ?>">
                                                <img src="<?= htmlspecialchars($pageImageUrl((string) $page['id']), ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="Página <?= (int) $page['numero_pagina'] ?>"
                                                     loading="lazy" decoding="async">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <footer class="scan-card-footer">
                                    <span class="small text-muted"><?= $totalPages ?> página(s)</span>
                                    <form method="post" action="<?= Url::to('/scanner/' . rawurlencode((string) $scan['id']) . '/excluir') ?>" class="m-0"
                                          onsubmit="return confirm('Excluir esta digitalização? As imagens e o texto reconhecido serão removidos.');">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="bi bi-trash" aria-hidden="true"></i> Excluir
                                        </button>
                                    </form>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<dialog class="scan-page-dialog" data-scan-page-dialog aria-labelledby="scan-page-dialog-title">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
        <h2 class="h6 mb-0" id="scan-page-dialog-title" data-scan-page-dialog-title>Página digitalizada</h2>
        <button class="btn btn-sm btn-light border" type="button" data-scan-page-dialog-close aria-label="Fechar prévia">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
    <img alt="" data-scan-page-dialog-image>
</dialog>

<script src="<?= Url::asset('assets/js/camera-capture.js') ?>"></script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
