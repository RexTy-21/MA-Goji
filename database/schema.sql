-- SIMPLE untuk MA Ghoyatul Jihad Karawang - skema database DEMO
CREATE DATABASE IF NOT EXISTS simple_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simple_sekolah;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_log, absensi_guru, absensi_siswa, siswa, kelas, master_user, guru;

CREATE TABLE guru (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nip VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  jenis_kelamin CHAR(1) NOT NULL DEFAULT 'L',
  mapel VARCHAR(100) NULL,
  jabatan VARCHAR(100) NULL,
  status_kepegawaian VARCHAR(30) NOT NULL DEFAULT 'GTY',
  no_hp VARCHAR(30) NULL,
  email VARCHAR(100) NULL,
  status VARCHAR(10) NOT NULL DEFAULT 'Active'
);
CREATE TABLE master_user (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(50) NOT NULL UNIQUE,
  user_password VARCHAR(255) NOT NULL,
  user_fullname VARCHAR(120) NOT NULL,
  user_role VARCHAR(30) NOT NULL DEFAULT 'GURU',
  guru_id INT NULL,
  user_status TINYINT NOT NULL DEFAULT 1
);
CREATE TABLE kelas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(30) NOT NULL UNIQUE,
  tingkat VARCHAR(5) NOT NULL,
  jurusan VARCHAR(30) NULL,
  wali_guru_id INT NULL
);
CREATE TABLE siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nisn VARCHAR(20) NULL,
  nama VARCHAR(120) NOT NULL,
  jenis_kelamin CHAR(1) NOT NULL DEFAULT 'L',
  kelas_id INT NULL,
  tanggal_lahir DATE NULL,
  alamat VARCHAR(200) NULL,
  nama_wali VARCHAR(120) NULL,
  no_hp_wali VARCHAR(30) NULL,
  status VARCHAR(10) NOT NULL DEFAULT 'Active',
  KEY (kelas_id)
);
CREATE TABLE absensi_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jam_masuk TIME NULL,
  jam_pulang TIME NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'Hadir',
  keterangan VARCHAR(100) NULL,
  UNIQUE KEY uq_abs_siswa (siswa_id, tanggal)
);
CREATE TABLE absensi_guru (
  id INT AUTO_INCREMENT PRIMARY KEY,
  guru_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jam_masuk TIME NULL,
  jam_pulang TIME NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'Hadir',
  keterangan VARCHAR(100) NULL,
  UNIQUE KEY uq_abs_guru (guru_id, tanggal)
);
CREATE TABLE login_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  status VARCHAR(20) DEFAULT 'Success',
  KEY (user_id, login_time)
);
SET FOREIGN_KEY_CHECKS = 1;
-- TAHAP 1: Tahun Ajaran, Mata Pelajaran, Jadwal Pelajaran  (jalankan di database goji_db)
CREATE TABLE IF NOT EXISTS tahun_ajaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(20) NOT NULL,              -- 2026/2027
  semester VARCHAR(10) NOT NULL,          -- Ganjil / Genap
  tgl_mulai DATE NULL,
  tgl_selesai DATE NULL,
  aktif TINYINT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_ta (nama, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mapel (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(15) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  kelompok VARCHAR(30) NOT NULL DEFAULT 'Umum',   -- Umum / Agama / Peminatan / Muatan Lokal
  kkm INT NOT NULL DEFAULT 75
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jadwal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tahun_ajaran_id INT NOT NULL,
  kelas_id INT NOT NULL,
  mapel_id INT NOT NULL,
  guru_id INT NULL,
  hari TINYINT NOT NULL,                  -- 1=Senin ... 6=Sabtu
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  ruang VARCHAR(30) NULL,
  KEY (tahun_ajaran_id, kelas_id, hari),
  KEY (guru_id, hari)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== data contoh =====
INSERT IGNORE INTO tahun_ajaran (nama, semester, tgl_mulai, tgl_selesai, aktif) VALUES
 ('2025/2026','Genap','2026-01-05','2026-06-20',0),
 ('2026/2027','Ganjil','2026-07-13','2026-12-19',1);

INSERT IGNORE INTO mapel (kode, nama, kelompok, kkm) VALUES
 ('AQH','Al-Qur''an Hadis','Agama',75),('AKH','Akidah Akhlak','Agama',75),('FQH','Fikih','Agama',75),
 ('SKI','Sejarah Kebudayaan Islam','Agama',75),('BAR','Bahasa Arab','Agama',75),
 ('PKN','Pendidikan Pancasila','Umum',75),('BIN','Bahasa Indonesia','Umum',75),('BIG','Bahasa Inggris','Umum',75),
 ('MTK','Matematika','Umum',75),('SEJ','Sejarah Indonesia','Umum',75),('PJK','PJOK','Umum',75),
 ('FIS','Fisika','Peminatan',75),('KIM','Kimia','Peminatan',75),('BIO','Biologi','Peminatan',75),
 ('EKO','Ekonomi','Peminatan',75),('SOS','Sosiologi','Peminatan',75),('GEO','Geografi','Peminatan',75);

-- contoh jadwal Senin kelas "X IPA" (dilewati bila kelas/mapel tidak ada)
INSERT INTO jadwal (tahun_ajaran_id, kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai, ruang)
SELECT (SELECT id FROM tahun_ajaran WHERE aktif=1 LIMIT 1), k.id, m.id,
       (SELECT id FROM guru WHERE mapel LIKE CONCAT('%', m.nama, '%') LIMIT 1), 1, t.mulai, t.selesai, 'R. X IPA'
FROM kelas k
JOIN (SELECT 'MTK' kode,'07:15:00' mulai,'08:45:00' selesai UNION ALL SELECT 'BIN','09:00:00','10:30:00' UNION ALL SELECT 'FQH','10:45:00','12:15:00') t
JOIN mapel m ON m.kode = t.kode
WHERE k.nama = 'X IPA'
  AND NOT EXISTS (SELECT 1 FROM jadwal);

-- TAHAP 2: Perizinan, Pengumuman, Kenaikan Kelas/Mutasi  (jalankan di database goji_db)
CREATE TABLE IF NOT EXISTS perizinan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pemohon VARCHAR(10) NOT NULL,            -- Siswa / Guru
  siswa_id INT NULL,
  guru_id INT NULL,
  jenis VARCHAR(10) NOT NULL DEFAULT 'Izin',   -- Izin / Sakit
  tgl_mulai DATE NOT NULL,
  tgl_selesai DATE NOT NULL,
  alasan VARCHAR(255) NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'Menunggu',  -- Menunggu / Disetujui / Ditolak
  diajukan_oleh VARCHAR(100) NULL,
  dibuat DATETIME NULL,
  diproses_oleh VARCHAR(100) NULL,
  diproses_pada DATETIME NULL,
  catatan VARCHAR(255) NULL,
  KEY (status), KEY (siswa_id), KEY (guru_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pengumuman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  isi TEXT NOT NULL,
  penting TINYINT NOT NULL DEFAULT 0,
  tanggal DATE NOT NULL,
  berlaku_sampai DATE NULL,
  penulis VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mutasi_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  jenis VARCHAR(20) NOT NULL,              -- Naik Kelas / Pindah Kelas / Lulus / Pindah Sekolah / Keluar
  dari_kelas_id INT NULL,
  ke_kelas_id INT NULL,
  tanggal DATE NOT NULL,
  keterangan VARCHAR(200) NULL,
  oleh VARCHAR(100) NULL,
  KEY (siswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== data contoh =====
INSERT INTO pengumuman (judul, isi, penting, tanggal, berlaku_sampai, penulis)
SELECT * FROM (
 SELECT 'Rapat Dewan Guru' a, 'Rapat evaluasi bulanan dilaksanakan hari Sabtu pukul 09.00 di ruang guru. Seluruh guru dimohon hadir.' b, 1 c, CURDATE() d, DATE_ADD(CURDATE(), INTERVAL 14 DAY) e, 'Administrator Demo' f
 UNION ALL SELECT 'Jadwal Ujian Tengah Semester', 'UTS semester ganjil dimulai dua minggu lagi. Jadwal lengkap dapat dilihat di menu Jadwal Pelajaran.', 0, CURDATE(), NULL, 'Administrator Demo'
) t WHERE NOT EXISTS (SELECT 1 FROM pengumuman);

INSERT INTO perizinan (pemohon, siswa_id, jenis, tgl_mulai, tgl_selesai, alasan, status, diajukan_oleh, dibuat)
SELECT 'Siswa', (SELECT id FROM siswa WHERE status='Active' ORDER BY id LIMIT 1), 'Sakit', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Demam, surat dokter menyusul', 'Menunggu', 'Administrator Demo', NOW()
WHERE NOT EXISTS (SELECT 1 FROM perizinan) AND EXISTS (SELECT 1 FROM siswa);

-- TAHAP 3: Nilai & Rapor  (jalankan di database goji_db; butuh tabel tahap 1)
CREATE TABLE IF NOT EXISTS nilai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tahun_ajaran_id INT NOT NULL,
  siswa_id INT NOT NULL,
  mapel_id INT NOT NULL,
  tugas DECIMAL(5,2) NULL,
  uts DECIMAL(5,2) NULL,
  uas DECIMAL(5,2) NULL,
  diubah_oleh VARCHAR(100) NULL,
  diubah_pada DATETIME NULL,
  UNIQUE KEY uq_nilai (tahun_ajaran_id, siswa_id, mapel_id),
  KEY (mapel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rapor_catatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tahun_ajaran_id INT NOT NULL,
  siswa_id INT NOT NULL,
  sikap VARCHAR(20) NULL,           -- Sangat Baik / Baik / Cukup / Kurang
  catatan VARCHAR(500) NULL,
  UNIQUE KEY uq_rc (tahun_ajaran_id, siswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== data contoh: nilai kelas X IPA untuk beberapa mapel (hanya bila tabel nilai masih kosong) =====
INSERT IGNORE INTO nilai (tahun_ajaran_id, siswa_id, mapel_id, tugas, uts, uas, diubah_oleh, diubah_pada)
SELECT (SELECT id FROM tahun_ajaran WHERE aktif=1 LIMIT 1), s.id, m.id,
       60 + MOD(s.id*7 + m.id*3, 36), 55 + MOD(s.id*11 + m.id*5, 41), 58 + MOD(s.id*13 + m.id*2, 39), 'Seed', NOW()
FROM siswa s JOIN kelas k ON k.id = s.kelas_id AND k.nama = 'X IPA'
JOIN mapel m ON m.kode IN ('MTK','BIN','BIG','FQH','AKH','FIS')
WHERE s.status='Active' AND EXISTS (SELECT 1 FROM tahun_ajaran WHERE aktif=1)
  AND NOT EXISTS (SELECT 1 FROM nilai);

-- TAHAP 4: Pelanggaran Siswa + Inventaris  (jalankan di database goji_db)
CREATE TABLE IF NOT EXISTS jenis_pelanggaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  kategori VARCHAR(10) NOT NULL DEFAULT 'Ringan',   -- Ringan / Sedang / Berat
  poin INT NOT NULL DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pelanggaran_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  jenis_id INT NOT NULL,
  tanggal DATE NOT NULL,
  poin INT NOT NULL,                  -- salinan poin saat dicatat
  keterangan VARCHAR(255) NULL,
  dicatat_oleh VARCHAR(100) NULL,
  dibuat DATETIME NULL,
  KEY (siswa_id, tanggal), KEY (jenis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventaris_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  kategori VARCHAR(30) NOT NULL DEFAULT 'Lainnya',
  lokasi VARCHAR(80) NULL,
  jumlah INT NOT NULL DEFAULT 1,
  kondisi VARCHAR(15) NOT NULL DEFAULT 'Baik',      -- Baik / Rusak Ringan / Rusak Berat
  tahun INT NULL,
  harga DECIMAL(14,0) NULL,
  keterangan VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventaris_pinjam (
  id INT AUTO_INCREMENT PRIMARY KEY,
  barang_id INT NOT NULL,
  peminjam VARCHAR(120) NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  tgl_pinjam DATE NOT NULL,
  tgl_rencana DATE NULL,
  tgl_kembali DATE NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'Dipinjam',   -- Dipinjam / Kembali
  keterangan VARCHAR(255) NULL,
  dicatat_oleh VARCHAR(100) NULL,
  KEY (barang_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== data contoh =====
INSERT INTO jenis_pelanggaran (nama, kategori, poin)
SELECT * FROM (
 SELECT 'Terlambat masuk madrasah' n,'Ringan' k,5 p UNION ALL SELECT 'Tidak memakai seragam sesuai ketentuan','Ringan',10
 UNION ALL SELECT 'Tidak mengerjakan tugas','Ringan',5 UNION ALL SELECT 'Membawa HP saat pembelajaran','Sedang',15
 UNION ALL SELECT 'Membolos / keluar tanpa izin','Sedang',25 UNION ALL SELECT 'Mencontek saat ujian','Sedang',30
 UNION ALL SELECT 'Berkelahi','Berat',50 UNION ALL SELECT 'Merokok di lingkungan madrasah','Berat',50
) t WHERE NOT EXISTS (SELECT 1 FROM jenis_pelanggaran);

INSERT INTO inventaris_barang (kode, nama, kategori, lokasi, jumlah, kondisi, tahun, harga, keterangan)
SELECT * FROM (
 SELECT 'INV-0001' a,'Proyektor' b,'Elektronik' c,'Ruang Guru' d,4 e,'Baik' f,2023 g,5500000 h,'' i
 UNION ALL SELECT 'INV-0002','Laptop Guru','Elektronik','Ruang TU',6,'Baik',2024,7200000,''
 UNION ALL SELECT 'INV-0003','Meja Siswa','Mebel','Kelas X IPA',40,'Baik',2022,350000,''
 UNION ALL SELECT 'INV-0004','Kursi Siswa','Mebel','Kelas X IPA',40,'Rusak Ringan',2022,250000,'3 kursi patah kaki'
 UNION ALL SELECT 'INV-0005','Bola Voli','Olahraga','Gudang Olahraga',8,'Baik',2025,180000,''
 UNION ALL SELECT 'INV-0006','Sound System Portable','Elektronik','Aula',1,'Baik',2023,3500000,''
) t WHERE NOT EXISTS (SELECT 1 FROM inventaris_barang);

-- MASTER MENU: pengaturan akses menu per role / per user  (jalankan di database goji_db)
CREATE TABLE IF NOT EXISTS master_menu_akses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  target_type VARCHAR(5) NOT NULL,        -- ROLE atau USER
  target_key VARCHAR(60) NOT NULL,        -- nama role, atau user_id
  page_key VARCHAR(40) NOT NULL,          -- kunci halaman, mis. Data-Siswa ('-' = sengaja tanpa menu)
  updated_by VARCHAR(100) NULL,
  updated_date DATETIME NULL,
  UNIQUE KEY uq_menu (target_type, target_key, page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pengaturan awal untuk role GURU (hanya dibuat bila role GURU belum pernah diatur). Ubah kapan saja di menu Master Menu.
INSERT IGNORE INTO master_menu_akses (target_type, target_key, page_key, updated_by, updated_date)
SELECT 'ROLE', 'GURU', t.p, 'Sistem', NOW()
FROM (SELECT 'Data-Siswa' p UNION ALL SELECT 'Data-Kelas' UNION ALL SELECT 'Jadwal' UNION ALL SELECT 'Absen-Scan' UNION ALL SELECT 'Absen-Siswa'
      UNION ALL SELECT 'Input-Nilai' UNION ALL SELECT 'Rapor' UNION ALL SELECT 'Perizinan' UNION ALL SELECT 'Pengumuman'
      UNION ALL SELECT 'Pelanggaran' UNION ALL SELECT 'Upload-File') t
WHERE NOT EXISTS (SELECT 1 FROM master_menu_akses WHERE target_type = 'ROLE' AND target_key = 'GURU');

-- Poin otomatis: siswa yang scan masuk lebih dari 15 menit setelah batas (07:00) dikurangi poin kedisiplinan
INSERT INTO jenis_pelanggaran (nama, kategori, poin)
SELECT 'Terlambat lebih dari 15 menit', 'Ringan', 5
WHERE NOT EXISTS (SELECT 1 FROM jenis_pelanggaran WHERE nama = 'Terlambat lebih dari 15 menit');
