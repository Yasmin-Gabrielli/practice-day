<?php
use App\Helpers\UserTheme;
?>
<!doctype html>
<html <?= UserTheme::htmlAttributes() ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'PracticeDay', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= \App\Helpers\Url::asset('assets/css/app.css') ?>" rel="stylesheet">
    <?php
    $settings = \App\Helpers\UserTheme::settings();
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['cor_primaria'] ?? '')) ? (string) $settings['cor_primaria'] : '#2563eb';
    $secondary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['cor_secundaria'] ?? '')) ? (string) $settings['cor_secundaria'] : '#ffffff';
    $primaryRgb = implode(', ', [hexdec(substr($primary, 1, 2)), hexdec(substr($primary, 3, 2)), hexdec(substr($primary, 5, 2))]);
    ?>
    <style>
        :root, [data-bs-theme="dark"], [data-bs-theme="light"] {
            --bs-primary: <?= $primary ?> !important;
            --bs-primary-rgb: <?= $primaryRgb ?> !important;
            --app-accent-secondary: <?= $secondary ?> !important;
        }
    </style>
</head>
<body class="<?= htmlspecialchars(implode(' ', UserTheme::bodyClasses()), ENT_QUOTES, 'UTF-8') ?>" style="<?= UserTheme::bodyStyle() ?>">
