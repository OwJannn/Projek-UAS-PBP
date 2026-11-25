<?php
require_once '../config/db.php';
require_once '../includes/csrf.php';
require_once '../includes/auth_check.php';

// Jika sudah login, tidak perlu daftar lagi
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nim_nip = trim($_POST['nim_nip'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    // Validasi sisi server
    if ($nama === '' || $email === '' || $password === '') {
        $errors[] = "Nama, email, dan password wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek apakah email sudah terdaftar (Prepared Statement)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
        }
    }

    // Simpan data user baru ke database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $nimValue = ($nim_nip === '') ? null : $nim_nip;

        $stmt = $pdo->prepare("INSERT INTO users (nama, email, nim_nip, password, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->execute([$nama, $email, $nimValue, $hashedPassword]);

        $success = "Registrasi berhasil! Silakan login menggunakan akun barumu.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lost & Found Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="mb-1 text-center fw-bold">Daftar Akun</h4>
                    <p class="text-muted text-center small mb-4">Lost & Found Kampus</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3 small">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success small">
                            <?= e($success) ?> <a href="login.php" class="fw-bold">Login di sini</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrfInput() ?>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= e($_POST['nama'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="nama@kampus.ac.id" value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIM / NIP (Opsional)</label>
                            <input type="text" name="nim_nip" class="form-control" value="<?= e($_POST['nim_nip'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                            <div class="form-text">Minimal 6 karakter.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="konfirmasi_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    </form>
                    <p class="text-center mt-3 mb-0 small">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>