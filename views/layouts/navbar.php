<?php
use App\Helpers\Lang;
use App\Helpers\Url;
$userName   = (string) ($userName ?? '');
$userAvatar = $userAvatar ?? null;
$avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';
$pendingCount  = (int) ($_SESSION['notification_pending_count'] ?? 0);
?>
<header class="app-navbar">
    <button class="icon-button hamburger-btn d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#mobile-nav-drawer"
            aria-controls="mobile-nav-drawer"
            aria-label="<?= htmlspecialchars(Lang::get('nav.mobile_aria'), ENT_QUOTES, 'UTF-8') ?>">
        <i class="bi bi-list"></i>
    </button>
    <a class="mobile-brand d-lg-none" href="<?= Url::to('/') ?>"><span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span></a>

    <div class="navbar-actions ms-auto">
        <button type="button" class="icon-button notif-bell-btn <?= $pendingCount > 0 ? 'has-pending' : '' ?>"
                data-bs-toggle="modal" data-bs-target="#modal-notifications"
                title="<?= $pendingCount > 0 ? htmlspecialchars(Lang::get('topbar.notifications_title', ['count' => $pendingCount]), ENT_QUOTES, 'UTF-8') : htmlspecialchars(Lang::get('topbar.notifications'), ENT_QUOTES, 'UTF-8') ?>"
                aria-label="<?= htmlspecialchars(Lang::get('topbar.notifications'), ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-bell<?= $pendingCount > 0 ? '-fill' : '' ?>"></i>
            <?php if ($pendingCount > 0): ?>
                <span class="notif-badge" aria-label="<?= htmlspecialchars(Lang::get('topbar.notifications_badge', ['count' => $pendingCount]), ENT_QUOTES, 'UTF-8') ?>">
                    <?= $pendingCount <= 99 ? $pendingCount : '99+' ?>
                </span>
            <?php endif; ?>
        </button>
        <div class="user-summary">
            <?php if (is_string($userAvatar) && $userAvatar !== ''): ?>
                <img class="avatar" src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <span class="avatar" aria-label="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="d-none d-sm-inline fw-semibold small"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
</header>

<!-- Modal de notificações (sininho do topo) -->
<div class="modal fade" id="modal-notifications" tabindex="-1" aria-labelledby="modal-notifications-title" aria-hidden="true"
     data-csrf-token="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>"
     data-feed-url="<?= htmlspecialchars(Url::to('/notificacoes/feed'), ENT_QUOTES, 'UTF-8') ?>"
     data-mark-url="<?= htmlspecialchars(Url::to('/notificacoes/visualizar'), ENT_QUOTES, 'UTF-8') ?>"
     data-mark-all-url="<?= htmlspecialchars(Url::to('/notificacoes/visualizar-todas'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable notif-modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="notif-modal-bell"><i class="bi bi-bell"></i></span>
                    <div class="lh-sm">
                        <h2 class="modal-title fs-6 fw-bold mb-0" id="modal-notifications-title"><?= htmlspecialchars(Lang::get('topbar.notifications'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <small class="text-muted" id="notif-modal-subtitle">&nbsp;</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="notif-mark-all">
                        <i class="bi bi-check2-all me-1"></i>Todas
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
            </div>
            <div class="modal-body notif-modal-body px-2 py-2" id="notif-modal-body">
                <div class="text-center text-muted small py-4">
                    <span class="spinner-border spinner-border-sm me-2"></span>Carregando notificações...
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <a class="btn btn-sm btn-outline-secondary" href="<?= Url::to('/notificacoes') ?>">
                    <i class="bi bi-inbox me-1"></i>Ver central de notificações
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/mobile-navigation.php'; ?>
