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
</head>
<body class="<?= htmlspecialchars(implode(' ', UserTheme::bodyClasses()), ENT_QUOTES, 'UTF-8') ?>" style="<?= UserTheme::bodyStyle() ?>">
