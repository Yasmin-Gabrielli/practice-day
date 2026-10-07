<?php
use App\Helpers\Lang;

$errors = $feedback['errors'] ?? [];
$old = $feedback['old'] ?? [];
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="container py-4 py-md-5"><div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <section class="card auth-card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <p class="text-primary fw-semibold text-uppercase small mb-2">PracticeDay</p>
                <h1 class="h2 mb-2"><?= htmlspecialchars(Lang::get('auth.register_title'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-secondary mb-4"><?= htmlspecialchars(Lang::get('auth.register_subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (isset($errors['formulario'])): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $errors['formulario'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

                <form method="post" action="cadastro" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label" for="nome"><?= htmlspecialchars(Lang::get('auth.name'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['nome']) ? 'is-invalid' : '' ?>" id="nome" name="nome" type="text" autocomplete="name" maxlength="120" value="<?= htmlspecialchars((string) ($old['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errors['nome'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['nome'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email"><?= htmlspecialchars(Lang::get('auth.email'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" autocomplete="email" maxlength="180" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="senha"><?= htmlspecialchars(Lang::get('auth.password'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['senha']) ? 'is-invalid' : '' ?>" id="senha" name="senha" type="password" autocomplete="new-password" required>
                        <div class="form-text"><?= htmlspecialchars(Lang::get('auth.password_hint'), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (isset($errors['senha'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['senha'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="confirmacao_senha"><?= htmlspecialchars(Lang::get('auth.confirm_password'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input class="form-control <?= isset($errors['confirmacao_senha']) ? 'is-invalid' : '' ?>" id="confirmacao_senha" name="confirmacao_senha" type="password" autocomplete="new-password" required>
                        <?php if (isset($errors['confirmacao_senha'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['confirmacao_senha'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-primary w-100 py-2" type="submit"><?= htmlspecialchars(Lang::get('auth.create_account'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
                <p class="text-center text-secondary small mt-4 mb-0"><?= htmlspecialchars(Lang::get('auth.has_account'), ENT_QUOTES, 'UTF-8') ?> <a href="login"><?= htmlspecialchars(Lang::get('auth.sign_in'), ENT_QUOTES, 'UTF-8') ?></a></p>
            </div>
        </section>
    </div>
</div></div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
