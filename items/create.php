<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/upload_helper.php';
wajibLogin();
 
$pageTitle = 'Lapor Barang';
 
// Ambil data master kategori & lokasi dari seed.sql
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$locations  = $pdo->query("SELECT id, name FROM locations ORDER BY name ASC")->fetchAll();
 
$errors = [];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
 
    $title        = trim($_POST['title'] ?? '');
    $type         = $_POST['type'] ?? '';
    $categoryId   = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $locationId   = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT);
    $description  = trim($_POST['description'] ?? '');
    $secretDetail = trim($_POST['secret_detail'] ?? '');
    $questions    = is_array($_POST['questions'] ?? null) ? $_POST['questions'] : [];
    $answers      = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
 
    // Validasi sisi server
    if ($title === '' || strlen($title) > 150) {
        $errors[] = "Judul barang wajib diisi (maksimal 150 karakter).";
    }
    if (!in_array($type, ['lost', 'found'], true)) {
        $errors[] = "Tipe laporan harus Hilang (lost) atau Ditemukan (found).";
    }
    if (!$categoryId) {
        $errors[] = "Kategori wajib dipilih.";
    }
    if (!$locationId) {
        $errors[] = "Lokasi wajib dipilih.";
    }
    if ($description === '') {
        $errors[] = "Deskripsi barang wajib diisi.";
    }
    for ($i = 0; $i < 2; $i++) {
        $questionValue = $questions[$i] ?? '';
        $answerValue = $answers[$i] ?? '';
        $question = is_string($questionValue) ? trim($questionValue) : '';
        $answer = is_string($answerValue) ? trim($answerValue) : '';
        if ($question === '' || mb_strlen($question) > 255) {
            $errors[] = "Pertanyaan verifikasi " . ($i + 1) . " wajib diisi (maksimal 255 karakter).";
        }
        if ($answer === '' || mb_strlen($answer) > 255) {
            $errors[] = "Jawaban rahasia " . ($i + 1) . " wajib diisi (maksimal 255 karakter).";
        }
    }
 
    // Pastikan ID kategori & lokasi benar-benar ada di database
    if (empty($errors)) {
        $cekCat = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $cekCat->execute([$categoryId]);
        if (!$cekCat->fetch()) {
            $errors[] = "Kategori tidak ditemukan.";
        }
 
        $cekLoc = $pdo->prepare("SELECT id FROM locations WHERE id = ?");
        $cekLoc->execute([$locationId]);
        if (!$cekLoc->fetch()) {
            $errors[] = "Lokasi tidak ditemukan.";
        }
    }

    $photoFilename = null;
    if (empty($errors)) {
        $upload = uploadItemPhoto($_FILES['photo'] ?? [], __DIR__ . '/../uploads');
        if (!$upload['ok']) {
            $errors[] = $upload['error'];
        } else {
            $photoFilename = $upload['filename'];
        }
    }

 
    // Simpan postingan dengan status awal 'open' (sesuai query di index.php)
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO items
                (user_id, category_id, location_id, type, title, description, secret_detail, photo, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')"
            );
            $stmt->execute([
                $_SESSION['user_id'],
                $categoryId,
                $locationId,
                $type,
                $title,
                $description,
                $secretDetail !== '' ? $secretDetail : null,
                $photoFilename,
            ]);

            $itemId = (int) $pdo->lastInsertId();
            $questionStmt = $pdo->prepare(
                "INSERT INTO verification_questions (item_id, question, answer) VALUES (?, ?, ?)"
            );
            for ($i = 0; $i < 2; $i++) {
                $questionStmt->execute([
                    $itemId,
                    trim((string) $questions[$i]),
                    trim((string) $answers[$i]),
                ]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
 
        set_flash('success', 'Laporan barang berhasil diposting!');
        header('Location: ' . base_url('items/index.php'));
        exit;
    }
}
 
include __DIR__ . '/../includes/header.php';
?>
 
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 fw-bold mb-1">Lapor Barang Hilang / Ditemukan</h1>
                <p class="text-muted small mb-4">Lengkapi informasi barang dengan jelas.</p>
 
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
 
                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfInput() ?>
 
                    <div class="mb-3">
                        <label class="form-label">Judul Barang <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" maxlength="150"
                               placeholder="Contoh: Dompet Kulit Hitam / Kunci Motor Honda"
                               value="<?= e($_POST['title'] ?? '') ?>" required>
                    </div>
 
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipe Laporan <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="lost" <?= ($_POST['type'] ?? '') === 'lost' ? 'selected' : '' ?>>
                                    Barang Hilang
                                </option>
                                <option value="found" <?= ($_POST['type'] ?? '') === 'found' ? 'selected' : '' ?>>
                                    Barang Ditemukan
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"
                                        <?= (string)($_POST['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lokasi Kampus <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= (int)$loc['id'] ?>"
                                        <?= (string)($_POST['location_id'] ?? '') === (string)$loc['id'] ? 'selected' : '' ?>>
                                        <?= e($loc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
 
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Publik <span class="text-danger">*</span></label>
                        <textarea name="description" rows="3" class="form-control"
                                  placeholder="Jelaskan ciri umum dan waktu/tempat kejadian..." required><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>
 
                    <div class="mb-4">
                        <label class="form-label">Detail Rahasia (Opsional / Untuk Verifikasi Klaim)</label>
                        <textarea name="secret_detail" rows="2" class="form-control"
                                  placeholder="Ciri khusus yang TIDAK tampil di publik (misal: isi dompet, stiker khusus)."><?= e($_POST['secret_detail'] ?? '') ?></textarea>
                        <div class="form-text">Disembunyikan dari halaman publik untuk mencegah klaim asal tebak.</div>
                    </div>

                    <div class="border rounded-3 p-3 p-md-4 mb-4 bg-light">
                        <h2 class="h6 fw-bold mb-1">Pertanyaan Verifikasi</h2>
                        <p class="small text-muted mb-3">Buat 2 pertanyaan isian bebas. Pertanyaan tampil pada halaman barang, sedangkan jawabannya tidak ditampilkan ke publik.</p>
                        <?php for ($i = 0; $i < 2; $i++): ?>
                            <div class="<?= $i === 0 ? 'mb-3' : '' ?>">
                                <label class="form-label">Pertanyaan <?= $i + 1 ?> <span class="text-danger">*</span></label>
                                <input type="text" name="questions[<?= $i ?>]" class="form-control" maxlength="255"
                                       placeholder="Contoh: Apa warna gantungan kuncinya?"
                                       value="<?= e($questions[$i] ?? '') ?>" required>
                                <label class="form-label mt-2">Jawaban rahasia <span class="text-danger">*</span></label>
                                <input type="text" name="answers[<?= $i ?>]" class="form-control" maxlength="255"
                                       placeholder="Jawaban ini tidak ditampilkan ke publik"
                                       value="<?= e($answers[$i] ?? '') ?>" required>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="mb-3">
                            <label class="form-label">Foto Barang (Opsional, JPG/PNG/WEBP maks 2MB)</label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
 
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                        <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
 
<?php include __DIR__ . '/../includes/footer.php'; ?>
