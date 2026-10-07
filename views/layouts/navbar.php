<?php
use App\Helpers\Lang;
use App\Helpers\Url;
$userName   = (string) ($userName ?? '');
$userAvatar = $userAvatar ?? null;
$avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';
$pendingCount  = (int) ($_SESSION['notification_pending_count'] ?? 0);
?>
<header class="app-navbar">
    <a class="mobile-brand d-lg-none" href="<?= Url::to('/') ?>"><span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span></a>
    <div class="global-search d-none d-md-flex" role="search">
        <i class="bi bi-search"></i>
        <input type="search" aria-label="<?= htmlspecialchars(Lang::get('topbar.search_aria'), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(Lang::get('topbar.search_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
        <kbd>⌘ K</kbd>
    </div>
    <div class="navbar-actions ms-auto">
        <a class="icon-button notif-bell-btn <?= $pendingCount > 0 ? 'has-pending' : '' ?>"
           href="<?= Url::to('/notificacoes') ?>"
           title="<?= $pendingCount > 0 ? htmlspecialchars(Lang::get('topbar.notifications_title', ['count' => $pendingCount]), ENT_QUOTES, 'UTF-8') : htmlspecialchars(Lang::get('topbar.notifications'), ENT_QUOTES, 'UTF-8') ?>"
           aria-label="<?= htmlspecialchars(Lang::get('topbar.notifications'), ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-bell<?= $pendingCount > 0 ? '-fill' : '' ?>"></i>
            <?php if ($pendingCount > 0): ?>
                <span class="notif-badge" aria-label="<?= htmlspecialchars(Lang::get('topbar.notifications_badge', ['count' => $pendingCount]), ENT_QUOTES, 'UTF-8') ?>">
                    <?= $pendingCount <= 99 ? $pendingCount : '99+' ?>
                </span>
            <?php endif; ?>
        </a>
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
