<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil di halaman yang mewajibkan user login
function wajibLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        // Sesuaikan '/KelompokForum/' jika nama folder di htdocs berbeda
        header("Location: /Projek-UAS-PBP/auth/login.php");
        exit;
    }
}

// Panggil di halaman yang khusus diakses oleh role admin
function wajibAdmin(): void {
    wajibLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die("Akses ditolak: Halaman ini khusus Admin.");
    }
}

// Helper untuk mencegah serangan XSS saat menampilkan data user ke HTML
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>