<?php
    use App\Helpers\UserTheme;
?>
<!doctype html>
<html <?php echo UserTheme::htmlAttributes() ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title ?? 'PracticeDay', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo \App\Helpers\Url::asset('assets/images/logos-practice-day/logo-practice-day.ico') ?>">
    <link rel="shortcut icon" href="<?php echo \App\Helpers\Url::asset('assets/images/logos-practice-day/logo-practice-day.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo \App\Helpers\Url::asset('assets/css/app.css') ?>" rel="stylesheet">
    <link href="<?php echo \App\Helpers\Url::asset('assets/css/theme.css') ?>" rel="stylesheet">
    <?php
        $settings    = \App\Helpers\UserTheme::settings();
        $primary     = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['cor_primaria'] ?? '')) ? (string) $settings['cor_primaria'] : '#2563eb';
        $secondary   = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['cor_secundaria'] ?? '')) ? (string) $settings['cor_secundaria'] : '#ffffff';
        $primaryRgb  = implode(', ', [hexdec(substr($primary, 1, 2)), hexdec(substr($primary, 3, 2)), hexdec(substr($primary, 5, 2))]);
        $primaryText = \App\Helpers\UserTheme::primaryTextColor($primary);
    ?>
    <style>
        :root, [data-bs-theme="dark"], [data-bs-theme="light"] {
            --bs-primary: <?php echo $primary ?> !important;
            --bs-primary-rgb: <?php echo $primaryRgb ?> !important;
            --app-accent-secondary: <?php echo $secondary ?> !important;
            --bs-primary-text: <?php echo $primaryText ?> !important;
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars(implode(' ', UserTheme::bodyClasses()), ENT_QUOTES, 'UTF-8') ?>" style="<?php echo UserTheme::bodyStyle() ?>">
