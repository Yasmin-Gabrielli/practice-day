<?php
use App\Helpers\Url;
require dirname(__DIR__) . '/layouts/header.php';
$note = $note ?? [];
$blocks = $note['blocos'] ?? [];
$formatDate = static fn (?string $date): string => $date ? date('d/m/Y H:i', strtotime($date)) : '—';
?>
<div class="app-shell">
<?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
<div class="app-content">
<?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
<main class="page-content notes-page">
    <section class="page-heading">
        <div>
            <p class="eyebrow">Notas Rápidas</p>
            <h1><?= htmlspecialchars((string) ($note['titulo'] ?? 'Nota'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p>
                <?php if (!empty($note['disciplina_nome'])): ?>
                    Disciplina: <?= htmlspecialchars((string) $note['disciplina_nome'], ENT_QUOTES, 'UTF-8') ?> · 
                <?php endif; ?>
                Atualizada em <?= $formatDate($note['atualizado_em'] ?? null) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= Url::to('/notas/' . rawurlencode((string) ($note['id'] ?? '')) . '/editar') ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
            <a href="<?= Url::to('/notas') ?>" class="btn btn-light">Voltar</a>
        </div>
    </section>
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <?php if (!empty($note['conteudo'])): ?>
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h6 fw-bold mb-3">Conteúdo</h2>
                    <div class="note-content"><?= nl2br(htmlspecialchars((string) $note['conteudo'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            </section>
            <?php endif; ?>
            <?php if (!empty($blocks)): ?>
            <section class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h6 fw-bold mb-3">Blocos</h2>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($blocks as $block): ?>
                        <div class="note-block-view">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-light text-dark"><?= htmlspecialchars((string) ($block['tipo_bloco'] ?? 'texto'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="note-block-content">
                                <?php if (($block['tipo_bloco'] ?? 'texto') === 'codigo'): ?>
                                    <pre class="bg-dark text-light p-3 rounded"><code><?= htmlspecialchars((string) ($block['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></pre>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars((string) ($block['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </div>
        <aside class="col-12 col-xl-4">
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="h6 fw-bold mb-3">Informações</h3>
                    <div class="small text-muted">
                        <div class="mb-2"><strong>Disciplina:</strong> <?= htmlspecialchars((string) ($note['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="mb-2"><strong>Criada em:</strong> <?= $formatDate($note['criado_em'] ?? null) ?></div>
                        <div class="mb-2"><strong>Atualizada em:</strong> <?= $formatDate($note['atualizado_em'] ?? null) ?></div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</main>
</div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
