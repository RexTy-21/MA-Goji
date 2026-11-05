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
