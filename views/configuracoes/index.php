<?php
use App\Helpers\Lang;
use App\Helpers\Url;

$isDark = (int) ($settings['modo_escuro'] ?? 0) === 1;
$userNameDisplay = (string) ($user['nome'] ?? '');
$avatarInitial = $userNameDisplay !== '' ? mb_strtoupper(mb_substr($userNameDisplay, 0, 1)) : '?';
$avatarUrl = is_string($user['avatar'] ?? null) ? trim((string) $user['avatar']) : '';

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content settings-page">
            <section class="page-heading">
                <div>
                    <p class="eyebrow"><?= htmlspecialchars(Lang::get('settings.eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h1><?= htmlspecialchars(Lang::get('settings.title'), ENT_QUOTES, 'UTF-8') ?></h1>
                    <p><?= htmlspecialchars(Lang::get('settings.subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </section>

            <?php if (!empty($feedback['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($feedback['success'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($feedback['errors'])): ?>
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        <?php foreach ($feedback['errors'] as $error): ?>
                            <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= Url::to('/configuracoes') ?>" method="POST" enctype="multipart/form-data" class="settings-form mt-4" id="settings-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <section class="settings-section card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <p class="settings-section-label"><?= htmlspecialchars(Lang::get('settings.profile_label'), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 class="h4 mb-4"><?= htmlspecialchars(Lang::get('settings.profile_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <div class="row g-4 align-items-start">
                            <div class="col-md-4 col-lg-3">
                                <div class="settings-avatar-preview text-center">
                                    <?php if ($avatarUrl !== ''): ?>
                                        <img class="settings-avatar-image" id="avatar-preview" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Lang::get('settings.avatar_preview'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="settings-avatar-image avatar" id="avatar-preview"><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <p class="text-muted small mt-2 mb-0"><?= htmlspecialchars(Lang::get('settings.avatar_preview'), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-9">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="nome" class="form-label"><?= htmlspecialchars(Lang::get('settings.name'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars((string) ($user['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="120">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label"><?= htmlspecialchars(Lang::get('settings.email'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="180">
                                    </div>
                                    <div class="col-12">
                                        <label for="avatar" class="form-label"><?= htmlspecialchars(Lang::get('settings.avatar'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="file" class="form-control" id="avatar" name="avatar_arquivo" accept="image/png,image/jpeg,image/webp" data-avatar-file>
                                        <div class="form-text"><?= htmlspecialchars(Lang::get('settings.image_hint'), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if ($avatarUrl !== ''): ?>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remover_avatar" name="remover_avatar" value="1" data-avatar-remove>
                                                <label class="form-check-label" for="remover_avatar"><?= htmlspecialchars(Lang::get('settings.remove_avatar'), ENT_QUOTES, 'UTF-8') ?></label>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-section card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p class="settings-section-label"><?= htmlspecialchars(Lang::get('settings.appearance_label'), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 class="h4 mb-4"><?= htmlspecialchars(Lang::get('settings.appearance_title'), ENT_QUOTES, 'UTF-8') ?></h2>

                        <div class="mb-4">
                            <span class="form-label d-block mb-2"><?= htmlspecialchars(Lang::get('settings.display_mode'), ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="settings-theme-toggle" role="radiogroup" aria-label="<?= htmlspecialchars(Lang::get('settings.theme_aria'), ENT_QUOTES, 'UTF-8') ?>">
                                <label class="settings-theme-option">
                                    <input type="radio" name="tema_modo" value="claro" <?= !$isDark ? 'checked' : '' ?>>
                                    <span><i class="bi bi-sun"></i> <?= htmlspecialchars(Lang::get('settings.light_mode'), ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                                <label class="settings-theme-option">
                                    <input type="radio" name="tema_modo" value="escuro" <?= $isDark ? 'checked' : '' ?>>
                                    <span><i class="bi bi-moon-stars"></i> <?= htmlspecialchars(Lang::get('settings.dark_mode'), ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6 col-md-4">
                                <label for="cor_primaria" class="form-label"><?= htmlspecialchars(Lang::get('settings.primary_color'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="color" class="form-control form-control-color w-100" id="cor_primaria" name="cor_primaria" value="<?= htmlspecialchars((string) ($settings['cor_primaria'] ?? '#2563eb'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label for="cor_secundaria" class="form-label"><?= htmlspecialchars(Lang::get('settings.secondary_color'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="color" class="form-control form-control-color w-100" id="cor_secundaria" name="cor_secundaria" value="<?= htmlspecialchars((string) ($settings['cor_secundaria'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="tamanho_fonte" class="form-label"><?= htmlspecialchars(Lang::get('settings.font_size'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select class="form-select" id="tamanho_fonte" name="tamanho_fonte">
                                    <option value="pequeno" <?= ($settings['tamanho_fonte'] ?? '') === 'pequeno' ? 'selected' : '' ?>><?= htmlspecialchars(Lang::get('settings.font_size_small'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="médio" <?= ($settings['tamanho_fonte'] ?? 'médio') === 'médio' ? 'selected' : '' ?>><?= htmlspecialchars(Lang::get('settings.font_size_medium'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="grande" <?= ($settings['tamanho_fonte'] ?? '') === 'grande' ? 'selected' : '' ?>><?= htmlspecialchars(Lang::get('settings.font_size_large'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <?php $wallpaperUrl = trim((string) ($settings['papel_parede'] ?? '')); ?>
                            <label for="papel_parede" class="form-label"><?= htmlspecialchars(Lang::get('settings.wallpaper'), ENT_QUOTES, 'UTF-8') ?></label>
                            <?php if ($wallpaperUrl !== ''): ?>
                                <img id="wallpaper-preview" class="settings-wallpaper-preview mb-2" src="<?= htmlspecialchars($wallpaperUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Lang::get('settings.wallpaper_preview'), ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <input type="file" class="form-control" id="papel_parede" name="papel_parede_arquivo" accept="image/png,image/jpeg,image/webp" data-wallpaper-file>
                            <div class="form-text"><?= htmlspecialchars(Lang::get('settings.wallpaper_hint'), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if ($wallpaperUrl !== ''): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remover_papel_parede" name="remover_papel_parede" value="1">
                                    <label class="form-check-label" for="remover_papel_parede"><?= htmlspecialchars(Lang::get('settings.remove_wallpaper'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="animacoes_ativas" name="animacoes_ativas" value="1" <?= (int) ($settings['animacoes_ativas'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="animacoes_ativas"><?= htmlspecialchars(Lang::get('settings.animations'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </div>
                </section>

                <section class="settings-section card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p class="settings-section-label"><?= htmlspecialchars(Lang::get('settings.prefs_label'), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 class="h4 mb-4"><?= htmlspecialchars(Lang::get('settings.prefs_title'), ENT_QUOTES, 'UTF-8') ?></h2>

                        <div class="row g-4">
                            <div class="col-md-6 col-lg-4">
                                <label for="idioma" class="form-label"><?= htmlspecialchars(Lang::get('settings.language'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select class="form-select" id="idioma" name="idioma">
                                    <option value="pt-BR" <?= ($settings['idioma'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                                    <option value="en-US" <?= ($settings['idioma'] ?? '') === 'en-US' ? 'selected' : '' ?>>English (US)</option>
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <span class="form-label d-block"><?= htmlspecialchars(Lang::get('settings.notifications'), ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="notificacoes_lembretes_tarefas" name="notificacoes_lembretes_tarefas" value="1" <?= !empty($notifications['notificacoes_lembretes_tarefas']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notificacoes_lembretes_tarefas"><?= htmlspecialchars(Lang::get('settings.task_reminders'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notificacoes_eventos_calendario" name="notificacoes_eventos_calendario" value="1" <?= !empty($notifications['notificacoes_eventos_calendario']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notificacoes_eventos_calendario"><?= htmlspecialchars(Lang::get('settings.calendar_reminders'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <span class="form-label d-block"><?= htmlspecialchars(Lang::get('settings.dashboard_prefs'), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($widgets === []): ?>
                                    <p class="text-muted small mt-2 mb-0"><?= htmlspecialchars(Lang::get('settings.no_widgets'), ENT_QUOTES, 'UTF-8') ?></p>
                                <?php else: ?>
                                    <div class="settings-widget-list mt-2">
                                        <?php foreach ($widgets as $widget): ?>
                                            <?php
                                            $widgetId = (string) ($widget['id'] ?? '');
                                            $type = (string) ($widget['tipo_widget'] ?? '');
                                            $label = $widgetLabels[$type] ?? $type;
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="widget_<?= htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') ?>" name="widget_visivel[<?= htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') ?>]" value="1" <?= !empty($widget['visivel']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="widget_<?= htmlspecialchars($widgetId, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pb-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save"></i> <?= htmlspecialchars(Lang::get('settings.save'), ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<script>
(function () {
    const avatarFile = document.querySelector('[data-avatar-file]');
    const avatarRemove = document.querySelector('[data-avatar-remove]');
    const nameInput = document.getElementById('nome');
    const initialAvatarUrl = <?= json_encode($avatarUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const initialWallpaperUrl = <?= json_encode(trim((string) ($settings['papel_parede'] ?? '')), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const avatarPreviewAlt = <?= json_encode(Lang::get('settings.avatar_preview'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const wallpaperPreviewAlt = <?= json_encode(Lang::get('settings.wallpaper_preview'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    let avatarObjectUrl = null;

    function initialsFromName(name) {
        const trimmed = (name || '').trim();
        return trimmed ? trimmed.charAt(0).toUpperCase() : '?';
    }

    function showAvatar(src) {
        const current = document.getElementById('avatar-preview');
        if (!current) return;
        if (src) {
            if (current.tagName === 'IMG') {
                current.src = src;
            } else {
                const img = document.createElement('img');
                img.id = 'avatar-preview';
                img.className = 'settings-avatar-image';
                img.alt = avatarPreviewAlt;
                img.src = src;
                current.replaceWith(img);
            }
            return;
        }

        const initial = initialsFromName(nameInput ? nameInput.value : '');
        if (current.tagName !== 'SPAN') {
            const span = document.createElement('span');
            span.id = 'avatar-preview';
            span.className = 'settings-avatar-image avatar';
            span.textContent = initial;
            current.replaceWith(span);
        } else {
            current.textContent = initial;
        }
    }

    if (avatarFile) {
        avatarFile.addEventListener('change', function () {
            if (avatarObjectUrl) {
                URL.revokeObjectURL(avatarObjectUrl);
                avatarObjectUrl = null;
            }
            const file = avatarFile.files && avatarFile.files[0];
            if (file) {
                if (avatarRemove) avatarRemove.checked = false;
                avatarObjectUrl = URL.createObjectURL(file);
                showAvatar(avatarObjectUrl);
            } else {
                showAvatar(initialAvatarUrl || null);
            }
        });
    }

    if (avatarRemove) {
        avatarRemove.addEventListener('change', function () {
            if (avatarRemove.checked) {
                if (avatarFile) avatarFile.value = '';
                showAvatar(null);
            } else {
                showAvatar(initialAvatarUrl || null);
            }
        });
    }

    if (nameInput && !initialAvatarUrl) {
        nameInput.addEventListener('input', function () {
            const current = document.getElementById('avatar-preview');
            if (current && current.tagName === 'SPAN') {
                current.textContent = initialsFromName(nameInput.value);
            }
        });
    }

    const wallpaperFile = document.querySelector('[data-wallpaper-file]');
    if (wallpaperFile) {
        wallpaperFile.addEventListener('change', function () {
            const file = wallpaperFile.files && wallpaperFile.files[0];
            let el = document.getElementById('wallpaper-preview');
            const removeWallpaper = document.getElementById('remover_papel_parede');

            if (file) {
                if (removeWallpaper) removeWallpaper.checked = false;
                if (!el) {
                    el = document.createElement('img');
                    el.id = 'wallpaper-preview';
                    el.className = 'settings-wallpaper-preview mb-2';
                    el.alt = wallpaperPreviewAlt;
                    wallpaperFile.parentElement.insertBefore(el, wallpaperFile);
                }
                el.src = URL.createObjectURL(file);
            } else if (el && initialWallpaperUrl) {
                el.src = initialWallpaperUrl;
            } else if (el && !initialWallpaperUrl) {
                el.remove();
            }
        });
    }
})();
</script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
