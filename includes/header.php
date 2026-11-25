<?php
$pageTitle = $pageTitle ?? 'Lost & Found Kampus';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Lost &amp; Found Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include __DIR__ . '/navbar.php'; ?>
    <main class="container py-4 flex-grow-1">
        <?php include __DIR__ . '/components/alert.php'; ?>
