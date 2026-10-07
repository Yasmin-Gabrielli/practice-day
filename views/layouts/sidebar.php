<?php

    use App\Helpers\Lang;
    use App\Helpers\Url;

    $activePage = $activePage ?? '';

    $userName = (string) ($userName ?? '');

    $userAvatar = $userAvatar ?? null;

    $avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';

    $isActive = static fn(string $page): string => $activePage === $page ? ' active' : '';

?>

<aside class="app-sidebar d-none d-lg-flex flex-column">

    <div class="sidebar-header flex-shrink-0">
        <a class="app-brand" href="<?php echo Url::to('/') ?>"><span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span></a>
    </div>

    <div class="sidebar-scroll flex-grow-1">

        <nav class="sidebar-nav" aria-label="<?= htmlspecialchars(Lang::get('nav.main_aria'), ENT_QUOTES, 'UTF-8') ?>">

            <a class="sidebar-link<?php echo $isActive('dashboard') ?>" href="<?php echo Url::to('/') ?>"><i class="bi bi-grid-1x2"></i><span><?= htmlspecialchars(Lang::get('nav.dashboard'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('disciplinas') ?>" href="<?php echo Url::to('/disciplinas') ?>"><i class="bi bi-mortarboard"></i><span><?= htmlspecialchars(Lang::get('nav.disciplinas'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('arquivos') ?>" href="<?php echo Url::to('/arquivos') ?>"><i class="bi bi-folder2-open"></i><span><?= htmlspecialchars(Lang::get('nav.arquivos'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('favoritos') ?>" href="<?php echo Url::to('/favoritos') ?>"><i class="bi bi-star-fill"></i><span><?= htmlspecialchars(Lang::get('nav.favoritos'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('lixeira') ?>" href="<?php echo Url::to('/lixeira') ?>"><i class="bi bi-trash2"></i><span><?= htmlspecialchars(Lang::get('nav.lixeira'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('biblioteca') ?>" href="<?php echo Url::to('/biblioteca') ?>"><i class="bi bi-book"></i><span><?= htmlspecialchars(Lang::get('nav.biblioteca'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('scanner') ?>" href="<?php echo Url::to('/scanner') ?>"><i class="bi bi-camera"></i><span><?= htmlspecialchars(Lang::get('nav.scanner'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('planner') ?>" href="<?php echo Url::to('/planner') ?>"><i class="bi bi-check2-square"></i><span><?= htmlspecialchars(Lang::get('nav.planner'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('calendario') ?>" href="<?php echo Url::to('/calendario') ?>"><i class="bi bi-calendar3"></i><span><?= htmlspecialchars(Lang::get('nav.calendario'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('notas') ?>" href="<?php echo Url::to('/notas') ?>"><i class="bi bi-journal-text"></i><span><?= htmlspecialchars(Lang::get('nav.notas'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('pomodoro') ?>" href="<?php echo Url::to('/pomodoro') ?>"><i class="bi bi-clock-history"></i><span><?= htmlspecialchars(Lang::get('nav.pomodoro'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('estatisticas') ?>" href="<?php echo Url::to('/estatisticas') ?>"><i class="bi bi-bar-chart-line"></i><span><?= htmlspecialchars(Lang::get('nav.estatisticas'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <a class="sidebar-link<?php echo $isActive('notificacoes') ?>" href="<?php echo Url::to('/notificacoes') ?>">
                <i class="bi bi-bell<?php echo isset($_SESSION['notification_pending_count']) && (int) $_SESSION['notification_pending_count'] > 0 ? '-fill text-primary' : '' ?>"></i>
                <span><?= htmlspecialchars(Lang::get('nav.notificacoes'), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (isset($_SESSION['notification_pending_count']) && (int) $_SESSION['notification_pending_count'] > 0): ?>
                    <span class="ms-auto sidebar-notif-badge"><?= (int) $_SESSION['notification_pending_count'] <= 99 ? (int) $_SESSION['notification_pending_count'] : '99+' ?></span>
                <?php endif; ?>
            </a>

        </nav>

        <div class="sidebar-divider"></div>

        <nav class="sidebar-nav" aria-label="<?= htmlspecialchars(Lang::get('nav.account_aria'), ENT_QUOTES, 'UTF-8') ?>">

            <a class="sidebar-link<?php echo $isActive('configuracoes') ?>" href="<?php echo Url::to('/configuracoes') ?>"><i class="bi bi-gear"></i><span><?= htmlspecialchars(Lang::get('nav.configuracoes'), ENT_QUOTES, 'UTF-8') ?></span></a>

            <form method="post" action="<?php echo Url::to('/logout') ?>" class="m-0">

                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <button class="sidebar-link sidebar-logout" type="submit"><i class="bi bi-box-arrow-right"></i><span><?= htmlspecialchars(Lang::get('nav.logout'), ENT_QUOTES, 'UTF-8') ?></span></button>

            </form>

        </nav>

    </div>

    <div class="sidebar-footer flex-shrink-0 mt-auto">

        <div class="sidebar-profile">

            <?php if (is_string($userAvatar) && $userAvatar !== ''): ?>

                <img class="avatar avatar-sm" src="<?php echo htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>">

            <?php else: ?>

                <span class="avatar avatar-sm" aria-label="<?= htmlspecialchars(Lang::get('nav.avatar_of', ['name' => $userName]), ENT_QUOTES, 'UTF-8') ?>"><?php echo htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>

            <?php endif; ?>

            <span class="text-truncate"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>

        </div>

    </div>

</aside>

