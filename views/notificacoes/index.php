<?php

use App\Helpers\Url;

require dirname(__DIR__) . '/layouts/header.php';

/**
 * @var string $csrfToken
 * @var int    $pendingCount
 * @var array{
 *   tarefas_vencidas: list<array<string,mixed>>,
 *   tarefas_proximas: list<array<string,mixed>>,
 *   eventos: list<array<string,mixed>>,
 *   lembretes: list<array<string,mixed>>
 * } $groups
 * @var array<string,list<string>>|null $feedback
 */

$totalItems = 0;
foreach ($groups as $g) {
    $totalItems += count($g);
}

$groupMeta = [
    'tarefas_vencidas' => ['label' => 'Tarefas Vencidas', 'icon' => 'bi-exclamation-circle-fill', 'color' => 'danger'],
    'tarefas_proximas' => ['label' => 'Vencimento Próximo', 'icon' => 'bi-calendar-event-fill', 'color' => 'warning'],
    'eventos'          => ['label' => 'Eventos Próximos', 'icon' => 'bi-calendar3', 'color' => 'primary'],
    'lembretes'        => ['label' => 'Lembretes', 'icon' => 'bi-bell-fill', 'color' => 'info'],
];

function fmtRelDate(string $raw): string
{
    if ($raw === '') {
        return '—';
    }
    $ts = strtotime($raw);
    if ($ts === false) {
        return htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
    }
    $now  = time();
    $diff = $ts - $now;
    $abs  = abs($diff);

    if ($abs < 60) {
        return $diff < 0 ? 'Agora mesmo' : 'Em instantes';
    }
    if ($abs < 3600) {
        $m = (int) round($abs / 60);
        return $diff < 0 ? "Há {$m} min" : "Em {$m} min";
    }
    if ($abs < 86400) {
        $h = (int) round($abs / 3600);
        return $diff < 0 ? "Há {$h}h" : "Em {$h}h";
    }
    if ($abs < 86400 * 7) {
        $d = (int) round($abs / 86400);
        return $diff < 0 ? "Há {$d} dias" : "Em {$d} dias";
    }

    return date('d/m/Y H:i', $ts);
}
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">

            <section class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <p class="eyebrow">Central</p>
                    <h1>Notificações</h1>
                    <p>
                        <?php if ($pendingCount > 0): ?>
                            Você tem <strong><?= $pendingCount ?></strong> notificação<?= $pendingCount !== 1 ? 'ões' : '' ?> pendente<?= $pendingCount !== 1 ? 's' : '' ?>.
                        <?php else: ?>
                            Nenhuma notificação pendente. Tudo em dia!
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($pendingCount > 0): ?>
                    <form method="POST" action="<?= Url::to('/notificacoes/visualizar-todas') ?>">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-outline-secondary btn-sm" id="btn-mark-all-read">
                            <i class="bi bi-check2-all me-1"></i> Marcar todas como lidas
                        </button>
                    </form>
                <?php endif; ?>
            </section>

            <?php if (!empty($feedback['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show mt-1" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $feedback['success'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($feedback['errors']['geral'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars((string) $feedback['errors']['geral'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <?php if ($totalItems === 0): ?>
                <!-- Estado vazio -->
                <div class="notif-empty-state">
                    <div class="notif-empty-icon">
                        <i class="bi bi-bell-slash"></i>
                    </div>
                    <h2>Sem notificações</h2>
                    <p>Você não possui tarefas vencidas, eventos próximos ou lembretes agendados. Continue assim!</p>
                    <a href="<?= Url::to('/planner') ?>" class="btn btn-primary mt-1">
                        <i class="bi bi-check2-square me-1"></i> Ver Planner
                    </a>
                </div>

            <?php else: ?>
                <div class="notif-groups mt-2">
                    <?php foreach ($groupMeta as $groupKey => $meta):
                        $items = $groups[$groupKey] ?? [];
                        if (empty($items)) {
                            continue;
                        }
                    ?>
                        <div class="notif-group-section" id="group-<?= htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="notif-group-header">
                                <span class="notif-group-icon text-<?= $meta['color'] ?>">
                                    <i class="bi <?= $meta['icon'] ?>"></i>
                                </span>
                                <h2 class="notif-group-title"><?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?></h2>
                                <span class="notif-group-count badge bg-<?= $meta['color'] ?> bg-opacity-15 text-<?= $meta['color'] ?>">
                                    <?= count($items) ?>
                                </span>
                            </div>

                            <div class="notif-card-list">
                                <?php foreach ($items as $item):
                                    $isRead  = !empty($item['read']);
                                    $key     = htmlspecialchars((string) ($item['key'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    $title   = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    $sub     = htmlspecialchars((string) ($item['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    $url     = Url::to(ltrim((string) ($item['url'] ?? '/'), ''));
                                    $dateRel = fmtRelDate((string) ($item['occurred_at'] ?? ''));
                                ?>
                                    <div class="notif-card <?= $isRead ? 'notif-card--read' : 'notif-card--unread' ?>">
                                        <div class="notif-card-icon-wrap text-<?= $meta['color'] ?> bg-<?= $meta['color'] ?> bg-opacity-10">
                                            <i class="bi <?= htmlspecialchars((string) ($item['icon'] ?? 'bi-bell'), ENT_QUOTES, 'UTF-8') ?>"></i>
                                        </div>
                                        <div class="notif-card-body">
                                            <div class="notif-card-label"><?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="notif-card-title">
                                                <a href="<?= $url ?>" class="notif-card-link"><?= $title ?></a>
                                                <?php if (!$isRead): ?>
                                                    <span class="notif-unread-dot" title="Não lida"></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($sub !== ''): ?>
                                                <div class="notif-card-sub"><?= $sub ?></div>
                                            <?php endif; ?>
                                            <div class="notif-card-date">
                                                <i class="bi bi-clock me-1"></i><?= $dateRel ?>
                                            </div>
                                        </div>
                                        <div class="notif-card-actions">
                                            <a href="<?= $url ?>" class="btn btn-sm btn-outline-secondary notif-action-btn" title="Ver detalhes">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                            <?php if (!$isRead): ?>
                                                <form method="POST" action="<?= Url::to('/notificacoes/visualizar') ?>" class="d-inline">
                                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="notification_key" value="<?= $key ?>">
                                                    <input type="hidden" name="redirect" value="/notificacoes">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary notif-action-btn" title="Marcar como lida">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
