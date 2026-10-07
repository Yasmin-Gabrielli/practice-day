<?php
use App\Helpers\Url;

$errors = $feedback['errors'] ?? [];
$old = $feedback['old'] ?? [];
$selectedIcon = $old['icone'] ?? '';
$selectedColor = $old['cor'] ?? '#2563eb';
require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <section class="page-heading disciplines-heading">
                <div><p class="eyebrow">Organização</p><h1>Disciplinas</h1><p>Organize seus materiais, tarefas e notas por área de estudo.</p></div>
                <button class="btn btn-primary d-none d-sm-inline-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#new-discipline"><i class="bi bi-plus-lg"></i>Nova disciplina</button>
            </section>
            <?php if (isset($feedback['success'])): ?><div class="alert alert-success border-0 shadow-sm"><?= htmlspecialchars((string) $feedback['success'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($errors !== []): ?><div class="alert alert-danger border-0">Revise os campos da nova disciplina.</div><?php endif; ?>

            <?php if ($disciplines === []): ?>
                <section class="empty-module-state"><span><i class="bi bi-mortarboard"></i></span><h2>Você ainda não possui disciplinas.</h2><p>Crie sua primeira disciplina para começar a organizar seus estudos.</p><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#new-discipline">Criar disciplina</button></section>
            <?php else: ?>
                <section class="discipline-cards">
                    <?php foreach ($disciplines as $discipline): ?>
                        <?php $color = is_string($discipline['cor']) && preg_match('/^#[0-9a-fA-F]{6}$/', $discipline['cor']) ? $discipline['cor'] : '#2563eb'; ?>
                        <a class="discipline-card" href="<?= Url::to('/disciplinas/' . rawurlencode((string) $discipline['id'])) ?>">
                            <span class="discipline-card-icon" style="--discipline-color: <?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= htmlspecialchars(\App\Validators\DisciplineValidator::safeIcon((string) $discipline['icone']), ENT_QUOTES, 'UTF-8') ?>"></i></span>
                            <div class="discipline-card-title"><h2><?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?></h2><span class="discipline-color-label"><i style="background: <?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>"></i><?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="discipline-card-stats"><span><strong><?= (int) $discipline['arquivos_count'] ?></strong> arquivos</span><span><strong><?= (int) $discipline['tarefas_count'] ?></strong> tarefas</span><span><strong><?= (int) $discipline['notas_count'] ?></strong> notas</span></div>
                            <i class="bi bi-arrow-up-right discipline-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>
<button class="quick-action d-lg-none" type="button" data-bs-toggle="modal" data-bs-target="#new-discipline" aria-label="Nova disciplina"><i class="bi bi-plus-lg"></i></button>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>

<div class="modal fade" id="new-discipline" tabindex="-1" aria-labelledby="new-discipline-title"<?= $errors !== [] ? ' data-open-on-load="true"' : '' ?>><div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0 rounded-4" method="post" action="<?= Url::to('/disciplinas') ?>"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><div class="modal-header border-0 pb-0"><div><h2 class="modal-title fs-5" id="new-discipline-title">Nova disciplina</h2><p class="text-secondary small mb-0 mt-1">Defina como ela aparecerá no seu espaço.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body pt-4"><div class="mb-3"><label class="form-label" for="nome">Nome</label><input class="form-control <?= isset($errors['nome']) ? 'is-invalid' : '' ?>" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars((string) ($old['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required><?php if (isset($errors['nome'])): ?><div class="invalid-feedback"><?= htmlspecialchars((string) $errors['nome'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div><div class="row g-3"><div class="col-sm-5"><label class="form-label" for="cor">Cor</label><input class="form-control form-control-color w-100 <?= isset($errors['cor']) ? 'is-invalid' : '' ?>" id="cor" name="cor" type="color" value="<?= htmlspecialchars((string) $selectedColor, ENT_QUOTES, 'UTF-8') ?>" required><?php if (isset($errors['cor'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars((string) $errors['cor'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div><div class="col-sm-7"><label class="form-label" for="icone">Ícone</label><input type="hidden" name="icone" id="icone" value="<?= htmlspecialchars($selectedIcon, ENT_QUOTES, 'UTF-8') ?>"><div class="icon-picker" data-icon-picker="icone"><?php foreach ($icons as $icon): ?><button type="button" class="icon-picker-item" data-icon="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i></button><?php endforeach; ?></div><?php if (isset($errors['icone'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars((string) $errors['icone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div></div></div><div class="modal-footer border-0 pt-0"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Criar disciplina</button></div></form></div></div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
