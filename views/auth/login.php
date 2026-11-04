<?php
use App\Helpers\Lang;
use App\Helpers\Url;

$errors = $feedback['errors'] ?? [];
$old = $feedback['old'] ?? [];
require dirname(__DIR__) . '/layouts/header.php';
?>
<main class="auth-page">
    <div class="auth-ambient auth-ambient--one" aria-hidden="true"></div>
    <div class="auth-ambient auth-ambient--two" aria-hidden="true"></div>

    <div class="auth-shell">
        <?php require __DIR__ . '/_showcase.php'; ?>

        <section class="auth-panel">
            <div class="auth-form-wrap">
                <header class="auth-form-head">
                    <span class="auth-eyebrow"><?= htmlspecialchars(Lang::get('auth.login_eyebrow'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h1><?= htmlspecialchars(Lang::get('auth.login_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                    <p><?= htmlspecialchars(Lang::get('auth.login_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
                </header>

                <?php if (isset($feedback['success'])): ?>
                    <div class="auth-alert auth-alert--success" role="alert">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        <span><?= htmlspecialchars((string) $feedback['success'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($errors['credenciais'])): ?>
                    <div class="auth-alert auth-alert--danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <span><?= htmlspecialchars((string) $errors['credenciais'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= Url::to('/login') ?>" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="auth-field">
                        <label class="form-label" for="email"><?= htmlspecialchars(Lang::get('auth.email'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="auth-input">
                            <i class="bi bi-envelope auth-input-icon" aria-hidden="true"></i>
                            <input
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                <?= isset($errors['email']) ? 'aria-describedby="email-error"' : '' ?>
                                aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
                                required
                            >
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div id="email-error" class="invalid-feedback d-block"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="senha"><?= htmlspecialchars(Lang::get('auth.password'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="auth-input">
                            <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                            <input
                                class="form-control <?= isset($errors['senha']) ? 'is-invalid' : '' ?>"
                                id="senha"
                                name="senha"
                                type="password"
                                autocomplete="current-password"
                                <?= isset($errors['senha']) ? 'aria-describedby="senha-error"' : '' ?>
                                aria-invalid="<?= isset($errors['senha']) ? 'true' : 'false' ?>"
                                required
                            >
                            <button
                                class="auth-password-toggle"
                                type="button"
                                data-toggle-password="#senha"
                                data-show-label="<?= htmlspecialchars(Lang::get('auth.show_password'), ENT_QUOTES, 'UTF-8') ?>"
                                data-hide-label="<?= htmlspecialchars(Lang::get('auth.hide_password'), ENT_QUOTES, 'UTF-8') ?>"
                                aria-label="<?= htmlspecialchars(Lang::get('auth.show_password'), ENT_QUOTES, 'UTF-8') ?>"
                                aria-pressed="false"
                            >
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['senha'])): ?>
                            <div id="senha-error" class="invalid-feedback d-block"><?= htmlspecialchars((string) $errors['senha'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="auth-submit" type="submit">
                        <span><?= htmlspecialchars(Lang::get('auth.sign_in'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="auth-submit-icon" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                    </button>
                </form>

                <p class="auth-switch">
                    <span><?= htmlspecialchars(Lang::get('auth.no_account'), ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="<?= Url::to('/cadastro') ?>">
                        <?= htmlspecialchars(Lang::get('auth.create_account'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </p>

                <p class="auth-privacy">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <?= htmlspecialchars(Lang::get('auth.data_private'), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </section>
    </div>
</main>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
