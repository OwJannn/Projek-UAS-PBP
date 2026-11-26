# Projek-UAS-PBP

## Database

Import file SQL ke database MySQL/MariaDB dengan urutan berikut:

1. `database/tabel_auth.sql` untuk tabel pengguna dan log klaim.
2. `database/002_items.sql` untuk tabel barang, kategori, dan lokasi.
3. `database/003_claims.sql` untuk klaim, pertanyaan verifikasi, dan jawaban.
4. `database/seed.sql` untuk data kategori dan lokasi.

## Klaim dan verifikasi

Setiap laporan barang menyimpan dua pertanyaan beserta jawaban rahasia. Pengguna terdaftar dapat menjawab pertanyaan pada halaman detail barang. Klaim yang dikirim akan berstatus menunggu verifikasi.