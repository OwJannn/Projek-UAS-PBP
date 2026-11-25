<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Beranda';
$items = [];
$dbReady = true;

try {
    $stmt = $pdo->prepare(
        "SELECT i.id, i.type, i.title, i.description, i.photo, i.status, i.created_at,
                c.name AS category_name, l.name AS location_name
         FROM items i
         LEFT JOIN categories c ON i.category_id = c.id
         LEFT JOIN locations l ON i.location_id = l.id
         WHERE i.status = ?
         ORDER BY i.created_at DESC
         LIMIT 6"
    );
    $stmt->execute(['open']);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tabel items belum dibuat oleh Anggota B / SQL belum di-import.
    $dbReady = false;
}

include __DIR__ . '/includes/header.php';
?>

<section class="hero-banner p-4 p-md-5 mb-4 shadow-sm">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="display-6 fw-bold mb-2">Lost &amp; Found Kampus</h1>
            <p class="lead mb-3">
                Laporkan barang hilang atau temuan di lingkungan kampus dengan proses verifikasi klaim yang aman dan terpantau.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-light fw-semibold">
                    Cari Barang
                </a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="<?= e(base_url('items/create.php')) ?>" class="btn btn-outline-light">
                        + Lapor Barang
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('auth/register.php')) ?>" class="btn btn-outline-light">
                        Daftar Akun
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Laporan Terbaru (Status Open)</h2>
    <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
</div>

<?php if (!$dbReady): ?>
    <div class="alert alert-info">
        Database belum berisi tabel <code>items</code>. Silakan import file SQL di folder <code>database/</code> terlebih dahulu.
    </div>
<?php elseif (empty($items)): ?>
    <div class="alert alert-secondary">
        Belum ada laporan barang berstatus <strong>open</strong> saat ini.
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($items as $item): ?>
            <div class="col">
                <div class="card h-100 card-hover border-0 shadow-sm">
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?= e(base_url('uploads/' . $item['photo'])) ?>"
                             class="card-img-top item-card-img img-fluid"
                             alt="<?= e($item['title']) ?>">
                    <?php else: ?>
                        <div class="item-card-img d-flex align-items-center justify-content-center text-muted">
                            Tidak ada foto
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <?php if ($item['type'] === 'lost'): ?>
                                <span class="badge bg-danger">Hilang</span>
                            <?php else: ?>
                                <span class="badge bg-success">Ditemukan</span>
                            <?php endif; ?>
                            <?php if (!empty($item['category_name'])): ?>
                                <span class="badge bg-secondary"><?= e($item['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5 card-title"><?= e($item['title']) ?></h3>
                        <p class="card-text text-muted small flex-grow-1">
                            <?= e(mb_strimwidth($item['description'] ?? '', 0, 100, '...')) ?>
                        </p>
                        <div class="small text-muted mb-2">
                            Lokasi: <?= e($item['location_name'] ?? '-') ?>
                        </div>
                        <a href="<?= e(base_url('items/detail.php?id=' . (int)$item['id'])) ?>" class="btn btn-outline-primary btn-sm mt-auto">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
