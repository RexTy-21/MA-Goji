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
