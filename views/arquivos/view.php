<?php
use App\Helpers\Url;

$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1048576) {
        return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }

    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
};

$extLabel = strtoupper((string) ($file['extensao'] ?? ''));
$disciplina = (string) ($file['disciplina_nome'] ?? 'Sem disciplina');

require dirname(__DIR__) . '/layouts/header.php';
?>
<link href="<?= Url::asset('assets/css/file-viewer.css') ?>" rel="stylesheet">
<div class="app-shell file-viewer-page">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <header class="file-viewer-header">
                <div class="file-viewer-header-main">
                    <a class="back-link" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-arrow-left"></i>Voltar
                    </a>
                    <div>
                        <p class="eyebrow">Visualizador interno</p>
                        <h1 class="file-viewer-title"><?= htmlspecialchars((string) $file['nome_original'], ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>
                <dl class="file-viewer-meta">
                    <div><dt>Tipo</dt><dd><?= htmlspecialchars($extLabel, ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Tamanho</dt><dd><?= $formatBytes((int) ($file['tamanho_bytes'] ?? 0)) ?></dd></div>
                    <div><dt>Disciplina</dt><dd><?= htmlspecialchars($disciplina, ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                <div class="file-viewer-actions" data-viewer-toolbar>
                    <?php if ($viewerType === 'pdf'): ?>
                        <button type="button" class="btn btn-light border" data-pdf-prev title="Página anterior"><i class="bi bi-chevron-left"></i></button>
                        <span class="file-viewer-page-indicator" data-pdf-page-label>—</span>
                        <button type="button" class="btn btn-light border" data-pdf-next title="Próxima página"><i class="bi bi-chevron-right"></i></button>
                        <button type="button" class="btn btn-light border" data-pdf-zoom-out title="Diminuir zoom"><i class="bi bi-zoom-out"></i></button>
                        <button type="button" class="btn btn-light border" data-pdf-zoom-in title="Aumentar zoom"><i class="bi bi-zoom-in"></i></button>
                        <button type="button" class="btn btn-light border" data-viewer-fullscreen title="Tela cheia"><i class="bi bi-arrows-fullscreen"></i></button>
                        <button type="button" class="btn btn-light border" data-pdf-print title="Imprimir"><i class="bi bi-printer"></i></button>
                        <button type="button" class="btn btn-light border" data-pdf-bookmark title="Criar marcador"><i class="bi bi-bookmark"></i></button>
                    <?php elseif ($viewerType === 'image'): ?>
                        <button type="button" class="btn btn-light border" data-img-zoom-out title="Diminuir zoom"><i class="bi bi-zoom-out"></i></button>
                        <button type="button" class="btn btn-light border" data-img-zoom-in title="Aumentar zoom"><i class="bi bi-zoom-in"></i></button>
                        <button type="button" class="btn btn-light border" data-img-zoom-reset title="Zoom original"><i class="bi bi-aspect-ratio"></i></button>
                        <button type="button" class="btn btn-light border" data-viewer-fullscreen title="Tela cheia"><i class="bi bi-arrows-fullscreen"></i></button>
                    <?php endif; ?>
                    <a class="btn btn-primary" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-x-lg me-1"></i>Fechar
                    </a>
                </div>
            </header>

            <section class="file-viewer-stage" data-viewer-stage data-viewer-type="<?= htmlspecialchars($viewerType, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($viewerType === 'unsupported'): ?>
                    <div class="file-viewer-message">
                        <i class="bi bi-file-earmark-lock"></i>
                        <p>Este formato ainda não possui visualizador integrado. Use o download para abrir localmente.</p>
                    </div>
                <?php elseif ($viewerType === 'txt'): ?>
                    <?php if ($txtContent === null): ?>
                        <div class="file-viewer-message">
                            <i class="bi bi-exclamation-triangle"></i>
                            <p>Não foi possível carregar o conteúdo deste arquivo TXT.</p>
                        </div>
                    <?php else: ?>
                        <article class="txt-reader">
                            <pre class="txt-reader-content"><?= htmlspecialchars($txtContent, ENT_QUOTES, 'UTF-8') ?></pre>
                        </article>
                    <?php endif; ?>
                <?php elseif ($viewerType === 'docx'): ?>
                    <?php
                    $docxHtml = (string) ($docxPreview['html'] ?? '');
                    $docxMsg  = $docxPreview['message'] ?? null;
                    $docxEngine = (string) ($docxPreview['engine'] ?? 'none');
                    ?>
                    <?php if ($docxHtml === ''): ?>
                        <div class="file-viewer-message">
                            <i class="bi bi-filetype-docx"></i>
                            <p><?= htmlspecialchars((string) ($docxMsg ?: 'Visualização DOCX indisponível.'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="small text-muted mb-0">Instalação opcional: <code>composer require phpoffice/phpword</code> na raiz do projeto para conversão avançada.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($docxMsg): ?>
                            <div class="alert alert-info border-0 shadow-sm mb-3 small">
                                <?= htmlspecialchars($docxMsg, ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($docxEngine === 'zip-xml'): ?>
                                    <span class="d-block mt-1">Motor atual: conversão nativa PHP (Zip + XML).</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <article class="docx-reader"><?= $docxHtml ?></article>
                    <?php endif; ?>
                <?php elseif ($viewerType === 'pdf'): ?>
                    <div class="pdf-viewer-wrap">
                        <canvas id="pdf-canvas" class="pdf-canvas"></canvas>
                    </div>
                    <p class="file-viewer-loading" data-pdf-loading>Carregando PDF…</p>
                    <p class="file-viewer-error d-none" data-pdf-error></p>
                <?php elseif ($viewerType === 'image'): ?>
                    <div class="image-viewer-wrap" data-image-wrap>
                        <img src="<?= htmlspecialchars($serveUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" class="image-viewer-img" data-image-view draggable="false">
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<?php if ($viewerType === 'pdf'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<?php endif; ?>
<script>
window.PracticeDayFileViewer = {
    type: <?= json_encode($viewerType, JSON_UNESCAPED_UNICODE) ?>,
    serveUrl: <?= json_encode($serveUrl, JSON_UNESCAPED_UNICODE) ?>,
    downloadUrl: <?= json_encode($downloadUrl, JSON_UNESCAPED_UNICODE) ?>,
    csrfToken: <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>,
    library: <?= json_encode($library['progresso'] ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    libraryApi: <?= json_encode($libraryApi ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
};
</script>
<script src="<?= Url::asset('assets/js/file-viewer.js') ?>"></script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
