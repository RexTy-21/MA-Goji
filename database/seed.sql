USE simple_sekolah;

INSERT INTO guru (nip,nama,jenis_kelamin,mapel,jabatan,status_kepegawaian,no_hp,email) VALUES
('198006052005071001','Drs. H. Ahmad Fauzi, M.Pd','L','-','Kepala Madrasah','PNS','081230001111','guru1@ma-ghoyatuljihad.example'),
('198011022005021002','Hj. Siti Nurhaliza, S.Pd','P','Bahasa Indonesia','Wakil Kepala Kurikulum','PNS','081230002222','guru2@ma-ghoyatuljihad.example'),
('198009042005061003','Muhammad Rizki, S.Kom','L','Informatika','Kepala Tata Usaha','GTY','081230003333','guru3@ma-ghoyatuljihad.example'),
('198010022005091004','Ustadz Abdul Karim, Lc','L','Fiqih','Guru Mapel / Wali Kelas','GTY','081230004444','guru4@ma-ghoyatuljihad.example'),
('198004022005021005','Dewi Lestari, S.Pd','P','Matematika','Guru Mapel / Wali Kelas','PNS','081230005555','guru5@ma-ghoyatuljihad.example'),
('198007142005021006','Nurul Aini, S.Pd','P','Bahasa Inggris','Guru Mapel / Wali Kelas','PPPK','081230006666','guru6@ma-ghoyatuljihad.example'),
('198004032005091007','Hasan Basri, S.Pd.I','L','Akidah Akhlak','Guru Mapel / Wali Kelas','GTY','081230007777','guru7@ma-ghoyatuljihad.example'),
('198007022005101008','Fatimah Azzahra, S.Pd','P','Biologi','Guru Mapel / Wali Kelas','GTY','081230008888','guru8@ma-ghoyatuljihad.example'),
('198002082005111009','Yusuf Maulana, S.Si','L','Fisika','Guru Mapel / Wali Kelas','PPPK','081230009999','guru9@ma-ghoyatuljihad.example'),
('198011192005011010','Aisyah Putri, S.Pd','P','Kimia','Guru Mapel','GTT / Honorer','081230011110','guru10@ma-ghoyatuljihad.example'),
('198010192005071011','Ridwan Hakim, S.Pd','L','Sejarah Kebudayaan Islam','Guru Mapel','GTY','081230012221','guru11@ma-ghoyatuljihad.example'),
('198001082005011012','Lilis Suryani, S.E','P','Ekonomi','Guru Mapel','GTT / Honorer','081230013332','guru12@ma-ghoyatuljihad.example'),
('198009282005031013','Bambang Heriyanto, S.Pd','L','PJOK','Guru Mapel','PNS','081230014443','guru13@ma-ghoyatuljihad.example'),
('198005142005031014','Zainal Abidin, S.Ag','L','Bahasa Arab','Guru Mapel','GTY','081230015554','guru14@ma-ghoyatuljihad.example'),
('198009042005101015','Rina Marlina, S.Pd','P','Geografi','Guru Mapel','GTT / Honorer','081230016665','guru15@ma-ghoyatuljihad.example');

-- password semua akun: demo1234
INSERT INTO master_user (user_name,user_password,user_fullname,user_role,guru_id) VALUES
('admin',SHA2('demo1234',256),'Administrator Demo','ADMIN',NULL),
('kepsek',SHA2('demo1234',256),'Drs. H. Ahmad Fauzi, M.Pd','KEPALA MADRASAH',1),
('wakasek',SHA2('demo1234',256),'Hj. Siti Nurhaliza, S.Pd','WAKIL KEPALA',2),
('tu',SHA2('demo1234',256),'Muhammad Rizki, S.Kom','TATA USAHA',3),
('guru.fiqih',SHA2('demo1234',256),'Ustadz Abdul Karim, Lc','GURU',4),
('guru.mtk',SHA2('demo1234',256),'Dewi Lestari, S.Pd','GURU',5),
('guru.bing',SHA2('demo1234',256),'Nurul Aini, S.Pd','GURU',6);

INSERT INTO kelas (nama,tingkat,jurusan,wali_guru_id) VALUES
('X IPA','X','IPA',4),
('X IPS','X','IPS',5),
('XI IPA','XI','IPA',6),
('XI IPS','XI','IPS',7),
('XII IPA','XII','IPA',8),
('XII IPS','XII','IPS',9);

INSERT INTO siswa (nis,nisn,nama,jenis_kelamin,kelas_id,tanggal_lahir,alamat,nama_wali,no_hp_wali) VALUES
('2526001','0034256684','Reza Safitri','L',1,'2010-02-19','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Fauziah','081350000733'),
('2526002','0023076910','Dewi Hakim','P',1,'2010-09-23','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hidayat','081350001466'),
('2526003','0037643310','Muhammad Utami','L',1,'2010-08-22','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Safitri','081350002199'),
('2526004','0072492024','Intan Setiawan','P',1,'2010-10-15','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hakim','081350002932'),
('2526005','0034127884','Reza Firmansyah','L',1,'2010-12-25','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Firmansyah','081350003665'),
('2526006','0050298754','Siti Fauziah','P',1,'2010-09-16','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Setiawan','081350004398'),
('2526007','0091733095','Zidan Permana','L',1,'2010-02-04','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Amelia','081350005131'),
('2526008','0055909953','Intan Wibowo','P',1,'2010-03-16','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Lestari','081350005864'),
('2526009','0084903659','Muhammad Hidayat','L',2,'2010-10-26','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Setiawan','081350006597'),
('2526010','0089774974','Zahra Hakim','P',2,'2010-08-19','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wulandari','081350007330'),
('2526011','0046230636','Rizky Hidayat','L',2,'2010-08-23','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hidayat','081350008063'),
('2526012','0096856164','Nur Permana','P',2,'2010-10-22','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wulandari','081350008796'),
('2526013','0099745048','Reza Anggraini','L',2,'2010-06-01','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wulandari','081350009529'),
('2526014','0091996233','Alya Wibowo','P',2,'2010-02-16','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Saputra','081350010262'),
('2526015','0027359750','Naufal Permana','L',2,'2010-12-08','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Anggraini','081350010995'),
('2526016','0020815439','Fitri Rahmawati','P',2,'2010-03-15','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Anggraini','081350011728'),
('2526017','0067783637','Alif Nugraha','L',3,'2009-09-09','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Lestari','081350012461'),
('2526018','0040970943','Alya Anggraini','P',3,'2009-03-03','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wibowo','081350013194'),
('2526019','0098384612','Dimas Firmansyah','L',3,'2009-04-01','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Rahmawati','081350013927'),
('2526020','0047840101','Salsabila Kusuma','P',3,'2009-01-05','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Lestari','081350014660'),
('2526021','0086013032','Ilham Utami','L',3,'2009-06-05','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Amelia','081350015393'),
('2526022','0085064182','Nur Wulandari','P',3,'2009-07-13','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Anggraini','081350016126'),
('2526023','0074628898','Raka Ramadhan','L',3,'2009-11-13','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Saputra','081350016859'),
('2526024','0038019720','Dewi Hidayat','P',3,'2009-08-06','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Ramadhan','081350017592'),
('2526025','0017056578','Hafiz Utami','L',4,'2009-02-01','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Fauziah','081350018325'),
('2526026','0023618316','Annisa Safitri','P',4,'2009-06-20','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Pratama','081350019058'),
('2526027','0092418944','Rizky Maulana','L',4,'2009-07-05','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Kusuma','081350019791'),
('2526028','0058877189','Alya Utami','P',4,'2009-08-04','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Ramadhan','081350020524'),
('2526029','0074477539','Arif Wulandari','L',4,'2009-08-10','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hidayat','081350021257'),
('2526030','0055987803','Annisa Ramadhan','P',4,'2009-12-09','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Rahmawati','081350021990'),
('2526031','0013099855','Bagas Amelia','L',4,'2009-04-17','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hakim','081350022723'),
('2526032','0050008920','Aisyah Amelia','P',4,'2009-11-28','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hidayat','081350023456'),
('2526033','0059217612','Alif Amelia','L',5,'2008-03-12','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Firmansyah','081350024189'),
('2526034','0092306098','Zahra Firmansyah','P',5,'2008-04-26','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Firmansyah','081350024922'),
('2526035','0036832537','Raka Firmansyah','L',5,'2008-09-16','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hakim','081350025655'),
('2526036','0047502921','Aisyah Pratama','P',5,'2008-08-09','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Maulana','081350026388'),
('2526037','0056911734','Ilham Wulandari','L',5,'2008-06-03','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Firmansyah','081350027121'),
('2526038','0073093067','Putri Firmansyah','P',5,'2008-04-11','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Maulana','081350027854'),
('2526039','0091907998','Arif Utami','L',5,'2008-01-16','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hakim','081350028587'),
('2526040','0062148384','Siti Ramadhan','P',5,'2008-12-25','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Maulana','081350029320'),
('2526041','0068240437','Arif Wibowo','L',6,'2008-11-11','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hidayat','081350030053'),
('2526042','0063873226','Fitri Wulandari','P',6,'2008-12-03','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wibowo','081350030786'),
('2526043','0013697544','Bagas Nugraha','L',6,'2008-03-19','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Wulandari','081350031519'),
('2526044','0089976351','Annisa Utami','P',6,'2008-08-22','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Hakim','081350032252'),
('2526045','0083589642','Dimas Safitri','L',6,'2008-03-01','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Pratama','081350032985'),
('2526046','0028689916','Putri Amelia','P',6,'2008-07-28','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Maulana','081350033718'),
('2526047','0043800696','Naufal Pratama','L',6,'2008-04-10','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Amelia','081350034451'),
('2526048','0053753544','Rani Fauziah','P',6,'2008-05-18','Desa Contoh, Telagasari, Karawang','Bpk/Ibu Lestari','081350035184');

-- data absensi contoh: 5 hari kerja terakhir
INSERT IGNORE INTO absensi_siswa (siswa_id, tanggal, jam_masuk, jam_pulang, status)
SELECT s.id, DATE_SUB(CURDATE(), INTERVAL d.n DAY),
  CASE MOD(CRC32(CONCAT(s.id, d.n)), 25) WHEN 0 THEN '07:12:00' WHEN 1 THEN NULL WHEN 2 THEN NULL WHEN 3 THEN NULL ELSE '06:50:00' END,
  CASE WHEN MOD(CRC32(CONCAT(s.id, d.n)), 25) BETWEEN 1 AND 3 THEN NULL ELSE '14:00:00' END,
  CASE MOD(CRC32(CONCAT(s.id, d.n)), 25) WHEN 0 THEN 'Terlambat' WHEN 1 THEN 'Sakit' WHEN 2 THEN 'Izin' WHEN 3 THEN 'Alpa' ELSE 'Hadir' END
FROM siswa s JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7) d
WHERE DAYOFWEEK(DATE_SUB(CURDATE(), INTERVAL d.n DAY)) NOT IN (1, 7);

INSERT IGNORE INTO absensi_guru (guru_id, tanggal, jam_masuk, jam_pulang, status)
SELECT g.id, DATE_SUB(CURDATE(), INTERVAL d.n DAY),
  IF(MOD(CRC32(CONCAT('g', g.id, d.n)), 8) = 0, '07:10:00', '06:45:00'), '15:00:00',
  IF(MOD(CRC32(CONCAT('g', g.id, d.n)), 8) = 0, 'Terlambat', 'Hadir')
FROM guru g JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7) d
WHERE DAYOFWEEK(DATE_SUB(CURDATE(), INTERVAL d.n DAY)) NOT IN (1, 7);

INSERT INTO login_log (user_id, login_time, ip_address, status) VALUES
(1, NOW() - INTERVAL 1 HOUR, '127.0.0.1', 'Success'), (1, NOW() - INTERVAL 1 DAY, '127.0.0.1', 'Success'), (4, NOW() - INTERVAL 3 HOUR, '127.0.0.1', 'Success');
