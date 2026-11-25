<?php
$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin    = ($isLoggedIn && ($_SESSION['role'] ?? '') === 'admin');
$userName   = $_SESSION['nama'] ?? $_SESSION['user_name'] ?? 'Pengguna';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(base_url('index.php')) ?>">
            Lost &amp; Found Kampus
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(base_url('index.php')) ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(base_url('items/index.php')) ?>">Daftar Barang</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('items/create.php')) ?>">Lapor Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('claims/my_claims.php')) ?>">Klaim Saya</a>
                    </li>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-warning" href="<?= e(base_url('admin/index.php')) ?>">Panel Admin</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Halo, <?= e($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= e(base_url('auth/profile.php')) ?>">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= e(base_url('auth/logout.php')) ?>">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(base_url('auth/login.php')) ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-lg-2 mt-2 mt-lg-0" href="<?= e(base_url('auth/register.php')) ?>">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
