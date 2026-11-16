-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2026 at 06:36 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goji_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi_guru`
--

CREATE TABLE `absensi_guru` (
  `id` int NOT NULL,
  `guru_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'Hadir',
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `absensi_siswa`
--

CREATE TABLE `absensi_siswa` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'Hadir',
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `absensi_siswa`
--

INSERT INTO `absensi_siswa` (`id`, `siswa_id`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `keterangan`) VALUES
(259, 49, '2026-11-15', '16:01:40', NULL, 'Terlambat', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL DEFAULT 'L',
  `mapel` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_kepegawaian` varchar(30) NOT NULL DEFAULT 'GTY',
  `no_hp` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'Active',
  `foto` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `mapel`, `jabatan`, `status_kepegawaian`, `no_hp`, `email`, `status`, `foto`) VALUES
(1, '198006052005071001', 'Drs. H. Ahmad Fauzi, M.Pd', 'L', '-', 'Kepala Madrasah', 'PNS', '081230001111', 'guru1@ma-ghoyatuljihad.example', 'Active', NULL),
(2, '198011022005021002', 'Hj. Siti Nurhaliza, S.Pd', 'P', 'Bahasa Indonesia', 'Wakil Kepala Kurikulum', 'PNS', '081230002222', 'guru2@ma-ghoyatuljihad.example', 'Active', NULL),
(3, '198009042005061003', 'Muhammad Rizki, S.Kom', 'L', 'Informatika', 'Kepala Tata Usaha', 'GTY', '081230003333', 'guru3@ma-ghoyatuljihad.example', 'Active', NULL),
(4, '198010022005091004', 'Ustadz Abdul Karim, Lc', 'L', 'Fiqih', 'Guru Mapel / Wali Kelas', 'GTY', '081230004444', 'guru4@ma-ghoyatuljihad.example', 'Active', NULL),
(5, '198004022005021005', 'Dewi Lestari, S.Pd', 'P', 'Matematika', 'Guru Mapel / Wali Kelas', 'PNS', '081230005555', 'guru5@ma-ghoyatuljihad.example', 'Active', NULL),
(6, '198007142005021006', 'Nurul Aini, S.Pd', 'P', 'Bahasa Inggris', 'Guru Mapel / Wali Kelas', 'PPPK', '081230006666', 'guru6@ma-ghoyatuljihad.example', 'Active', NULL),
(7, '198004032005091007', 'Hasan Basri, S.Pd.I', 'L', 'Akidah Akhlak', 'Guru Mapel / Wali Kelas', 'GTY', '081230007777', 'guru7@ma-ghoyatuljihad.example', 'Active', NULL),
(8, '198007022005101008', 'Fatimah Azzahra, S.Pd', 'P', 'Biologi', 'Guru Mapel / Wali Kelas', 'GTY', '081230008888', 'guru8@ma-ghoyatuljihad.example', 'Active', NULL),
(9, '198002082005111009', 'Yusuf Maulana, S.Si', 'L', 'Fisika', 'Guru Mapel / Wali Kelas', 'PPPK', '081230009999', 'guru9@ma-ghoyatuljihad.example', 'Active', NULL),
(10, '198011192005011010', 'Aisyah Putri, S.Pd', 'P', 'Kimia', 'Guru Mapel', 'GTT / Honorer', '081230011110', 'guru10@ma-ghoyatuljihad.example', 'Active', NULL),
(11, '198010192005071011', 'Ridwan Hakim, S.Pd', 'L', 'Sejarah Kebudayaan Islam', 'Guru Mapel', 'GTY', '081230012221', 'guru11@ma-ghoyatuljihad.example', 'Active', NULL),
(12, '198001082005011012', 'Lilis Suryani, S.E', 'P', 'Ekonomi', 'Guru Mapel', 'GTT / Honorer', '081230013332', 'guru12@ma-ghoyatuljihad.example', 'Active', NULL),
(13, '198009282005031013', 'Bambang Heriyanto, S.Pd', 'L', 'PJOK', 'Guru Mapel', 'PNS', '081230014443', 'guru13@ma-ghoyatuljihad.example', 'Active', NULL),
(14, '198005142005031014', 'Zainal Abidin, S.Ag', 'L', 'Bahasa Arab', 'Guru Mapel', 'GTY', '081230015554', 'guru14@ma-ghoyatuljihad.example', 'Active', NULL),
(15, '198009042005101015', 'Rina Marlina, S.Pd', 'P', 'Geografi', 'Guru Mapel', 'GTT / Honorer', '081230016665', 'guru15@ma-ghoyatuljihad.example', 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventaris_barang`
--

CREATE TABLE `inventaris_barang` (
  `id` int NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `kategori` varchar(30) NOT NULL DEFAULT 'Lainnya',
  `lokasi` varchar(80) DEFAULT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `kondisi` varchar(15) NOT NULL DEFAULT 'Baik',
  `tahun` int DEFAULT NULL,
  `harga` decimal(14,0) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventaris_barang`
--

INSERT INTO `inventaris_barang` (`id`, `kode`, `nama`, `kategori`, `lokasi`, `jumlah`, `kondisi`, `tahun`, `harga`, `keterangan`) VALUES
(1, 'INV-0001', 'Proyektor', 'Elektronik', 'Ruang Guru', 4, 'Baik', 2023, '5500000', ''),
(2, 'INV-0002', 'Laptop Guru', 'Elektronik', 'Ruang TU', 6, 'Baik', 2024, '7200000', ''),
(3, 'INV-0003', 'Meja Siswa', 'Mebel', 'Kelas X IPA', 40, 'Baik', 2022, '350000', ''),
(4, 'INV-0004', 'Kursi Siswa', 'Mebel', 'Kelas X IPA', 3, 'Rusak Ringan', 2022, '250000', 'patah kaki'),
(5, 'INV-0005', 'Bola Voli', 'Olahraga', 'Gudang Olahraga', 8, 'Baik', 2025, '180000', ''),
(6, 'INV-0006', 'Sound System Portable', 'Elektronik', 'Aula', 1, 'Baik', 2023, '3500000', ''),
(9, 'INV-0007', 'Kursi Siswa', 'Mebel', 'Semua Kelas', 40, 'Baik', 2026, '40000', '');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris_pinjam`
--

CREATE TABLE `inventaris_pinjam` (
  `id` int NOT NULL,
  `barang_id` int NOT NULL,
  `peminjam` varchar(120) NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `tgl_pinjam` date NOT NULL,
  `tgl_rencana` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'Dipinjam',
  `keterangan` varchar(255) DEFAULT NULL,
  `dicatat_oleh` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int NOT NULL,
  `tahun_ajaran_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `guru_id` int DEFAULT NULL,
  `hari` tinyint NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruang` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `tahun_ajaran_id`, `kelas_id`, `mapel_id`, `guru_id`, `hari`, `jam_mulai`, `jam_selesai`, `ruang`) VALUES
(1, 2, 1, 9, 5, 1, '07:15:00', '08:45:00', 'R. X IPA'),
(2, 2, 1, 7, 2, 1, '09:00:00', '10:30:00', 'R. X IPA'),
(3, 2, 1, 3, NULL, 1, '10:45:00', '12:15:00', 'R. X IPA'),
(4, 2, 1, 1, 11, 2, '07:15:00', '08:45:00', 'X IPA');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_pelanggaran`
--

CREATE TABLE `jenis_pelanggaran` (
  `id` int NOT NULL,
  `nama` varchar(120) NOT NULL,
  `kategori` varchar(10) NOT NULL DEFAULT 'Ringan',
  `poin` int NOT NULL DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jenis_pelanggaran`
--

INSERT INTO `jenis_pelanggaran` (`id`, `nama`, `kategori`, `poin`) VALUES
(2, 'Tidak memakai seragam sesuai ketentuan', 'Ringan', 5),
(3, 'Tidak mengerjakan tugas', 'Ringan', 5),
(5, 'Membolos / keluar tanpa izin', 'Sedang', 25),
(6, 'Mencontek saat ujian', 'Sedang', 30),
(7, 'Berkelahi', 'Berat', 40),
(8, 'Merokok di lingkungan Sekolah', 'Berat', 50),
(16, 'Terlambat lebih dari 15 menit', 'Ringan', 3);

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int NOT NULL,
  `nama` varchar(30) NOT NULL,
  `tingkat` varchar(5) NOT NULL,
  `jurusan` varchar(30) DEFAULT NULL,
  `wali_guru_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama`, `tingkat`, `jurusan`, `wali_guru_id`) VALUES
(1, 'X IPA', 'X', 'IPA', 4),
(2, 'X IPS', 'X', 'IPS', 5),
(3, 'XI IPA', 'XI', 'IPA', 6),
(4, 'XI IPS', 'XI', 'IPS', 7),
(5, 'XII IPA', 'XII', 'IPA', 8),
(6, 'XII IPS', 'XII', 'IPS', 9);

-- --------------------------------------------------------

--
-- Table structure for table `login_log`
--

CREATE TABLE `login_log` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `login_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_log`
--

INSERT INTO `login_log` (`id`, `user_id`, `login_time`, `ip_address`, `status`) VALUES
(1, 1, '2026-11-02 08:16:29', '127.0.0.1', 'Success'),
(2, 1, '2026-11-01 09:16:29', '127.0.0.1', 'Success'),
(3, 4, '2026-11-02 06:16:29', '127.0.0.1', 'Success'),
(4, 1, '2026-11-02 09:21:22', '::1', 'Success'),
(5, 1, '2026-11-02 10:12:56', '::1', 'Success'),
(6, 1, '2026-11-04 10:10:32', '127.0.0.1', 'Success'),
(7, 1, '2026-11-05 09:47:31', '127.0.0.1', 'Success'),
(8, 1, '2026-11-09 14:34:16', '127.0.0.1', 'Success'),
(9, 1, '2026-11-10 07:37:25', '127.0.0.1', 'Success'),
(10, 1, '2026-11-10 11:29:08', '::1', 'Success'),
(11, 1, '2026-11-12 11:12:53', '127.0.0.1', 'Success'),
(12, 4, '2026-11-12 13:31:13', '::1', 'Success'),
(13, 1, '2026-11-12 13:34:06', '::1', 'Success'),
(14, 1, '2026-11-13 21:55:09', '::1', 'Success'),
(15, 1, '2026-11-15 15:54:43', '::1', 'Success'),
(16, 4, '2026-11-15 16:08:50', '::1', 'Success'),
(17, 1, '2026-11-15 16:09:47', '::1', 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int NOT NULL,
  `kode` varchar(15) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelompok` varchar(30) NOT NULL DEFAULT 'Umum',
  `kkm` int NOT NULL DEFAULT '75'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `kode`, `nama`, `kelompok`, `kkm`) VALUES
(1, 'AQH', 'Al-Qur\'an Hadis', 'Agama', 75),
(2, 'AKH', 'Akidah Akhlak', 'Agama', 75),
(3, 'FQH', 'Fikih', 'Agama', 75),
(4, 'SKI', 'Sejarah Kebudayaan Islam', 'Agama', 75),
(5, 'BAR', 'Bahasa Arab', 'Agama', 75),
(6, 'PKN', 'Pendidikan Pancasila', 'Umum', 75),
(7, 'BIN', 'Bahasa Indonesia', 'Umum', 75),
(8, 'BIG', 'Bahasa Inggris', 'Umum', 75),
(9, 'MTK', 'Matematika', 'Umum', 75),
(10, 'SEJ', 'Sejarah Indonesia', 'Umum', 75),
(11, 'PJK', 'PJOK', 'Umum', 75),
(12, 'FIS', 'Fisika', 'Peminatan', 75),
(13, 'KIM', 'Kimia', 'Peminatan', 75),
(14, 'BIO', 'Biologi', 'Peminatan', 75),
(15, 'EKO', 'Ekonomi', 'Peminatan', 75),
(16, 'SOS', 'Sosiologi', 'Peminatan', 75),
(17, 'GEO', 'Geografi', 'Peminatan', 75);

-- --------------------------------------------------------

--
-- Table structure for table `master_cost_control`
--

CREATE TABLE `master_cost_control` (
  `id` int NOT NULL,
  `note` text NOT NULL,
  `doc_date` date DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_cost_control`
--

INSERT INTO `master_cost_control` (`id`, `note`, `doc_date`, `updated_by`, `updated_date`) VALUES
(2, 'pelanggaran si a', '2026-11-01', 'Administrator Demo', '2026-11-15 16:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `master_cost_control_file`
--

CREATE TABLE `master_cost_control_file` (
  `id` int NOT NULL,
  `cost_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_cost_control_file`
--

INSERT INTO `master_cost_control_file` (`id`, `cost_id`, `file_name`, `original_name`) VALUES
(2, 2, '1794733645_2_0_WhatsApp_Image_2026-11-05_at_11.42.47.jpeg', 'WhatsApp Image 2026-11-05 at 11.42.47.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `master_menu_akses`
--

CREATE TABLE `master_menu_akses` (
  `id` int NOT NULL,
  `target_type` varchar(5) NOT NULL,
  `target_key` varchar(60) NOT NULL,
  `page_key` varchar(40) NOT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_menu_akses`
--

INSERT INTO `master_menu_akses` (`id`, `target_type`, `target_key`, `page_key`, `updated_by`, `updated_date`) VALUES
(29, 'ROLE', 'TATA USAHA', 'Data-Siswa', 'Administrator Demo', '2026-11-10 07:45:30'),
(30, 'ROLE', 'TATA USAHA', 'Data-Guru', 'Administrator Demo', '2026-11-10 07:45:30'),
(31, 'ROLE', 'TATA USAHA', 'Data-Kelas', 'Administrator Demo', '2026-11-10 07:45:30'),
(32, 'ROLE', 'TATA USAHA', 'Mata-Pelajaran', 'Administrator Demo', '2026-11-10 07:45:30'),
(33, 'ROLE', 'TATA USAHA', 'Jadwal', 'Administrator Demo', '2026-11-10 07:45:30'),
(34, 'ROLE', 'TATA USAHA', 'Tahun-Ajaran', 'Administrator Demo', '2026-11-10 07:45:30'),
(35, 'ROLE', 'TATA USAHA', 'Kenaikan-Kelas', 'Administrator Demo', '2026-11-10 07:45:30'),
(36, 'ROLE', 'TATA USAHA', 'Absen-Scan', 'Administrator Demo', '2026-11-10 07:45:30'),
(37, 'ROLE', 'TATA USAHA', 'Input-Nilai', 'Administrator Demo', '2026-11-10 07:45:30'),
(38, 'ROLE', 'TATA USAHA', 'Rapor', 'Administrator Demo', '2026-11-10 07:45:30'),
(39, 'ROLE', 'TATA USAHA', 'Perizinan', 'Administrator Demo', '2026-11-10 07:45:30'),
(40, 'ROLE', 'TATA USAHA', 'Pengumuman', 'Administrator Demo', '2026-11-10 07:45:30'),
(41, 'ROLE', 'TATA USAHA', 'Pelanggaran', 'Administrator Demo', '2026-11-10 07:45:30'),
(42, 'ROLE', 'TATA USAHA', 'Inventaris', 'Administrator Demo', '2026-11-10 07:45:30'),
(43, 'ROLE', 'TATA USAHA', 'Laporan', 'Administrator Demo', '2026-11-10 07:45:30'),
(44, 'ROLE', 'TATA USAHA', 'Upload-File', 'Administrator Demo', '2026-11-10 07:45:30'),
(45, 'ROLE', 'BK', 'Data-Siswa', 'Administrator Demo', '2026-11-10 07:45:42'),
(46, 'ROLE', 'BK', 'Data-Kelas', 'Administrator Demo', '2026-11-10 07:45:42'),
(47, 'ROLE', 'BK', 'Mata-Pelajaran', 'Administrator Demo', '2026-11-10 07:45:42'),
(48, 'ROLE', 'BK', 'Jadwal', 'Administrator Demo', '2026-11-10 07:45:42'),
(49, 'ROLE', 'BK', 'Kenaikan-Kelas', 'Administrator Demo', '2026-11-10 07:45:43'),
(50, 'ROLE', 'BK', 'Absen-Scan', 'Administrator Demo', '2026-11-10 07:45:43'),
(51, 'ROLE', 'BK', 'Absen-Siswa', 'Administrator Demo', '2026-11-10 07:45:43'),
(52, 'ROLE', 'BK', 'Input-Nilai', 'Administrator Demo', '2026-11-10 07:45:43'),
(53, 'ROLE', 'BK', 'Rapor', 'Administrator Demo', '2026-11-10 07:45:43'),
(54, 'ROLE', 'BK', 'Perizinan', 'Administrator Demo', '2026-11-10 07:45:43'),
(55, 'ROLE', 'BK', 'Pengumuman', 'Administrator Demo', '2026-11-10 07:45:43'),
(56, 'ROLE', 'BK', 'Pelanggaran', 'Administrator Demo', '2026-11-10 07:45:43'),
(57, 'ROLE', 'BK', 'Laporan', 'Administrator Demo', '2026-11-10 07:45:43'),
(58, 'ROLE', 'BK', 'Upload-File', 'Administrator Demo', '2026-11-10 07:45:43'),
(59, 'ROLE', 'GURU', 'Data-Siswa', 'Administrator Demo', '2026-11-10 07:45:59'),
(60, 'ROLE', 'GURU', 'Data-Kelas', 'Administrator Demo', '2026-11-10 07:45:59'),
(61, 'ROLE', 'GURU', 'Jadwal', 'Administrator Demo', '2026-11-10 07:45:59'),
(62, 'ROLE', 'GURU', 'Absen-Scan', 'Administrator Demo', '2026-11-10 07:45:59'),
(63, 'ROLE', 'GURU', 'Absen-Siswa', 'Administrator Demo', '2026-11-10 07:45:59'),
(64, 'ROLE', 'GURU', 'Input-Nilai', 'Administrator Demo', '2026-11-10 07:45:59'),
(65, 'ROLE', 'GURU', 'Rapor', 'Administrator Demo', '2026-11-10 07:46:00'),
(66, 'ROLE', 'GURU', 'Perizinan', 'Administrator Demo', '2026-11-10 07:46:00'),
(67, 'ROLE', 'GURU', 'Pengumuman', 'Administrator Demo', '2026-11-10 07:46:00'),
(68, 'ROLE', 'GURU', 'Pelanggaran', 'Administrator Demo', '2026-11-10 07:46:00'),
(69, 'ROLE', 'GURU', 'Upload-File', 'Administrator Demo', '2026-11-10 07:46:00'),
(70, 'ROLE', 'KEPALA MADRASAH', 'Data-Siswa', 'Administrator Demo', '2026-11-10 07:46:16'),
(71, 'ROLE', 'KEPALA MADRASAH', 'Data-Guru', 'Administrator Demo', '2026-11-10 07:46:16'),
(72, 'ROLE', 'KEPALA MADRASAH', 'Data-Kelas', 'Administrator Demo', '2026-11-10 07:46:16'),
(73, 'ROLE', 'KEPALA MADRASAH', 'Mata-Pelajaran', 'Administrator Demo', '2026-11-10 07:46:16'),
(74, 'ROLE', 'KEPALA MADRASAH', 'Jadwal', 'Administrator Demo', '2026-11-10 07:46:16'),
(75, 'ROLE', 'KEPALA MADRASAH', 'Tahun-Ajaran', 'Administrator Demo', '2026-11-10 07:46:16'),
(76, 'ROLE', 'KEPALA MADRASAH', 'Kenaikan-Kelas', 'Administrator Demo', '2026-11-10 07:46:16'),
(77, 'ROLE', 'KEPALA MADRASAH', 'Absen-Scan', 'Administrator Demo', '2026-11-10 07:46:16'),
(78, 'ROLE', 'KEPALA MADRASAH', 'Absen-Siswa', 'Administrator Demo', '2026-11-10 07:46:16'),
(79, 'ROLE', 'KEPALA MADRASAH', 'Absen-Guru', 'Administrator Demo', '2026-11-10 07:46:16'),
(80, 'ROLE', 'KEPALA MADRASAH', 'Perizinan', 'Administrator Demo', '2026-11-10 07:46:16'),
(81, 'ROLE', 'KEPALA MADRASAH', 'Pengumuman', 'Administrator Demo', '2026-11-10 07:46:16'),
(82, 'ROLE', 'KEPALA MADRASAH', 'Pelanggaran', 'Administrator Demo', '2026-11-10 07:46:16'),
(83, 'ROLE', 'KEPALA MADRASAH', 'Inventaris', 'Administrator Demo', '2026-11-10 07:46:16'),
(84, 'ROLE', 'KEPALA MADRASAH', 'Laporan', 'Administrator Demo', '2026-11-10 07:46:16'),
(85, 'ROLE', 'KEPALA MADRASAH', 'Upload-File', 'Administrator Demo', '2026-11-10 07:46:16');

-- --------------------------------------------------------

--
-- Table structure for table `master_user`
--

CREATE TABLE `master_user` (
  `user_id` int NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_fullname` varchar(120) NOT NULL,
  `user_role` varchar(30) NOT NULL DEFAULT 'GURU',
  `guru_id` int DEFAULT NULL,
  `user_status` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_user`
--

INSERT INTO `master_user` (`user_id`, `user_name`, `user_password`, `user_fullname`, `user_role`, `guru_id`, `user_status`) VALUES
(1, 'admin', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Administrator Demo', 'ADMIN', NULL, 1),
(2, 'kepsek', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Drs. H. Ahmad Fauzi, M.Pd', 'KEPALA MADRASAH', 1, 1),
(3, 'wakasek', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Hj. Siti Nurhaliza, S.Pd', 'WAKIL KEPALA', 2, 1),
(4, 'tu', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Muhammad Rizki, S.Kom', 'TATA USAHA', 3, 1),
(5, 'guru.fiqih', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Ustadz Abdul Karim, Lc', 'GURU', 4, 1),
(6, 'guru.mtk', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Dewi Lestari, S.Pd', 'GURU', 5, 1),
(7, 'guru.bing', '0ead2060b65992dca4769af601a1b3a35ef38cfad2c2c465bb160ea764157c5d', 'Nurul Aini, S.Pd', 'GURU', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mutasi_siswa`
--

CREATE TABLE `mutasi_siswa` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `jenis` varchar(20) NOT NULL,
  `dari_kelas_id` int DEFAULT NULL,
  `ke_kelas_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(200) DEFAULT NULL,
  `oleh` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id` int NOT NULL,
  `tahun_ajaran_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `tugas` decimal(5,2) DEFAULT NULL,
  `uts` decimal(5,2) DEFAULT NULL,
  `uas` decimal(5,2) DEFAULT NULL,
  `diubah_oleh` varchar(100) DEFAULT NULL,
  `diubah_pada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nilai`
--

INSERT INTO `nilai` (`id`, `tahun_ajaran_id`, `siswa_id`, `mapel_id`, `tugas`, `uts`, `uas`, `diubah_oleh`, `diubah_pada`) VALUES
(1, 2, 8, 2, '86.00', '71.00', '88.00', 'Seed', '2026-11-09 15:35:24'),
(2, 2, 7, 2, '79.00', '60.00', '75.00', 'Seed', '2026-11-09 15:35:24'),
(3, 2, 6, 2, '72.00', '90.00', '62.00', 'Seed', '2026-11-09 15:35:24'),
(4, 2, 5, 2, '65.00', '79.00', '88.00', 'Seed', '2026-11-09 15:35:24'),
(5, 2, 4, 2, '94.00', '68.00', '75.00', 'Seed', '2026-11-09 15:35:24'),
(6, 2, 3, 2, '87.00', '57.00', '62.00', 'Seed', '2026-11-09 15:35:24'),
(7, 2, 2, 2, '80.00', '87.00', '88.00', 'Seed', '2026-11-09 15:35:24'),
(8, 2, 1, 2, '73.00', '76.00', '75.00', 'Seed', '2026-11-09 15:35:24'),
(9, 2, 8, 8, '68.00', '60.00', '61.00', 'Seed', '2026-11-09 15:35:24'),
(10, 2, 7, 8, '61.00', '90.00', '87.00', 'Seed', '2026-11-09 15:35:24'),
(11, 2, 6, 8, '90.00', '79.00', '74.00', 'Seed', '2026-11-09 15:35:24'),
(12, 2, 5, 8, '83.00', '68.00', '61.00', 'Seed', '2026-11-09 15:35:24'),
(13, 2, 4, 8, '76.00', '57.00', '87.00', 'Seed', '2026-11-09 15:35:24'),
(14, 2, 3, 8, '69.00', '87.00', '74.00', 'Seed', '2026-11-09 15:35:24'),
(15, 2, 2, 8, '62.00', '76.00', '61.00', 'Seed', '2026-11-09 15:35:24'),
(16, 2, 1, 8, '91.00', '65.00', '87.00', 'Seed', '2026-11-09 15:35:24'),
(17, 2, 8, 7, '65.00', '55.00', '59.00', 'Seed', '2026-11-09 15:35:24'),
(18, 2, 7, 7, '94.00', '85.00', '85.00', 'Seed', '2026-11-09 15:35:24'),
(19, 2, 6, 7, '87.00', '74.00', '72.00', 'Seed', '2026-11-09 15:35:24'),
(20, 2, 5, 7, '80.00', '63.00', '59.00', 'Seed', '2026-11-09 15:35:24'),
(21, 2, 4, 7, '73.00', '93.00', '85.00', 'Seed', '2026-11-09 15:35:24'),
(22, 2, 3, 7, '66.00', '82.00', '72.00', 'Seed', '2026-11-09 15:35:24'),
(23, 2, 2, 7, '95.00', '71.00', '59.00', 'Seed', '2026-11-09 15:35:24'),
(24, 2, 1, 7, '88.00', '60.00', '85.00', 'Seed', '2026-11-09 15:35:24'),
(25, 2, 8, 12, '80.00', '80.00', '69.00', 'Seed', '2026-11-09 15:35:24'),
(26, 2, 7, 12, '73.00', '69.00', '95.00', 'Seed', '2026-11-09 15:35:24'),
(27, 2, 6, 12, '66.00', '58.00', '82.00', 'Seed', '2026-11-09 15:35:24'),
(28, 2, 5, 12, '95.00', '88.00', '69.00', 'Seed', '2026-11-09 15:35:24'),
(29, 2, 4, 12, '88.00', '77.00', '95.00', 'Seed', '2026-11-09 15:35:24'),
(30, 2, 3, 12, '81.00', '66.00', '82.00', 'Seed', '2026-11-09 15:35:24'),
(31, 2, 2, 12, '74.00', '55.00', '69.00', 'Seed', '2026-11-09 15:35:24'),
(32, 2, 1, 12, '67.00', '85.00', '95.00', 'Seed', '2026-11-09 15:35:24'),
(33, 2, 8, 3, '89.00', '76.00', '90.00', 'Seed', '2026-11-09 15:35:24'),
(34, 2, 7, 3, '82.00', '65.00', '77.00', 'Seed', '2026-11-09 15:35:24'),
(35, 2, 6, 3, '75.00', '95.00', '64.00', 'Seed', '2026-11-09 15:35:24'),
(36, 2, 5, 3, '68.00', '84.00', '90.00', 'Seed', '2026-11-09 15:35:24'),
(37, 2, 4, 3, '61.00', '73.00', '77.00', 'Seed', '2026-11-09 15:35:24'),
(38, 2, 3, 3, '90.00', '62.00', '64.00', 'Seed', '2026-11-09 15:35:24'),
(39, 2, 2, 3, '83.00', '92.00', '90.00', 'Seed', '2026-11-09 15:35:24'),
(40, 2, 1, 3, '76.00', '81.00', '77.00', 'Seed', '2026-11-09 15:35:24'),
(41, 2, 8, 9, '71.00', '65.00', '63.00', 'Seed', '2026-11-09 15:35:24'),
(42, 2, 7, 9, '64.00', '95.00', '89.00', 'Seed', '2026-11-09 15:35:24'),
(43, 2, 6, 9, '93.00', '84.00', '76.00', 'Seed', '2026-11-09 15:35:24'),
(44, 2, 5, 9, '86.00', '73.00', '63.00', 'Seed', '2026-11-09 15:35:24'),
(45, 2, 4, 9, '79.00', '62.00', '89.00', 'Seed', '2026-11-09 15:35:24'),
(46, 2, 3, 9, '72.00', '92.00', '76.00', 'Seed', '2026-11-09 15:35:24'),
(47, 2, 2, 9, '65.00', '81.00', '63.00', 'Seed', '2026-11-09 15:35:24'),
(48, 2, 1, 9, '94.00', '70.00', '89.00', 'Seed', '2026-11-09 15:35:24'),
(49, 2, 49, 2, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-10 11:36:15'),
(51, 2, 49, 1, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:18:25'),
(52, 2, 49, 5, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:18:37'),
(53, 2, 49, 7, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:18:48'),
(54, 2, 49, 8, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:00'),
(55, 2, 49, 14, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:11'),
(56, 2, 49, 15, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:20'),
(57, 2, 49, 3, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:30'),
(58, 2, 49, 12, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:41'),
(59, 2, 49, 17, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:19:50'),
(60, 2, 49, 13, '100.00', '100.00', '100.00', 'Administrator Demo', '2026-11-12 11:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggaran_siswa`
--

CREATE TABLE `pelanggaran_siswa` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `jenis_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `poin` int NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `dicatat_oleh` varchar(100) DEFAULT NULL,
  `dibuat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pelanggaran_siswa`
--

INSERT INTO `pelanggaran_siswa` (`id`, `siswa_id`, `jenis_id`, `tanggal`, `poin`, `keterangan`, `dicatat_oleh`, `dibuat`) VALUES
(2, 49, 16, '2026-11-15', 3, 'Otomatis: scan masuk pukul 16:01', 'Sistem', '2026-11-15 16:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `penting` tinyint NOT NULL DEFAULT '0',
  `tanggal` date NOT NULL,
  `berlaku_sampai` date DEFAULT NULL,
  `penulis` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `penting`, `tanggal`, `berlaku_sampai`, `penulis`) VALUES
(1, 'Rapat Dewan Guru', 'Rapat evaluasi bulanan dilaksanakan hari Sabtu pukul 09.00 di ruang guru. Seluruh guru dimohon hadir.', 1, '2026-11-09', '2026-11-23', 'Administrator Demo'),
(2, 'Jadwal Ujian Tengah Semester', 'UTS semester ganjil dimulai dua minggu lagi. Jadwal lengkap dapat dilihat di menu Jadwal Pelajaran.', 0, '2026-11-09', NULL, 'Administrator Demo');

-- --------------------------------------------------------

--
-- Table structure for table `perizinan`
--

CREATE TABLE `perizinan` (
  `id` int NOT NULL,
  `pemohon` varchar(10) NOT NULL,
  `siswa_id` int DEFAULT NULL,
  `guru_id` int DEFAULT NULL,
  `jenis` varchar(10) NOT NULL DEFAULT 'Izin',
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'Menunggu',
  `diajukan_oleh` varchar(100) DEFAULT NULL,
  `dibuat` datetime DEFAULT NULL,
  `diproses_oleh` varchar(100) DEFAULT NULL,
  `diproses_pada` datetime DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `perizinan`
--

INSERT INTO `perizinan` (`id`, `pemohon`, `siswa_id`, `guru_id`, `jenis`, `tgl_mulai`, `tgl_selesai`, `alasan`, `status`, `diajukan_oleh`, `dibuat`, `diproses_oleh`, `diproses_pada`, `catatan`) VALUES
(1, 'Siswa', 1, NULL, 'Sakit', '2026-11-09', '2026-11-10', 'Demam, surat dokter menyusul', 'Menunggu', 'Administrator Demo', '2026-11-09 15:29:55', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rapor_catatan`
--

CREATE TABLE `rapor_catatan` (
  `id` int NOT NULL,
  `tahun_ajaran_id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `sikap` varchar(20) DEFAULT NULL,
  `catatan` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rapor_catatan`
--

INSERT INTO `rapor_catatan` (`id`, `tahun_ajaran_id`, `siswa_id`, `sikap`, `catatan`) VALUES
(1, 2, 49, 'Sangat Baik', 'Anjay');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL DEFAULT 'L',
  `kelas_id` int DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `nama_wali` varchar(120) DEFAULT NULL,
  `no_hp_wali` varchar(30) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'Active',
  `foto` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `kelas_id`, `tanggal_lahir`, `alamat`, `nama_wali`, `no_hp_wali`, `status`, `foto`) VALUES
(1, '2526001', '0034256684', 'Reza Safitri', 'L', 1, '2010-02-19', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Fauziah', '081350000733', 'Active', NULL),
(2, '2526002', '0023076910', 'Dewi Hakim', 'P', 1, '2010-09-23', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hidayat', '081350001466', 'Active', NULL),
(3, '2526003', '0037643310', 'Muhammad Utami', 'L', 1, '2010-08-22', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Safitri', '081350002199', 'Active', NULL),
(4, '2526004', '0072492024', 'Intan Setiawan', 'P', 1, '2010-10-15', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hakim', '081350002932', 'Active', NULL),
(5, '2526005', '0034127884', 'Reza Firmansyah', 'L', 1, '2010-12-25', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Firmansyah', '081350003665', 'Active', NULL),
(6, '2526006', '0050298754', 'Siti Fauziah', 'P', 1, '2010-09-16', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Setiawan', '081350004398', 'Active', NULL),
(7, '2526007', '0091733095', 'Zidan Permana', 'L', 1, '2010-02-04', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Amelia', '081350005131', 'Active', NULL),
(8, '2526008', '0055909953', 'Intan Wibowo', 'P', 1, '2010-03-16', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Lestari', '081350005864', 'Active', NULL),
(9, '2526009', '0084903659', 'Muhammad Hidayat', 'L', 2, '2010-10-26', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Setiawan', '081350006597', 'Active', NULL),
(10, '2526010', '0089774974', 'Zahra Hakim', 'P', 2, '2010-08-19', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wulandari', '081350007330', 'Active', NULL),
(11, '2526011', '0046230636', 'Rizky Hidayat', 'L', 2, '2010-08-23', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hidayat', '081350008063', 'Active', NULL),
(12, '2526012', '0096856164', 'Nur Permana', 'P', 2, '2010-10-22', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wulandari', '081350008796', 'Active', NULL),
(13, '2526013', '0099745048', 'Reza Anggraini', 'L', 2, '2010-06-01', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wulandari', '081350009529', 'Active', NULL),
(14, '2526014', '0091996233', 'Alya Wibowo', 'P', 2, '2010-02-16', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Saputra', '081350010262', 'Active', NULL),
(15, '2526015', '0027359750', 'Naufal Permana', 'L', 2, '2010-12-08', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Anggraini', '081350010995', 'Active', NULL),
(16, '2526016', '0020815439', 'Fitri Rahmawati', 'P', 2, '2010-03-15', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Anggraini', '081350011728', 'Active', NULL),
(17, '2526017', '0067783637', 'Alif Nugraha', 'L', 3, '2009-09-09', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Lestari', '081350012461', 'Active', NULL),
(18, '2526018', '0040970943', 'Alya Anggraini', 'P', 3, '2009-03-03', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wibowo', '081350013194', 'Active', NULL),
(19, '2526019', '0098384612', 'Dimas Firmansyah', 'L', 3, '2009-04-01', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Rahmawati', '081350013927', 'Active', NULL),
(20, '2526020', '0047840101', 'Salsabila Kusuma', 'P', 3, '2009-01-05', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Lestari', '081350014660', 'Active', NULL),
(21, '2526021', '0086013032', 'Ilham Utami', 'L', 3, '2009-06-05', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Amelia', '081350015393', 'Active', NULL),
(22, '2526022', '0085064182', 'Nur Wulandari', 'P', 3, '2009-07-13', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Anggraini', '081350016126', 'Active', NULL),
(23, '2526023', '0074628898', 'Raka Ramadhan', 'L', 3, '2009-11-13', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Saputra', '081350016859', 'Active', NULL),
(24, '2526024', '0038019720', 'Dewi Hidayat', 'P', 3, '2009-08-06', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Ramadhan', '081350017592', 'Active', NULL),
(25, '2526025', '0017056578', 'Hafiz Utami', 'L', 4, '2009-02-01', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Fauziah', '081350018325', 'Active', NULL),
(26, '2526026', '0023618316', 'Annisa Safitri', 'P', 4, '2009-06-20', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Pratama', '081350019058', 'Active', NULL),
(27, '2526027', '0092418944', 'Rizky Maulana', 'L', 4, '2009-07-05', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Kusuma', '081350019791', 'Active', NULL),
(28, '2526028', '0058877189', 'Alya Utami', 'P', 4, '2009-08-04', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Ramadhan', '081350020524', 'Active', NULL),
(29, '2526029', '0074477539', 'Arif Wulandari', 'L', 4, '2009-08-10', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hidayat', '081350021257', 'Active', NULL),
(30, '2526030', '0055987803', 'Annisa Ramadhan', 'P', 4, '2009-12-09', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Rahmawati', '081350021990', 'Active', NULL),
(31, '2526031', '0013099855', 'Bagas Amelia', 'L', 4, '2009-04-17', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hakim', '081350022723', 'Active', NULL),
(32, '2526032', '0050008920', 'Aisyah Amelia', 'P', 4, '2009-11-28', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hidayat', '081350023456', 'Active', NULL),
(33, '2526033', '0059217612', 'Alif Amelia', 'L', 5, '2008-03-12', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Firmansyah', '081350024189', 'Active', NULL),
(34, '2526034', '0092306098', 'Zahra Firmansyah', 'P', 5, '2008-04-26', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Firmansyah', '081350024922', 'Active', NULL),
(35, '2526035', '0036832537', 'Raka Firmansyah', 'L', 5, '2008-09-16', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hakim', '081350025655', 'Active', NULL),
(36, '2526036', '0047502921', 'Aisyah Pratama', 'P', 5, '2008-08-09', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Maulana', '081350026388', 'Active', NULL),
(37, '2526037', '0056911734', 'Ilham Wulandari', 'L', 5, '2008-06-03', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Firmansyah', '081350027121', 'Active', NULL),
(38, '2526038', '0073093067', 'Putri Firmansyah', 'P', 5, '2008-04-11', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Maulana', '081350027854', 'Active', NULL),
(39, '2526039', '0091907998', 'Arif Utami', 'L', 5, '2008-01-16', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hakim', '081350028587', 'Active', NULL),
(40, '2526040', '0062148384', 'Siti Ramadhan', 'P', 5, '2008-12-25', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Maulana', '081350029320', 'Active', NULL),
(41, '2526041', '0068240437', 'Arif Wibowo', 'L', 6, '2008-11-11', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hidayat', '081350030053', 'Active', NULL),
(42, '2526042', '0063873226', 'Fitri Wulandari', 'P', 6, '2008-12-03', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wibowo', '081350030786', 'Active', NULL),
(43, '2526043', '0013697544', 'Bagas Nugraha', 'L', 6, '2008-03-19', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Wulandari', '081350031519', 'Active', NULL),
(44, '2526044', '0089976351', 'Annisa Utami', 'P', 6, '2008-08-22', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Hakim', '081350032252', 'Active', NULL),
(45, '2526045', '0083589642', 'Dimas Safitri', 'L', 6, '2008-03-01', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Pratama', '081350032985', 'Active', NULL),
(46, '2526046', '0028689916', 'Putri Amelia', 'P', 6, '2008-07-28', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Maulana', '081350033718', 'Active', NULL),
(47, '2526047', '0043800696', 'Naufal Pratama', 'L', 6, '2008-04-10', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Amelia', '081350034451', 'Active', NULL),
(48, '2526048', '0053753544', 'Rani Fauziah', 'P', 6, '2008-05-18', 'Desa Contoh, Telagasari, Karawang', 'Bpk/Ibu Lestari', '081350035184', 'Active', NULL),
(49, '101010', '202020', 'Ammar Zahran herlambang', 'L', 5, '2006-05-21', 'jl.anjay gurinjany naik bajay', 'Bpk. Anjay', '08123456789', 'Active', 'siswa_6f5d746248e1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int NOT NULL,
  `nama` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `aktif` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `nama`, `semester`, `tgl_mulai`, `tgl_selesai`, `aktif`) VALUES
(1, '2025/2026', 'Genap', '2026-01-05', '2026-06-20', 0),
(2, '2026/2027', 'Ganjil', '2026-07-13', '2026-12-19', 1),
(3, '2027/2028', 'Ganjil', '2026-11-12', '2027-05-12', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_abs_guru` (`guru_id`,`tanggal`);

--
-- Indexes for table `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_abs_siswa` (`siswa_id`,`tanggal`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `inventaris_barang`
--
ALTER TABLE `inventaris_barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `inventaris_pinjam`
--
ALTER TABLE `inventaris_pinjam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barang_id` (`barang_id`,`status`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`,`kelas_id`,`hari`),
  ADD KEY `guru_id` (`guru_id`,`hari`);

--
-- Indexes for table `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `login_log`
--
ALTER TABLE `login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`login_time`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `master_cost_control`
--
ALTER TABLE `master_cost_control`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_cost_control_file`
--
ALTER TABLE `master_cost_control_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cost_id` (`cost_id`);

--
-- Indexes for table `master_menu_akses`
--
ALTER TABLE `master_menu_akses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_menu` (`target_type`,`target_key`,`page_key`);

--
-- Indexes for table `master_user`
--
ALTER TABLE `master_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- Indexes for table `mutasi_siswa`
--
ALTER TABLE `mutasi_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nilai` (`tahun_ajaran_id`,`siswa_id`,`mapel_id`),
  ADD KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `pelanggaran_siswa`
--
ALTER TABLE `pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`,`tanggal`),
  ADD KEY `jenis_id` (`jenis_id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perizinan`
--
ALTER TABLE `perizinan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `rapor_catatan`
--
ALTER TABLE `rapor_catatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rc` (`tahun_ajaran_id`,`siswa_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ta` (`nama`,`semester`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventaris_barang`
--
ALTER TABLE `inventaris_barang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventaris_pinjam`
--
ALTER TABLE `inventaris_pinjam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_log`
--
ALTER TABLE `login_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `master_cost_control`
--
ALTER TABLE `master_cost_control`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_cost_control_file`
--
ALTER TABLE `master_cost_control_file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_menu_akses`
--
ALTER TABLE `master_menu_akses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `master_user`
--
ALTER TABLE `master_user`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mutasi_siswa`
--
ALTER TABLE `mutasi_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `pelanggaran_siswa`
--
ALTER TABLE `pelanggaran_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `perizinan`
--
ALTER TABLE `perizinan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rapor_catatan`
--
ALTER TABLE `rapor_catatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
