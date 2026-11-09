<?php
// Hak akses halaman berdasarkan peran (master_user.user_role). Halaman yang tidak terdaftar = semua peran.
function mgmtRoles() { return ['ADMIN', 'KEPALA MADRASAH', 'WAKIL KEPALA', 'TATA USAHA']; }
function accessRules() {
    return [
        'Data-Guru'  => mgmtRoles(),
        'Absen-Guru' => mgmtRoles(),
        'Absen-Scan' => ['ADMIN', 'TATA USAHA', 'WAKIL KEPALA', 'GURU'],
        'Users'      => ['ADMIN'],
        'Tahun-Ajaran' => mgmtRoles(),
    ];
}
function canAccess($page) {
    $rules = accessRules();
    if (!isset($rules[$page])) return true;
    if (role() === 'ADMIN') return true;
    if ($page === 'Users' && isset($_SESSION['MASTER_LOGIN_SES'])) return true;
    return in_array(role(), $rules[$page], true);
}
