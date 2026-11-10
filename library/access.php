<?php
// Hak akses halaman.
// Urutan: (1) ADMIN selalu penuh, (2) pengaturan Master Menu per USER, (3) pengaturan Master Menu per ROLE, (4) aturan bawaan di bawah.
function mgmtRoles() { return ['ADMIN', 'KEPALA MADRASAH', 'WAKIL KEPALA', 'TATA USAHA']; }

// Aturan bawaan (dipakai bila Master Menu belum diatur untuk user/role tersebut). Halaman yang tidak tercantum = semua peran.
function accessRules() {
    return [
        'Data-Guru'      => mgmtRoles(),
        'Absen-Guru'     => mgmtRoles(),
        'Absen-Scan'     => ['ADMIN', 'TATA USAHA', 'WAKIL KEPALA', 'GURU'],
        'Tahun-Ajaran'   => mgmtRoles(),
        'Kenaikan-Kelas' => mgmtRoles(),
        'Users'          => ['ADMIN'],
        'Master-Menu'    => ['ADMIN'],
    ];
}

// Daftar menu (satu sumber untuk sidebar dan layar Master Menu): [judul, ikon, halaman|null, [submenu: [judul, halaman]]|null]
function menuRegistry() {
    return [
        ['Profil Madrasah', 'fa-university', 'Dashboard', null],
        ['Akademik', 'fa-graduation-cap', null, [['Data Siswa', 'Data-Siswa'], ['Data Guru', 'Data-Guru'], ['Data Kelas', 'Data-Kelas'], ['Mata Pelajaran', 'Mata-Pelajaran'], ['Jadwal Pelajaran', 'Jadwal'], ['Tahun Ajaran', 'Tahun-Ajaran'], ['Kenaikan Kelas', 'Kenaikan-Kelas']]],
        ['Absensi', 'fa-qrcode', null, [['Scan Absen', 'Absen-Scan'], ['Absensi Siswa', 'Absen-Siswa'], ['Absensi Guru', 'Absen-Guru']]],
        ['Penilaian', 'fa-pencil-square-o', null, [['Input Nilai', 'Input-Nilai'], ['Rapor', 'Rapor']]],
        ['Layanan', 'fa-bullhorn', null, [['Perizinan', 'Perizinan'], ['Pengumuman', 'Pengumuman']]],
        ['Kesiswaan', 'fa-shield', null, [['Pelanggaran Siswa', 'Pelanggaran']]],
        ['Inventaris', 'fa-archive', 'Inventaris', null],
        ['Laporan', 'fa-bar-chart', 'Laporan', null],
        ['Upload File', 'fa-upload', 'Upload-File', null],
        ['Pengaturan', 'fa-cog', null, [['Kelola User', 'Users'], ['Master Menu', 'Master-Menu']]],
    ];
}

function defaultAccess($page, $role) {
    $rules = accessRules();
    if (!isset($rules[$page])) return true;
    return $role === 'ADMIN' || in_array($role, $rules[$page], true);
}

// Pengaturan Master Menu untuk user yang sedang login: null = belum diatur (pakai bawaan); array = daftar halaman yang diizinkan
function customAccess() {
    static $cache = false;
    if ($cache !== false) return $cache;
    $cache = null;
    $db = $GLOBALS['koneksidb'] ?? null;
    if (!$db) return null;
    $targets = [['USER', (string)(int)($_SESSION['SES_USERID'] ?? 0)], ['ROLE', role()]];
    foreach ($targets as [$t, $k]) {
        $st = mysqli_prepare($db, "SELECT page_key FROM master_menu_akses WHERE target_type = ? AND target_key = ?");
        if (!$st) return $cache = null;          // tabel belum dibuat -> pakai aturan bawaan
        mysqli_stmt_bind_param($st, 'ss', $t, $k); mysqli_stmt_execute($st);
        $rows = []; $res = mysqli_stmt_get_result($st);
        while ($r = mysqli_fetch_row($res)) $rows[] = $r[0];
        if ($rows) return $cache = $rows;
    }
    return $cache = null;
}

function canAccess($page) {
    $alias = ['Cetak-Kartu' => 'Data-Siswa', 'Cetak-Rapor' => 'Rapor', 'Profile' => 'Dashboard', 'Help' => 'Dashboard', 'User-Guide' => 'Dashboard'];
    $page = $alias[$page] ?? $page;
    if ($page === 'Dashboard') return true;
    if ($page === 'Master-Menu') return role() === 'ADMIN';
    if ($page === 'Users') return role() === 'ADMIN' || isset($_SESSION['MASTER_LOGIN_SES']);
    if (role() === 'ADMIN') return true;
    $c = customAccess();
    if ($c !== null) return in_array($page, $c, true);
    return defaultAccess($page, role());
}