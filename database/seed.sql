-- Seed data dummy untuk demo & pengujian awal (disesuaikan dengan database/tabel_auth.sql).
-- Jalankan SETELAH tabel_auth.sql, 002_items.sql, dan 003_claims.sql.

-- Password untuk semua akun demo di bawah: "password123"
-- Hash dibuat dengan password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO users (id, nama, email, nim_nip, password, role) VALUES
(1, 'Admin Kampus', 'admin@kampus.ac.id', '19900101001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Budi Santoso', 'budi@kampus.ac.id', '230101001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(3, 'Siti Aminah', 'siti@kampus.ac.id', '230101002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO categories (id, name) VALUES
(1, 'Elektronik'),
(2, 'Dokumen & Kartu'),
(3, 'Tas & Dompet'),
(4, 'Kunci'),
(5, 'Lainnya');

INSERT INTO locations (id, name) VALUES
(1, 'Gedung A'),
(2, 'Perpustakaan'),
(3, 'Kantin'),
(4, 'Parkiran'),
(5, 'Masjid Kampus');

INSERT INTO items (id, user_id, category_id, location_id, type, title, description, secret_detail, photo, status) VALUES
(1, 2, 2, 2, 'found', 'KTM atas nama mahasiswa Informatika', 'Ditemukan kartu mahasiswa di meja baca lantai 2.', 'NIM akhiran 042, ada stiker kecil di belakang', NULL, 'open'),
(2, 3, 1, 3, 'lost', 'Charger Laptop Lenovo Type-C', 'Tertinggal di dekat colokan pojok kantin sekitar jam 12 siang.', 'Ada lakban hitam di ujung kabel', NULL, 'open'),
(3, 2, 4, 4, 'found', 'Kunci Motor Honda dengan gantungan biru', 'Ditemukan di area parkiran motor dekat pos satpam.', 'Gantungan karet bertuliskan nama bengkel', NULL, 'open');

INSERT INTO verification_questions (id, item_id, question, answer) VALUES
(1, 1, 'Sebutkan 3 digit terakhir NIM pada kartu tersebut', '042'),
(2, 3, 'Apa warna gantungan kunci tersebut?', 'biru');
