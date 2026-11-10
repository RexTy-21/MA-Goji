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
