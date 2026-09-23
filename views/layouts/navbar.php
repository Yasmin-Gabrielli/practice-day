<?php
use App\Helpers\Url;
$userName = (string) ($userName ?? '');
$userAvatar = $userAvatar ?? null;
$avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';
?>
<header class="app-navbar">
    <a class="mobile-brand d-lg-none" href="<?= Url::to('/') ?>"><span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span></a>
    <div class="global-search d-none d-md-flex" role="search">
        <i class="bi bi-search"></i>
        <input type="search" aria-label="Pesquisa global" placeholder="Pesquisar no PracticeDay">
        <kbd>⌘ K</kbd>
    </div>
    <div class="navbar-actions ms-auto">
        <button class="icon-button" type="button" aria-label="Notificações" title="Notificações"><i class="bi bi-bell"></i><span class="notification-dot"></span></button>
        <div class="user-summary">
            <?php if (is_string($userAvatar) && $userAvatar !== ''): ?>
                <img class="avatar" src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <span class="avatar" aria-label="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="d-none d-sm-inline fw-semibold small"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
</header>
