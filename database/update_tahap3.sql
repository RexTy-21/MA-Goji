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
