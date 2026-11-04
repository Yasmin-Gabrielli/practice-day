<?php

use App\Helpers\Lang;
use App\Helpers\Url;

$features = [
    [
        'icon' => 'bi-collection-play',
        'title' => Lang::get('auth.feature_organize_title'),
        'text' => Lang::get('auth.feature_organize_text'),
    ],
    [
        'icon' => 'bi-bullseye',
        'title' => Lang::get('auth.feature_focus_title'),
        'text' => Lang::get('auth.feature_focus_text'),
    ],
    [
        'icon' => 'bi-graph-up-arrow',
        'title' => Lang::get('auth.feature_progress_title'),
        'text' => Lang::get('auth.feature_progress_text'),
    ],
];
?>
<aside class="auth-showcase">
    <a class="auth-brand" href="<?= Url::to('/') ?>" aria-label="PracticeDay">
        <span class="auth-brand-mark">
            <img src="<?= Url::asset('assets/images/logos-practice-day/logo-practice-day-interira2.svg') ?>" alt="PracticeDay">
        </span>
    </a>

    <div class="auth-showcase-copy">
        <span class="auth-showcase-kicker">
            <i class="bi bi-stars" aria-hidden="true"></i>
            <?= htmlspecialchars(Lang::get('auth.showcase_eyebrow'), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <h2><?= htmlspecialchars(Lang::get('auth.showcase_title'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars(Lang::get('auth.showcase_text'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <ul class="auth-feature-list">
        <?php foreach ($features as $feature): ?>
            <li class="auth-feature">
                <span class="auth-feature-icon"><i class="bi <?= htmlspecialchars($feature['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                <span class="auth-feature-copy">
                    <strong><?= htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($feature['text'], ENT_QUOTES, 'UTF-8') ?></small>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <p class="auth-showcase-note">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <?= htmlspecialchars(Lang::get('auth.secure_note'), ENT_QUOTES, 'UTF-8') ?>
    </p>
</aside>
