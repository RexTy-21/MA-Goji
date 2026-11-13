<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function role() { return strtoupper((string)($_SESSION['SES_ROLE'] ?? '')); }
function canEdit() { return in_array(role(), ['ADMIN', 'TATA USAHA'], true); }
function initials($n) {
    $n = trim((string)$n); if ($n === '') return '?';
    $p = preg_split('/\s+/', $n);
    return strtoupper(mb_substr($p[0], 0, 1) . (isset($p[1]) ? mb_substr($p[1], 0, 1) : ''));
}
function avatarColor($k) { return 'hsl(' . (110 + abs(crc32((string)$k)) % 70) . ',45%,36%)'; }
function qall($db, $sql, $types = '', $p = []) {
    $st = $db->prepare($sql);
    if (!$st) die('Query gagal: ' . e($db->error) . '<br>Pastikan database sudah diimpor (schema.sql dan seed.sql).');
    if ($types) $st->bind_param($types, ...$p);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
function qone($db, $sql, $types = '', $p = []) { $r = qall($db, $sql, $types, $p); return $r[0] ?? null; }
function qrun($db, $sql, $types = '', $p = []) {
    $st = $db->prepare($sql); if (!$st) return false;
    if ($types) $st->bind_param($types, ...$p);
    return $st->execute();
}

// Simpan foto upload ke uploads/foto/. Return nama file, atau null jika tidak ada/tidak valid.
function uploadFoto(string $field, string $prefix): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $f = $_FILES[$field];
    if ($f['size'] > 2 * 1024 * 1024) return null;                       // maks 2 MB
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) return null;                                              // hanya gambar
    $dir = 'uploads/foto/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = $prefix . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $name) ? $name : null;
}

function bulanList() { return [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; }

function taAktif($db) { return qone($db, "SELECT * FROM tahun_ajaran WHERE aktif = 1 ORDER BY id DESC LIMIT 1"); }
function hariList() { return [1=>'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; }
function namaUser() { return $_SESSION['SES_NAMA'] ?? ($_SESSION['SES_USER_NAME'] ?? 'user'); }
function nilaiAkhir($t, $u, $a) {
    if ($t === null || $t === '' || $u === null || $u === '' || $a === null || $a === '') return null;
    return round($t * 0.3 + $u * 0.3 + $a * 0.4, 1);
}
function predikat($n) {
    if ($n === null) return '-';
    return $n >= 90 ? 'A' : ($n >= 80 ? 'B' : ($n >= 70 ? 'C' : 'D'));
}