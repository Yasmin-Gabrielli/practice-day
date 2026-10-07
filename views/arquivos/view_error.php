<?php
use App\Helpers\Url;

require dirname(__DIR__) . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <a class="back-link" href="<?= htmlspecialchars($backUrl ?? Url::to('/arquivos'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-arrow-left"></i>Voltar para Meus Arquivos
            </a>
            <section class="empty-state-card mt-3">
                <span class="empty-state-icon"><i class="bi bi-file-earmark-x"></i></span>
                <div>
                    <h2>Não foi possível abrir o arquivo</h2>
                    <p><?= htmlspecialchars((string) ($message ?? 'Arquivo indisponível.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </section>
        </main>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
