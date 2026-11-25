-- Data master kategori & lokasi (aman di-import berulang kali dengan INSERT IGNORE).
-- Data dummy users, items, dan verification_questions dihapus agar tidak bentrok dengan akun/data tes anggota lain.

INSERT IGNORE INTO categories (id, name) VALUES
(1, 'Elektronik'),
(2, 'Dokumen & Kartu'),
(3, 'Tas & Dompet'),
(4, 'Kunci'),
(5, 'Lainnya');

INSERT IGNORE INTO locations (id, name) VALUES
(1, 'Gedung A'),
(2, 'Perpustakaan'),
(3, 'Kantin'),
(4, 'Parkiran'),
(5, 'Masjid Kampus');
