<?php
// Helper tambahan untuk layout & flash message (melengkapi auth_check.php dan csrf.php milik Anggota A).

function base_url(string $path = ''): string
{
    $base = '';
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if (preg_match('#^(.+?)/(auth|items|claims|admin)(/.*)?$#', $dir, $m)) {
            $base = $m[1];
        } elseif (
            preg_match('#^(/[^/]+)$#', $dir)
            && !empty($_SERVER['DOCUMENT_ROOT'])
            && file_exists(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $dir . '/includes/init.php')
        ) {
            $base = $dir;
        }
    }

    $path = ltrim($path, '/');
    return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
