<?php
use App\Helpers\Lang;

$errors = $feedback['errors'] ?? [];
$old = $feedback['old'] ?? [];
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="container py-4 py-md-5"><div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <section class="card auth-card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <p class="text-primary fw-semibold text-uppercase small mb-2">PracticeDay</p>
                <h1 class="h2 mb-2"><?= htmlspecialchars(Lang::get('auth.login_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-secondary mb-4"><?= htmlspecialchars(Lang::get('auth.login_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>

                <?php if (isset($feedback['success'])): ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars((string) $feedback['success'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (isset($errors['credenciais'])): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $errors['credenciais'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="login" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label" for="email"><?= htmlspecialchars(Lang::get('auth.email'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" autocomplete="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="senha"><?= htmlspecialchars(Lang::get('auth.password'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['senha']) ? 'is-invalid' : '' ?>" id="senha" name="senha" type="password" autocomplete="current-password" required>
                        <?php if (isset($errors['senha'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['senha'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-primary w-100 py-2" type="submit"><?= htmlspecialchars(Lang::get('auth.sign_in'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
                <p class="text-center text-secondary small mt-4 mb-0"><?= htmlspecialchars(Lang::get('auth.no_account'), ENT_QUOTES, 'UTF-8') ?> <a href="cadastro"><?= htmlspecialchars(Lang::get('auth.create_account'), ENT_QUOTES, 'UTF-8') ?></a></p>
            </div>
        </section>
    </div>
</div></div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
