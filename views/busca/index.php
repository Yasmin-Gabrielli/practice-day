<?php
use App\Helpers\Url;
require dirname(__DIR__) . '/layouts/header.php';
$q = (string) ($query ?? '');
$res = $results ?? ['files' => [], 'disciplines' => [], 'notes' => [], 'tasks' => []];
$total = count($res['files']) + count($res['disciplines']) + count($res['notes']) + count($res['tasks']);
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Pesquisa geral</p>
                    <h1>Busca</h1>
                    <p>Pesquise arquivos, disciplinas, notas e tarefas em um só lugar.</p>
                </div>
            </section>

            <form class="card border-0 shadow-sm mb-4" method="get" action="<?= Url::to('/busca') ?>">
                <div class="card-body p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input class="form-control" type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Digite para pesquisar...">
                        <button class="btn btn-primary" type="submit">Pesquisar</button>
                    </div>
                </div>
            </form>

            <?php if ($q === ''): ?>
                <div class="text-muted">Digite um termo para começar.</div>
            <?php elseif ($total === 0): ?>
                <div class="alert alert-info border-0 shadow-sm">Nenhum resultado encontrado para "<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>".</div>
            <?php else: ?>
                <p class="text-muted small mb-3"><?= $total ?> resultado(s).</p>

                <?php if ($res['files'] !== []): ?>
                    <h2 class="h5 fw-bold mt-3">Arquivos</h2>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($res['files'] as $f): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?= Url::to('/arquivos/visualizar/' . rawurlencode((string) $f['id'])) ?>" class="text-decoration-none">
                                    <i class="bi bi-file-earmark me-2"></i><?= htmlspecialchars((string) $f['nome_original'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <small class="text-muted"><?= htmlspecialchars((string) ($f['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($res['disciplines'] !== []): ?>
                    <h2 class="h5 fw-bold mt-3">Disciplinas</h2>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($res['disciplines'] as $d): ?>
                            <li class="list-group-item">
                                <a href="<?= Url::to('/disciplinas/' . rawurlencode((string) $d['id'])) ?>" class="text-decoration-none">
                                    <span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= htmlspecialchars((string) ($d['cor'] ?? '#6c757d'), ENT_QUOTES, 'UTF-8') ?>"></span>
                                    <?= htmlspecialchars((string) $d['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($res['notes'] !== []): ?>
                    <h2 class="h5 fw-bold mt-3">Notas</h2>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($res['notes'] as $n): ?>
                            <li class="list-group-item">
                                <a href="<?= Url::to('/notas/' . rawurlencode((string) $n['id']) . '/editar') ?>" class="text-decoration-none">
                                    <i class="bi bi-journal-text me-2"></i><?= htmlspecialchars((string) $n['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($res['tasks'] !== []): ?>
                    <h2 class="h5 fw-bold mt-3">Tarefas</h2>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($res['tasks'] as $t): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?= Url::to('/planner') ?>?tarefa=<?= rawurlencode((string) $t['id']) ?>" class="text-decoration-none">
                                    <i class="bi bi-check2-square me-2"></i><?= htmlspecialchars((string) $t['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <small class="text-muted"><?= htmlspecialchars((string) ($t['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>