<?php
/**
 * @var string $slot          rendered page content
 * @var string $title         page <title>
 * @var string $bodyClass     optional extra body classes
 * @var array  $pageScripts   optional list of extra script src paths
 */
$title = $title ?? config('app.name');
$bodyClass = $bodyClass ?? '';
$pageScripts = $pageScripts ?? [];
?>
<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="app-base" content="<?= e(config('app.base_path', '')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title) ?></title>
    <meta name="description" content="LifeLink connects blood donors with patients and blood banks during emergencies.">

    <link rel="icon" href="<?= asset('favicon.svg') ?>" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="min-h-full bg-gray-50 text-gray-800 <?= e($bodyClass) ?>">
    <?= $slot ?>

    <script src="<?= asset('js/vendor/jquery.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php foreach ($pageScripts as $src): ?>
        <script src="<?= e($src) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
