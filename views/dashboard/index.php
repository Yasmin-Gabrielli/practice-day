<?php
$summary = $dashboard['summary'];
$widgets = $dashboard['widgets'];
$formatDate = static function (?string $date, bool $withTime = false): string {
    if ($date === null || $date === '') {
        return 'Sem data definida';
    }
    return (new \DateTimeImmutable($date))->format($withTime ? 'd/m/Y · H:i' : 'd/m/Y');
};
$formatDuration = static function (?int $minutes): string {
    if ($minutes === null) return '—';
    if ($minutes < 60) return $minutes . ' min';
    return intdiv($minutes, 60) . 'h' . ($minutes % 60 ? ' ' . ($minutes % 60) . 'min' : '');
};
$priorityClass = ['baixa' => 'priority-low', 'media' => 'priority-medium', 'alta' => 'priority-high'];
$priorityLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'];
$statusLabel = ['a_fazer' => 'A fazer', 'em_andamento' => 'Em andamento', 'concluido' => 'Concluído'];
$visible = static fn (string $key): bool => !isset($widgets[$key]) || (bool) $widgets[$key]['visible'];
$widgetAttributes = static function (string $key) use ($widgets): string {
    $widget = $widgets[$key] ?? null;
    $attributes = 'data-widget-type="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '"';
    if (is_array($widget) && isset($widget['id'])) {
        $attributes .= ' data-widget-id="' . htmlspecialchars((string) $widget['id'], ENT_QUOTES, 'UTF-8') . '"';
        $attributes .= ' data-widget-x="' . (int) ($widget['x'] ?? 0) . '" data-widget-y="' . (int) ($widget['y'] ?? 0) . '"';
        $attributes .= ' data-widget-width="' . (int) ($widget['width'] ?? 0) . '" data-widget-height="' . (int) ($widget['height'] ?? 0) . '"';
    }
    return $attributes;
};
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content dashboard-page">
            <section class="page-heading">
                <div>
                    <p class="eyebrow">Visão geral</p>
                    <h1><?= (int) date('H') < 12 ? 'Bom dia' : ((int) date('H') < 18 ? 'Boa tarde' : 'Boa noite') ?>, <?= htmlspecialchars((string) $userName, ENT_QUOTES, 'UTF-8') ?>.</h1>
                    <p>Confira o que importa para o seu dia de estudos.</p>
                </div>
            </section>

            <?php if ($visible('resumo')): ?>
                <section class="summary-grid dashboard-widget" <?= $widgetAttributes('resumo') ?> aria-label="Resumo dos estudos">
                    <article class="summary-card"><span class="summary-icon icon-blue"><i class="bi bi-list-check"></i></span><div><span class="summary-label">Tarefas pendentes</span><strong><?= $summary['pending'] ?></strong><small>tarefas em aberto</small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-green"><i class="bi bi-check2-circle"></i></span><div><span class="summary-label">Tarefas concluídas</span><strong><?= $summary['completed'] ?></strong><small>tarefas finalizadas</small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-purple"><i class="bi bi-stopwatch"></i></span><div><span class="summary-label">Tempo estudado</span><strong><?= $formatDuration($summary['studied_minutes']) ?></strong><small><?= $summary['studied_minutes'] === null ? 'sem sessões registradas' : 'total registrado' ?></small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-orange"><i class="bi bi-graph-up-arrow"></i></span><div><span class="summary-label">Produtividade</span><strong><?= $summary['productivity'] === null ? '—' : number_format($summary['productivity'], 1, ',', '.') ?></strong><small><?= $summary['productivity'] === null ? 'sem registros ainda' : 'média registrada' ?></small></div></article>
                </section>
            <?php endif; ?>

            <div class="dashboard-grid">
                <?php if ($visible('tarefas')): ?>
                    <section class="dashboard-card dashboard-widget widget-wide" <?= $widgetAttributes('tarefas') ?>><div class="card-heading"><div><h2>Próximas tarefas</h2><p>Organizadas pela data de vencimento.</p></div><i class="bi bi-arrow-up-right card-heading-icon"></i></div>
                        <?php if ($dashboard['tasks'] === []): ?><div class="empty-state"><i class="bi bi-check2-square"></i><p>Você ainda não possui tarefas pendentes.</p></div>
                        <?php else: ?><div class="task-list"><?php foreach ($dashboard['tasks'] as $task): ?><article class="task-row"><span class="task-check"><i class="bi bi-circle"></i></span><div class="task-details"><strong><?= htmlspecialchars((string) $task['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string) ($task['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?></span></div><span class="priority-badge <?= $priorityClass[$task['prioridade']] ?? 'priority-medium' ?>"><?= $priorityLabel[$task['prioridade']] ?? htmlspecialchars((string) $task['prioridade'], ENT_QUOTES, 'UTF-8') ?></span><span class="task-date"><i class="bi bi-calendar3"></i><?= $formatDate($task['data_vencimento']) ?></span><span class="status-label"><?= $statusLabel[$task['status']] ?? htmlspecialchars((string) $task['status'], ENT_QUOTES, 'UTF-8') ?></span></article><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('disciplinas')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('disciplinas') ?>><div class="card-heading"><div><h2>Disciplinas</h2><p>Suas áreas de estudo.</p></div><i class="bi bi-book card-heading-icon"></i></div>
                        <?php if ($dashboard['disciplines'] === []): ?><div class="empty-state"><i class="bi bi-book"></i><p>Você ainda não possui disciplinas.</p></div>
                        <?php else: ?><div class="discipline-list"><?php foreach ($dashboard['disciplines'] as $discipline): ?><?php $color = is_string($discipline['cor']) && preg_match('/^#[0-9a-fA-F]{6}$/', $discipline['cor']) ? $discipline['cor'] : null; ?><div class="discipline-row"><span class="discipline-dot"<?= $color ? ' style="background:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"' : '' ?>></span><span><?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('arquivos')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('arquivos') ?>><div class="card-heading"><div><h2>Arquivos recentes</h2><p>Últimos materiais adicionados.</p></div><i class="bi bi-folder2-open card-heading-icon"></i></div>
                        <?php if ($dashboard['files'] === []): ?><div class="empty-state"><i class="bi bi-folder2-open"></i><p>Você ainda não possui arquivos.</p></div>
                        <?php else: ?><div class="compact-list"><?php foreach ($dashboard['files'] as $file): ?><div class="compact-row"><span class="file-icon"><i class="bi bi-file-earmark"></i></span><div class="text-truncate"><strong class="text-truncate d-block"><?= htmlspecialchars((string) ($file['nome_original'] ?: $file['nome_arquivo']), ENT_QUOTES, 'UTF-8') ?></strong><small><?= $formatDate($file['criado_em'], true) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('notas')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('notas') ?>><div class="card-heading"><div><h2>Notas recentes</h2><p>Últimas notas atualizadas.</p></div><i class="bi bi-journal-text card-heading-icon"></i></div>
                        <?php if ($dashboard['notes'] === []): ?><div class="empty-state"><i class="bi bi-journal-text"></i><p>Você ainda não possui notas.</p></div>
                        <?php else: ?><div class="compact-list"><?php foreach ($dashboard['notes'] as $note): ?><div class="compact-row"><span class="note-icon"><i class="bi bi-sticky"></i></span><div class="text-truncate"><strong class="text-truncate d-block"><?= htmlspecialchars((string) $note['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($note['disciplina_nome'] ?? 'Sem disciplina'), ENT_QUOTES, 'UTF-8') ?> · <?= $formatDate($note['atualizado_em']) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
