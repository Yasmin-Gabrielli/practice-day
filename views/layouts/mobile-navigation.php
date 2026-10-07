<?php use App\Helpers\Lang; use App\Helpers\Url; $activePage = $activePage ?? ''; ?>
<nav class="mobile-navigation d-lg-none" aria-label="<?= htmlspecialchars(Lang::get('nav.mobile_aria'), ENT_QUOTES, 'UTF-8') ?>">
    <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= Url::to('/') ?>"><i class="bi bi-house-door"></i><span><?= htmlspecialchars(Lang::get('nav.home'), ENT_QUOTES, 'UTF-8') ?></span></a>
    <a class="<?= $activePage === 'biblioteca' ? 'active' : '' ?>" href="<?= Url::to('/biblioteca') ?>"><i class="bi bi-book"></i><span><?= htmlspecialchars(Lang::get('nav.biblioteca'), ENT_QUOTES, 'UTF-8') ?></span></a>
    <a class="<?= $activePage === 'scanner' ? 'active' : '' ?>" href="<?= Url::to('/scanner') ?>"><i class="bi bi-camera"></i><span><?= htmlspecialchars(Lang::get('nav.scanner'), ENT_QUOTES, 'UTF-8') ?></span></a>
    <a class="<?= $activePage === 'notas' ? 'active' : '' ?>" href="<?= Url::to('/notas') ?>"><i class="bi bi-journal-text"></i><span><?= htmlspecialchars(Lang::get('nav.notas'), ENT_QUOTES, 'UTF-8') ?></span></a>
    <a href="#" aria-disabled="true"><i class="bi bi-person"></i><span><?= htmlspecialchars(Lang::get('nav.profile'), ENT_QUOTES, 'UTF-8') ?></span></a>
</nav>
<button class="quick-action d-lg-none" type="button" aria-label="<?= htmlspecialchars(Lang::get('nav.quick_action'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(Lang::get('nav.quick_action'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-plus-lg"></i></button>
