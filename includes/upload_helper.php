<?php
/**
 * Validasi dan simpan foto barang ke folder uploads/ secara aman.
 * Return: ['ok' => bool, 'filename' => ?string, 'error' => ?string]
 */
function uploadItemPhoto(array $file, string $targetDir): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }
 
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'Gagal mengunggah file foto.'];
    }
 
    // Batas maksimal ukuran file: 2 MB
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'filename' => null, 'error' => 'Ukuran foto maksimal 2 MB.'];
    }
 
    // Validasi tipe MIME asli dari isi file (bukan sekadar ekstensi)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
 
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
 
    if (!isset($allowedMime[$mime])) {
        return ['ok' => false, 'filename' => null, 'error' => 'Format foto harus JPG, PNG, atau WEBP.'];
    }
 
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
 
    // Rename file secara acak untuk keamanan
    $newFilename = 'item_' . bin2hex(random_bytes(12)) . '.' . $allowedMime[$mime];
    $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $newFilename;
 
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'filename' => null, 'error' => 'Gagal menyimpan file ke folder uploads.'];
    }
 
    return ['ok' => true, 'filename' => $newFilename, 'error' => null];
}
 
function deleteItemPhoto(?string $filename, string $targetDir): void
{
    if (!$filename) {
        return;
    }
    $safeName = basename($filename);
    $fullPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}
