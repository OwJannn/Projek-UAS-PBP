<?php
require_once __DIR__ . '/../includes/init.php';
 
$pageTitle = 'Daftar Barang';
 
// Query daftar barang versi awal (belum ada pencarian & filter)
$sql = "SELECT i.id, i.type, i.title, i.status, i.created_at,
               c.name AS category_name, l.name AS location_name, u.nama AS reporter_name
        FROM items i
        JOIN categories c ON i.category_id = c.id
        JOIN locations l ON i.location_id = l.id
        JOIN users u ON i.user_id = u.id
        ORDER BY i.created_at DESC";
 
$items = $pdo->query($sql)->fetchAll();
 
include __DIR__ . '/../includes/header.php';
?>
 
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-0">Daftar Barang Hilang &amp; Ditemukan</h1>
        <small class="text-muted">Tahap 2: Tampilan daftar tabel dasar</small>
    </div>
    <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= e(base_url('items/create.php')) ?>" class="btn btn-primary">+ Lapor Barang</a>
    <?php endif; ?>
</div>
 
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Judul Barang</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Pelapor</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        Belum ada postingan barang.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($item['title']) ?></td>
                        <td>
                            <?php if ($item['type'] === 'lost'): ?>
                                <span class="badge bg-danger">Hilang</span>
                            <?php else: ?>
                                <span class="badge bg-success">Ditemukan</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($item['category_name']) ?></td>
                        <td><?= e($item['location_name']) ?></td>
                        <td>
                            <?php
                            $itemStatuses = [
                                'open' => ['Tersedia', 'success'],
                                'claim_pending' => ['Menunggu verifikasi', 'warning'],
                                'returned' => ['Dikembalikan', 'primary'],
                                'closed' => ['Ditutup', 'secondary'],
                            ];
                            [$statusLabel, $statusColor] = $itemStatuses[$item['status']] ?? [$item['status'], 'secondary'];
                            ?>
                            <span class="badge bg-<?= e($statusColor) ?>"><?= e($statusLabel) ?></span>
                        </td>
                        <td><?= e($item['reporter_name']) ?></td>
                        <td class="small text-muted"><?= e(date('d/m/Y', strtotime($item['created_at']))) ?></td>
                        <td class="text-end">
                            <a href="<?= e(base_url('items/detail.php?id=' . (int) $item['id'])) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
 
<?php include __DIR__ . '/../includes/footer.php'; ?>
