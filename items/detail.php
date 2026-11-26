<?php
require_once __DIR__ . '/../includes/init.php';

$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = null;

if ($itemId) {
    $stmt = $pdo->prepare(
        "SELECT i.id, i.user_id, i.type, i.title, i.description, i.photo, i.status, i.created_at,
                c.name AS category_name, l.name AS location_name, u.nama AS reporter_name
         FROM items i
         JOIN categories c ON c.id = i.category_id
         JOIN locations l ON l.id = i.location_id
         JOIN users u ON u.id = i.user_id
         WHERE i.id = ?"
    );
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
}

if (!$item) {
    http_response_code(404);
    $pageTitle = 'Barang tidak ditemukan';
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="alert alert-warning">Barang yang dicari tidak ditemukan.</div>
    <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-outline-primary">Kembali ke daftar barang</a>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, question FROM verification_questions WHERE item_id = ? ORDER BY id"
);
$stmt->execute([$itemId]);
$questions = $stmt->fetchAll();
$errors = [];
$answers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajibLogin();
    verifyCsrfToken();

    if ($item['status'] !== 'open') {
        $errors[] = 'Barang ini tidak lagi tersedia untuk diklaim.';
    } elseif ((int) $item['user_id'] === (int) $_SESSION['user_id']) {
        $errors[] = 'Anda tidak dapat mengklaim barang sendiri.';
    } elseif (empty($questions)) {
        $errors[] = 'Pertanyaan verifikasi untuk barang ini belum tersedia.';
    }

    $claimAnswers = [];
    foreach ($questions as $question) {
        $answer = $answers[$question['id']] ?? '';
        $answer = is_string($answer) ? trim($answer) : '';
        if ($answer === '') {
            $errors[] = 'Semua pertanyaan harus dijawab.';
            break;
        }
        $claimAnswers[(int) $question['id']] = $answer;
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO claims (item_id, user_id, status) VALUES (?, ?, 'pending')"
            );
            $stmt->execute([$itemId, $_SESSION['user_id']]);
            $claimId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                "INSERT INTO claim_answers (claim_id, question_id, answer) VALUES (?, ?, ?)"
            );
            foreach ($claimAnswers as $questionId => $answer) {
                $stmt->execute([$claimId, $questionId, $answer]);
            }

            $stmt = $pdo->prepare(
                "UPDATE items SET status = 'claim_pending' WHERE id = ? AND status = 'open'"
            );
            $stmt->execute([$itemId]);
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('Barang ini tidak lagi tersedia untuk diklaim.');
            }

            $pdo->commit();
            set_flash('success', 'Klaim berhasil dikirim dan menunggu verifikasi.');
            header('Location: ' . base_url('claims/my_claims.php'));
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}

$pageTitle = $item['title'];
$statuses = [
    'open' => ['Tersedia', 'success'],
    'claim_pending' => ['Menunggu verifikasi', 'warning'],
    'returned' => ['Dikembalikan', 'primary'],
    'closed' => ['Ditutup', 'secondary'],
];
[$statusLabel, $statusColor] = $statuses[$item['status']] ?? [$item['status'], 'secondary'];
include __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <?php if (!empty($item['photo'])): ?>
                <img src="<?= e(base_url('uploads/' . $item['photo'])) ?>"
                     class="card-img-top item-card-img" alt="<?= e($item['title']) ?>">
            <?php endif; ?>
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge <?= $item['type'] === 'lost' ? 'bg-danger' : 'bg-success' ?>">
                        <?= $item['type'] === 'lost' ? 'Hilang' : 'Ditemukan' ?>
                    </span>
                    <span class="badge bg-secondary"><?= e($item['category_name']) ?></span>
                    <span class="badge bg-<?= e($statusColor) ?>"><?= e($statusLabel) ?></span>
                </div>
                <h1 class="h3 fw-bold"><?= e($item['title']) ?></h1>
                <p class="text-muted small">
                    <?= e($item['location_name']) ?> &middot;
                    Dilaporkan oleh <?= e($item['reporter_name']) ?> pada
                    <?= e(date('d/m/Y', strtotime($item['created_at']))) ?>
                </p>
                <p class="mb-0"><?= nl2br(e($item['description'])) ?></p>
            </div>
        </div>
        <a href="<?= e(base_url('items/index.php')) ?>" class="btn btn-link px-0 mt-2">
            &larr; Kembali ke daftar barang
        </a>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold">Klaim barang ini</h2>
                <p class="small text-muted">Jawab pertanyaan verifikasi. Klaim akan menunggu pemeriksaan.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach (array_unique($errors) as $error): ?>
                            <div><?= e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($item['status'] !== 'open'): ?>
                    <div class="alert alert-secondary mb-0">Barang ini sedang menunggu verifikasi atau sudah tidak tersedia.</div>
                <?php elseif (!empty($_SESSION['user_id']) && (int) $item['user_id'] === (int) $_SESSION['user_id']): ?>
                    <div class="alert alert-info mb-0">Ini adalah laporan yang Anda buat.</div>
                <?php elseif (empty($_SESSION['user_id'])): ?>
                    <a href="<?= e(base_url('auth/login.php')) ?>" class="btn btn-primary">Login untuk mengklaim</a>
                <?php elseif (empty($questions)): ?>
                    <div class="alert alert-warning mb-0">Pertanyaan verifikasi belum tersedia.</div>
                <?php else: ?>
                    <form method="post" action="<?= e(base_url('items/detail.php?id=' . (int) $item['id'])) ?>">
                        <?= csrfInput() ?>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-3">
                                <label for="answer-<?= (int) $question['id'] ?>" class="form-label">
                                    <?= $index + 1 ?>. <?= e($question['question']) ?>
                                </label>
                                <textarea id="answer-<?= (int) $question['id'] ?>"
                                          name="answers[<?= (int) $question['id'] ?>]"
                                          rows="2" maxlength="1000" class="form-control" required><?= e($answers[$question['id']] ?? '') ?></textarea>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary w-100">Kirim Klaim</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
