<?php
require_once __DIR__ . '/../includes/init.php';
wajibLogin();

$pageTitle = 'Klaim Saya';
$stmt = $pdo->prepare(
    "SELECT c.id, c.status AS claim_status, c.created_at,
            i.id AS item_id, i.title, i.type
     FROM claims c
     JOIN items i ON i.id = c.item_id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$claims = $stmt->fetchAll();

$statusLabels = [
    'pending' => ['Menunggu verifikasi', 'warning'],
    'approved' => ['Disetujui', 'success'],
    'rejected' => ['Ditolak', 'danger'],
];

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Klaim Saya</h1>
    <p class="text-muted mb-0">Lihat status barang yang Anda klaim.</p>
</div>

<?php if (empty($claims)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-3">Anda belum mengirim klaim barang.</p>
            <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-primary">Cari barang</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($claims as $claim): ?>
            <?php [$label, $badge] = $statusLabels[$claim['claim_status']] ?? ['Status tidak diketahui', 'secondary']; ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                <h2 class="h6 fw-bold mb-0"><?= e($claim['title']) ?></h2>
                                <span class="badge <?= $claim['type'] === 'lost' ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $claim['type'] === 'lost' ? 'Hilang' : 'Ditemukan' ?>
                                </span>
                                <span class="badge bg-<?= e($badge) ?>"><?= e($label) ?></span>
                            </div>
                            <div class="small text-muted">
                                Dikirim <?= e(date('d/m/Y H:i', strtotime($claim['created_at']))) ?>
                            </div>
                        </div>
                        <a href="<?= e(base_url('items/detail.php?id=' . (int) $claim['item_id'])) ?>" class="btn btn-outline-primary btn-sm">Lihat barang</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
