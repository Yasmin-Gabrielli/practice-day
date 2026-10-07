<?php
use App\Helpers\Url;
$errors = $feedback['errors'] ?? [];
$formatDate = static fn (?string $date): string => $date ? date('d/m/Y H:i', strtotime($date)) : '—';
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
<?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
<div class="app-content"><?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
<main class="page-content notes-page">
    <section class="page-heading">
        <div><p class="eyebrow">Estudo e anotações</p><h1>Notas Rápidas</h1><p>Registre ideias, resumos e conteúdos das suas disciplinas.</p></div>
        <a class="btn btn-primary d-inline-flex align-items-center gap-2" href="<?= Url::to('/notas/nova') ?>"><i class="bi bi-plus-lg"></i>Nova Nota</a>
    </section>
    <?php if (isset($feedback['success'])): ?><div class="alert alert-success border-0 shadow-sm"><?= htmlspecialchars((string) $feedback['success'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($errors !== []): ?><div class="alert alert-danger border-0 shadow-sm"><?php foreach ($errors as $error): ?><div><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <form class="card border-0 shadow-sm mb-4" method="get" action="<?= Url::to('/notas') ?>">
                <div class="card-body p-3 p-md-4"><div class="row g-3 align-items-end">
                    <div class="col-12 col-md"><label class="form-label small fw-semibold">Pesquisar notas</label><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input class="form-control" name="pesquisa" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Título ou conteúdo"></div></div>
                    <div class="col-12 col-md-4"><label class="form-label small fw-semibold">Disciplina</label><select name="disciplina" class="form-select"><option value="">Todas as disciplinas</option><?php foreach ($disciplines as $discipline): ?><option value="<?= htmlspecialchars((string) $discipline['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentDiscipline === $discipline['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                    <div class="col-12 col-md-auto"><button class="btn btn-outline-primary w-100" type="submit">Filtrar</button></div>
                </div></div>
            </form>
            <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h5 fw-bold mb-0">Todas as notas</h2><span class="text-muted small"><?= count($notes) ?> resultado(s)</span></div>
            <?php if ($notes === []): ?>
                <div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bi bi-journal-plus fs-1 d-block mb-3"></i><strong class="d-block text-dark mb-1">Nenhuma nota encontrada</strong><span>Crie uma nota ou ajuste os filtros.</span></div></div>
            <?php else: ?><div class="d-flex flex-column gap-3"><?php foreach ($notes as $note): ?>
                <article class="card border-0 shadow-sm note-row"><div class="card-body p-3 p-md-4"><div class="d-flex gap-3"><span class="note-icon flex-shrink-0"><i class="bi bi-journal-text"></i></span><div class="flex-grow-1 min-w-0"><div class="d-flex justify-content-between gap-3"><div class="min-w-0"><h2 class="h6 fw-bold mb-1 text-truncate"><?= htmlspecialchars((string) $note['titulo'], ENT_QUOTES, 'UTF-8') ?></h2><div class="small text-muted mb-2"><?= htmlspecialchars((string) ($note['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?> · Atualizada em <?= $formatDate($note['atualizado_em']) ?></div></div><a class="btn btn-sm btn-light" href="<?= Url::to('/notas/' . rawurlencode((string) $note['id']) . '/editar') ?>" aria-label="Editar nota"><i class="bi bi-pencil"></i></a></div><p class="mb-2 text-muted note-preview"><?= htmlspecialchars(mb_strimwidth(trim((string) ($note['conteudo'] ?? '')), 0, 180, '…'), ENT_QUOTES, 'UTF-8') ?: 'Nota estruturada em blocos.' ?></p><span class="badge text-bg-light border"><i class="bi bi-layers me-1"></i><?= (int) $note['blocos_count'] ?> bloco(s)</span></div></div></div></article>
            <?php endforeach; ?></div><?php endif; ?>
        </div>
        <aside class="col-12 col-xl-4"><section class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0 p-4 pb-2"><h2 class="h5 fw-bold mb-1">Notas recentes</h2><p class="text-muted small mb-0">Últimas atualizações.</p></div><div class="card-body p-4 pt-3"><?php if ($recent === []): ?><div class="text-muted small py-3">Você ainda não possui notas recentes.</div><?php else: ?><div class="d-flex flex-column gap-3"><?php foreach ($recent as $note): ?><a class="text-decoration-none text-dark d-flex gap-2 align-items-start" href="<?= Url::to('/notas/' . rawurlencode((string) $note['id']) . '/editar') ?>"><i class="bi bi-sticky text-primary mt-1"></i><span class="min-w-0"><strong class="d-block small text-truncate"><?= htmlspecialchars((string) $note['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><small class="text-muted"><?= htmlspecialchars((string) ($note['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?> · <?= $formatDate($note['atualizado_em']) ?></small></span></a><?php endforeach; ?></div><?php endif; ?></div></section></aside>
    </div>
</main></div></div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
