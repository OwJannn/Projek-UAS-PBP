<?php
require_once 'includes/auth_check.php';
wajibLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="alert alert-success">
        Halo, <strong><?= e($_SESSION['nama']) ?></strong>! Role kamu: <strong><?= e($_SESSION['role']) ?></strong>.
    </div>
    <a href="auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
</body>
</html>