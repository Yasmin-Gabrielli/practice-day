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

$libId = (string) ($library['id'] ?? '');
$progresso = $library['progresso'] ?? [];
$disciplina = (string) ($file['disciplina_nome'] ?? 'Sem disciplina');

require dirname(__DIR__) . '/layouts/header.php';
?>
<link href="<?= Url::asset('assets/css/file-viewer.css') ?>" rel="stylesheet">
<div class="app-shell file-viewer-page file-epub-page">
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
                        <p class="eyebrow">Biblioteca · Leitor EPUB</p>
                        <h1 class="file-viewer-title"><?= htmlspecialchars((string) $file['nome_original'], ENT_QUOTES, 'UTF-8') ?></h1>
                    </div>
                </div>
                <dl class="file-viewer-meta">
                    <div><dt>Tipo</dt><dd>EPUB</dd></div>
                    <div><dt>Tamanho</dt><dd><?= $formatBytes((int) ($file['tamanho_bytes'] ?? 0)) ?></dd></div>
                    <div><dt>Disciplina</dt><dd><?= htmlspecialchars($disciplina, ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Posição</dt><dd><span data-epub-page-label>—</span></dd></div>
                    <div><dt>Progresso</dt><dd><span data-epub-progress-label><?= number_format((float) ($progresso['progresso_porcentagem'] ?? 0), 1, ',', '.') ?>%</span></dd></div>
                </dl>
                <div class="file-viewer-actions">
                    <button type="button" class="btn btn-light border" data-epub-prev><i class="bi bi-chevron-left"></i> Anterior</button>
                    <button type="button" class="btn btn-light border" data-epub-next>Próximo <i class="bi bi-chevron-right"></i></button>
                    <button type="button" class="btn btn-light border" data-epub-bookmark><i class="bi bi-bookmark"></i> Marcador</button>
                    <button type="button" class="btn btn-light border" data-epub-highlight><i class="bi bi-highlighter"></i> Destacar</button>
                    <button type="button" class="btn btn-light border" data-epub-note><i class="bi bi-journal-text"></i> Anotar</button>
                    <button type="button" class="btn btn-light border" data-viewer-fullscreen><i class="bi bi-arrows-fullscreen"></i></button>
                    <a class="btn btn-primary" href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-download me-1"></i>Download</a>
                    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-x-lg me-1"></i>Fechar</a>
                </div>
            </header>

            <div class="epub-layout">
                <aside class="epub-sidebar">
                    <h2>Marcadores</h2>
                    <ul class="epub-list" data-epub-bookmarks-list></ul>
                    <h2 class="mt-3">Destaques</h2>
                    <ul class="epub-list" data-epub-highlights-list></ul>
                    <h2 class="mt-3">Anotações</h2>
                    <ul class="epub-list" data-epub-notes-list></ul>
                </aside>
                <section class="file-viewer-stage epub-stage" data-viewer-stage>
                    <div id="epub-viewer" class="epub-viewer-host"></div>
                    <p class="file-viewer-loading" data-epub-loading>Carregando EPUB…</p>
                </section>
            </div>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
<script>
window.PracticeDayEpubReader = {
    serveUrl: <?= json_encode($serveUrl, JSON_UNESCAPED_UNICODE) ?>,
    csrfToken: <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>,
    libraryId: <?= json_encode($libId, JSON_UNESCAPED_UNICODE) ?>,
    initial: {
        progresso: <?= json_encode($progresso, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        marcadores: <?= json_encode($library['marcadores'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        destaques: <?= json_encode($library['destaques'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        anotacoes: <?= json_encode($library['anotacoes'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
    },
    api: <?= json_encode($libraryApi ?? [], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="<?= Url::asset('assets/js/file-epub-reader.js') ?>"></script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
