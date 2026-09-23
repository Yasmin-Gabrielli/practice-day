<?php
use App\Helpers\Url;
$activePage = $activePage ?? '';
$userName = (string) ($userName ?? '');
$userAvatar = $userAvatar ?? null;
$avatarInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1)) : '?';
$isActive = static fn (string $page): string => $activePage === $page ? ' active' : '';
?>
<aside class="app-sidebar d-none d-lg-flex flex-column">
    <a class="app-brand" href="<?= Url::to('/') ?>"><span class="brand-mark"><i class="bi bi-journal-check"></i></span><span>PracticeDay</span></a>
    <nav class="sidebar-nav" aria-label="Navegação principal">
        <a class="sidebar-link<?= $isActive('dashboard') ?>" href="<?= Url::to('/') ?>"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>
        <a class="sidebar-link<?= $isActive('disciplinas') ?>" href="<?= Url::to('/disciplinas') ?>"><i class="bi bi-mortarboard"></i><span>Disciplinas</span></a>
        <a class="sidebar-link<?= $isActive('arquivos') ?>" href="#" aria-disabled="true"><i class="bi bi-folder2-open"></i><span>Meus Arquivos</span></a>
        <a class="sidebar-link<?= $isActive('biblioteca') ?>" href="#" aria-disabled="true"><i class="bi bi-book"></i><span>Biblioteca</span></a>
        <a class="sidebar-link<?= $isActive('planner') ?>" href="#" aria-disabled="true"><i class="bi bi-check2-square"></i><span>Planner</span></a>
        <a class="sidebar-link<?= $isActive('calendario') ?>" href="#" aria-disabled="true"><i class="bi bi-calendar3"></i><span>Calendário</span></a>
        <a class="sidebar-link<?= $isActive('notas') ?>" href="#" aria-disabled="true"><i class="bi bi-journal-text"></i><span>Notas</span></a>
        <a class="sidebar-link<?= $isActive('estatisticas') ?>" href="#" aria-disabled="true"><i class="bi bi-bar-chart-line"></i><span>Estatísticas</span></a>
    </nav>
    <div class="sidebar-divider"></div>
    <nav class="sidebar-nav" aria-label="Conta">
        <a class="sidebar-link<?= $isActive('configuracoes') ?>" href="#" aria-disabled="true"><i class="bi bi-gear"></i><span>Configurações</span></a>
        <form method="post" action="<?= Url::to('/logout') ?>" class="m-0">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <button class="sidebar-link sidebar-logout" type="submit"><i class="bi bi-box-arrow-right"></i><span>Sair</span></button>
        </form>
    </nav>
    <div class="sidebar-profile mt-auto">
        <?php if (is_string($userAvatar) && $userAvatar !== ''): ?>
            <img class="avatar avatar-sm" src="<?= htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">
        <?php else: ?>
            <span class="avatar avatar-sm" aria-label="Avatar de <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <span class="text-truncate"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</aside>
