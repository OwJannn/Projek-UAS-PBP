<?php
require_once '../includes/auth_check.php';
wajibAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="alert alert-primary">
        Selamat datang di Panel Admin, <strong><?= e($_SESSION['nama']) ?></strong>!
    </div>
    <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
</body>
</html>