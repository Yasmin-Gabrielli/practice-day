<?php
use App\Helpers\Lang;
use App\Helpers\Url;

/* Drawer móvel incluído pelo navbar; evita duplicar o id quando a view
   também inclui este arquivo diretamente. */
if (!empty($GLOBALS['__mobile_nav_rendered'])) {
    return;
}
$GLOBALS['__mobile_nav_rendered'] = true;

$activePage = $activePage ?? '';
$userName = (string) ($userName ?? '');
$userAvatar = $userAvatar ?? null;
$avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';
$isActive = static fn (string $page): string => $activePage === $page ? ' active' : '';
?>
<div class="offcanvas offcanvas-start mobile-nav-drawer d-lg-none" tabindex="-1" id="mobile-nav-drawer"
     aria-labelledby="mobile-nav-drawer-label" aria-hidden="true">

    <div class="offcanvas-header mobile-nav-header">
        <a class="app-brand" id="mobile-nav-drawer-label" href="<?= Url::to('/') ?>">
            <span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body mobile-nav-body">
        <nav class="sidebar-nav" aria-label="<?= htmlspecialchars(Lang::get('nav.main_aria'), ENT_QUOTES, 'UTF-8') ?>">
            <a class="sidebar-link<?= $isActive('dashboard') ?>" href="<?= Url::to('/') ?>"><i class="bi bi-grid-1x2"></i><span><?= htmlspecialchars(Lang::get('nav.dashboard'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('disciplinas') ?>" href="<?= Url::to('/disciplinas') ?>"><i class="bi bi-mortarboard"></i><span><?= htmlspecialchars(Lang::get('nav.disciplinas'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('arquivos') ?>" href="<?= Url::to('/arquivos') ?>"><i class="bi bi-folder2-open"></i><span><?= htmlspecialchars(Lang::get('nav.arquivos'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('favoritos') ?>" href="<?= Url::to('/favoritos') ?>"><i class="bi bi-star-fill"></i><span><?= htmlspecialchars(Lang::get('nav.favoritos'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('lixeira') ?>" href="<?= Url::to('/lixeira') ?>"><i class="bi bi-trash2"></i><span><?= htmlspecialchars(Lang::get('nav.lixeira'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('biblioteca') ?>" href="<?= Url::to('/biblioteca') ?>"><i class="bi bi-book"></i><span><?= htmlspecialchars(Lang::get('nav.biblioteca'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('scanner') ?>" href="<?= Url::to('/scanner') ?>"><i class="bi bi-camera"></i><span><?= htmlspecialchars(Lang::get('nav.scanner'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('planner') ?>" href="<?= Url::to('/planner') ?>"><i class="bi bi-check2-square"></i><span><?= htmlspecialchars(Lang::get('nav.planner'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('calendario') ?>" href="<?= Url::to('/calendario') ?>"><i class="bi bi-calendar3"></i><span><?= htmlspecialchars(Lang::get('nav.calendario'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('notas') ?>" href="<?= Url::to('/notas') ?>"><i class="bi bi-journal-text"></i><span><?= htmlspecialchars(Lang::get('nav.notas'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('pomodoro') ?>" href="<?= Url::to('/pomodoro') ?>"><i class="bi bi-clock-history"></i><span><?= htmlspecialchars(Lang::get('nav.pomodoro'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <a class="sidebar-link<?= $isActive('estatisticas') ?>" href="<?= Url::to('/estatisticas') ?>"><i class="bi bi-bar-chart-line"></i><span><?= htmlspecialchars(Lang::get('nav.estatisticas'), ENT_QUOTES, 'UTF-8') ?></span></a>
        </nav>

        <div class="sidebar-divider"></div>

        <nav class="sidebar-nav" aria-label="<?= htmlspecialchars(Lang::get('nav.account_aria'), ENT_QUOTES, 'UTF-8') ?>">
            <a class="sidebar-link<?= $isActive('configuracoes') ?>" href="<?= Url::to('/configuracoes') ?>"><i class="bi bi-gear"></i><span><?= htmlspecialchars(Lang::get('nav.configuracoes'), ENT_QUOTES, 'UTF-8') ?></span></a>
            <form method="post" action="<?= Url::to('/logout') ?>" class="m-0">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <button class="sidebar-link sidebar-logout" type="submit"><i class="bi bi-box-arrow-right"></i><span><?= htmlspecialchars(Lang::get('nav.logout'), ENT_QUOTES, 'UTF-8') ?></span></button>
            </form>
        </nav>
    </div>

    <div class="mobile-nav-footer">
        <div class="sidebar-profile">
            <?php if (is_string($userAvatar) && $userAvatar !== ''): ?>
                <img class="avatar avatar-sm" src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <span class="avatar avatar-sm" aria-label="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="text-truncate"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
</div>
