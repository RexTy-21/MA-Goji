<?php
// Router ?page=...
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/library/helpers.php';
require_once __DIR__ . '/library/access.php';
if (empty($_SESSION['SES_LOGIN'])) {
    header('Location: login.php');
    exit;
}

$routes = [
    'Dashboard' => 'pages/home.php',
    'Data-Siswa' => 'pages/siswa.php',
    'Data-Guru' => 'pages/guru.php',
    'Data-Kelas' => 'pages/kelas.php',
    'Absen-Scan' => 'pages/absen_scan.php',
    'Absen-Siswa' => 'pages/absen_siswa.php',
    'Absen-Guru' => 'pages/absen_guru.php',
    'Cetak-Kartu' => 'pages/kartu.php',
    'Profile' => 'pages/profile.php',
    'User-Guide' => 'pages/help.php',
    'Help' => 'pages/help.php',
    'Users' => 'pages/users.php',
    'Upload-File' => 'pages/upload_file.php',
    'Tahun-Ajaran' => 'pages/tahun_ajaran.php',
    'Mata-Pelajaran' => 'pages/mapel.php',
    'Jadwal' => 'pages/jadwal.php',
    'Perizinan' => 'pages/perizinan.php',
    'Pengumuman' => 'pages/pengumuman.php',
    'Kenaikan-Kelas' => 'pages/kenaikan.php',
    'Input-Nilai' => 'pages/nilai.php',
    'Rapor' => 'pages/rapor.php',
    'Cetak-Rapor' => 'pages/cetak_rapor.php',
    'Laporan' => 'pages/laporan.php',
];
$page = $_GET['page'] ?? 'Dashboard';

if ($page === 'Revert') {   // kembali dari "masuk sebagai user lain"
    if (isset($_SESSION['MASTER_LOGIN_SES'])) {
        $backup = $_SESSION['MASTER_LOGIN_SES'];
        $_SESSION = $backup;
    }
    header('Location: ?page=Users');
    exit;
}
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'Dashboard';
}
if (!canAccess($page)) {
    http_response_code(403);
    include 'header.php';
    echo '<div class="right_col" role="main"><div class="x_panel" style="text-align:center;padding:40px"><h2><i class="fa fa-lock"></i> Akses ditolak</h2><p>Halaman ini tidak tersedia untuk peran Anda.</p><a href="?page=Dashboard" class="btn btn-primary">Kembali</a></div></div>';
    include 'footer.php';
    exit;
}
require __DIR__ . '/' . $routes[$page];
