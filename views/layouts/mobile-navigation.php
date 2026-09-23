<?php use App\Helpers\Url; $activePage = $activePage ?? ''; ?>
<nav class="mobile-navigation d-lg-none" aria-label="Navegação móvel">
    <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= Url::to('/') ?>"><i class="bi bi-house-door"></i><span>Início</span></a>
    <a href="#" aria-disabled="true"><i class="bi bi-folder2-open"></i><span>Arquivos</span></a>
    <a href="#" aria-disabled="true"><i class="bi bi-check2-square"></i><span>Planner</span></a>
    <a href="#" aria-disabled="true"><i class="bi bi-journal-text"></i><span>Notas</span></a>
    <a href="#" aria-disabled="true"><i class="bi bi-person"></i><span>Perfil</span></a>
</nav>
<button class="quick-action d-lg-none" type="button" aria-label="Ação rápida" title="Ação rápida"><i class="bi bi-plus-lg"></i></button>
