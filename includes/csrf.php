<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Membuat token CSRF acak jika belum ada di session
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Mencetak input hidden untuk diselipkan di dalam tag <form>
function csrfInput(): string {
    $token = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Memvalidasi token saat form POST dikirim
function verifyCsrfToken(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die("Akses ditolak: Token CSRF tidak valid atau sesi kedaluwarsa.");
        }
    }
}
?>