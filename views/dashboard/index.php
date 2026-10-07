<?php
use App\Helpers\Lang;

$summary = $dashboard['summary'];
$widgets = $dashboard['widgets'];
$formatDate = static function (?string $date, bool $withTime = false): string {
    if ($date === null || $date === '') {
        return Lang::get('common.no_date');
    }
    return (new \DateTimeImmutable($date))->format($withTime ? 'd/m/Y · H:i' : 'd/m/Y');
};
$formatDuration = static function (?int $minutes): string {
    if ($minutes === null) return '—';
    if ($minutes < 60) return $minutes . ' min';
    return intdiv($minutes, 60) . 'h' . ($minutes % 60 ? ' ' . ($minutes % 60) . 'min' : '');
};
$priorityClass = ['baixa' => 'priority-low', 'media' => 'priority-medium', 'alta' => 'priority-high'];
$priorityLabel = ['baixa' => Lang::get('task.priority.baixa'), 'media' => Lang::get('task.priority.media'), 'alta' => Lang::get('task.priority.alta')];
$statusLabel = ['a_fazer' => Lang::get('task.status.a_fazer'), 'em_andamento' => Lang::get('task.status.em_andamento'), 'concluido' => Lang::get('task.status.concluido')];
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
                    <p class="eyebrow"><?= htmlspecialchars(Lang::get('dashboard.eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h1><?= htmlspecialchars((int) date('H') < 12 ? Lang::get('dashboard.greeting_morning') : ((int) date('H') < 18 ? Lang::get('dashboard.greeting_afternoon') : Lang::get('dashboard.greeting_evening')), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) $userName, ENT_QUOTES, 'UTF-8') ?>.</h1>
                    <p><?= htmlspecialchars(Lang::get('dashboard.subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </section>

            <?php if ($visible('resumo')): ?>
                <section class="summary-grid dashboard-widget" <?= $widgetAttributes('resumo') ?> aria-label="<?= htmlspecialchars(Lang::get('dashboard.summary_aria'), ENT_QUOTES, 'UTF-8') ?>">
                    <article class="summary-card"><span class="summary-icon icon-blue"><i class="bi bi-list-check"></i></span><div><span class="summary-label"><?= htmlspecialchars(Lang::get('summary.pending'), ENT_QUOTES, 'UTF-8') ?></span><strong><?= $summary['pending'] ?></strong><small><?= htmlspecialchars(Lang::get('summary.pending_sub'), ENT_QUOTES, 'UTF-8') ?></small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-green"><i class="bi bi-check2-circle"></i></span><div><span class="summary-label"><?= htmlspecialchars(Lang::get('summary.completed'), ENT_QUOTES, 'UTF-8') ?></span><strong><?= $summary['completed'] ?></strong><small><?= htmlspecialchars(Lang::get('summary.completed_sub'), ENT_QUOTES, 'UTF-8') ?></small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-purple"><i class="bi bi-stopwatch"></i></span><div><span class="summary-label"><?= htmlspecialchars(Lang::get('summary.studied'), ENT_QUOTES, 'UTF-8') ?></span><strong><?= $formatDuration($summary['studied_minutes']) ?></strong><small><?= htmlspecialchars($summary['studied_minutes'] === null ? Lang::get('summary.studied_none') : Lang::get('summary.studied_total'), ENT_QUOTES, 'UTF-8') ?></small></div></article>
                    <article class="summary-card"><span class="summary-icon icon-orange"><i class="bi bi-graph-up-arrow"></i></span><div><span class="summary-label"><?= htmlspecialchars(Lang::get('summary.productivity'), ENT_QUOTES, 'UTF-8') ?></span><strong><?= $summary['productivity'] === null ? '—' : number_format($summary['productivity'], 1, ',', '.') ?></strong><small><?= htmlspecialchars($summary['productivity'] === null ? Lang::get('summary.productivity_none') : Lang::get('summary.productivity_avg'), ENT_QUOTES, 'UTF-8') ?></small></div></article>
                </section>
            <?php endif; ?>

            <div class="dashboard-grid">
                <?php if ($visible('tarefas')): ?>
                    <section class="dashboard-card dashboard-widget widget-wide" <?= $widgetAttributes('tarefas') ?>><div class="card-heading"><div><h2><?= htmlspecialchars(Lang::get('widget.tasks_title'), ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars(Lang::get('widget.tasks_sub'), ENT_QUOTES, 'UTF-8') ?></p></div><i class="bi bi-arrow-up-right card-heading-icon"></i></div>
                        <?php if ($dashboard['tasks'] === []): ?><div class="empty-state"><i class="bi bi-check2-square"></i><p><?= htmlspecialchars(Lang::get('widget.tasks_empty'), ENT_QUOTES, 'UTF-8') ?></p></div>
                        <?php else: ?><div class="task-list"><?php foreach ($dashboard['tasks'] as $task): ?><article class="task-row"><span class="task-check"><i class="bi bi-circle"></i></span><div class="task-details"><strong><?= htmlspecialchars((string) $task['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string) ($task['disciplina_nome'] ?? Lang::get('common.no_discipline')), ENT_QUOTES, 'UTF-8') ?></span></div><span class="priority-badge <?= $priorityClass[$task['prioridade']] ?? 'priority-medium' ?>"><?= $priorityLabel[$task['prioridade']] ?? htmlspecialchars((string) $task['prioridade'], ENT_QUOTES, 'UTF-8') ?></span><span class="task-date"><i class="bi bi-calendar3"></i><?= $formatDate($task['data_vencimento']) ?></span><span class="status-label"><?= $statusLabel[$task['status']] ?? htmlspecialchars((string) $task['status'], ENT_QUOTES, 'UTF-8') ?></span></article><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('disciplinas')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('disciplinas') ?>><div class="card-heading"><div><h2><?= htmlspecialchars(Lang::get('widget.disciplines_title'), ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars(Lang::get('widget.disciplines_sub'), ENT_QUOTES, 'UTF-8') ?></p></div><i class="bi bi-book card-heading-icon"></i></div>
                        <?php if ($dashboard['disciplines'] === []): ?><div class="empty-state"><i class="bi bi-book"></i><p><?= htmlspecialchars(Lang::get('widget.disciplines_empty'), ENT_QUOTES, 'UTF-8') ?></p></div>
                        <?php else: ?><div class="discipline-list"><?php foreach ($dashboard['disciplines'] as $discipline): ?><?php $color = is_string($discipline['cor']) && preg_match('/^#[0-9a-fA-F]{6}$/', $discipline['cor']) ? $discipline['cor'] : null; ?><div class="discipline-row"><span class="discipline-dot"<?= $color ? ' style="background:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"' : '' ?>></span><span><?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('arquivos')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('arquivos') ?>><div class="card-heading"><div><h2><?= htmlspecialchars(Lang::get('widget.files_title'), ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars(Lang::get('widget.files_sub'), ENT_QUOTES, 'UTF-8') ?></p></div><i class="bi bi-folder2-open card-heading-icon"></i></div>
                        <?php if ($dashboard['files'] === []): ?><div class="empty-state"><i class="bi bi-folder2-open"></i><p><?= htmlspecialchars(Lang::get('widget.files_empty'), ENT_QUOTES, 'UTF-8') ?></p></div>
                        <?php else: ?><div class="compact-list"><?php foreach ($dashboard['files'] as $file): ?><div class="compact-row"><span class="file-icon"><i class="bi bi-file-earmark"></i></span><div class="text-truncate"><strong class="text-truncate d-block"><?= htmlspecialchars((string) ($file['nome_original'] ?: $file['nome_arquivo']), ENT_QUOTES, 'UTF-8') ?></strong><small><?= $formatDate($file['criado_em'], true) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($visible('notas')): ?>
                    <section class="dashboard-card dashboard-widget" <?= $widgetAttributes('notas') ?>><div class="card-heading"><div><h2><?= htmlspecialchars(Lang::get('widget.notes_title'), ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars(Lang::get('widget.notes_sub'), ENT_QUOTES, 'UTF-8') ?></p></div><i class="bi bi-journal-text card-heading-icon"></i></div>
                        <?php if ($dashboard['notes'] === []): ?><div class="empty-state"><i class="bi bi-journal-text"></i><p><?= htmlspecialchars(Lang::get('widget.notes_empty'), ENT_QUOTES, 'UTF-8') ?></p></div>
                        <?php else: ?><div class="compact-list"><?php foreach ($dashboard['notes'] as $note): ?><div class="compact-row"><span class="note-icon"><i class="bi bi-sticky"></i></span><div class="text-truncate"><strong class="text-truncate d-block"><?= htmlspecialchars((string) $note['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($note['disciplina_nome'] ?? Lang::get('common.no_discipline')), ENT_QUOTES, 'UTF-8') ?> · <?= $formatDate($note['atualizado_em']) ?></small></div></div><?php endforeach; ?></div><?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
