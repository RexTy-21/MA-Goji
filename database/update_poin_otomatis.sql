-- Poin otomatis: siswa yang scan masuk lebih dari 15 menit setelah batas (07:00) dikurangi poin kedisiplinan
INSERT INTO jenis_pelanggaran (nama, kategori, poin)
SELECT 'Terlambat lebih dari 15 menit', 'Ringan', 5
WHERE NOT EXISTS (SELECT 1 FROM jenis_pelanggaran WHERE nama = 'Terlambat lebih dari 15 menit');
