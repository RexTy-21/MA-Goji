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
