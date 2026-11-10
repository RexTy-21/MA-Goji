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
