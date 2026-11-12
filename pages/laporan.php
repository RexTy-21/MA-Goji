<?php
// Laporan: tampil di layar, cetak (?out=print) dan export Excel (?out=xls)
$db = $koneksidb;
$ta = taAktif($db);
$isMgmt = in_array(role(), mgmtRoles(), true);
$jenisList = ['absen_siswa' => 'Rekap Absensi Siswa (bulanan)', 'absen_guru' => 'Rekap Absensi Guru (bulanan)', 'nilai_kelas' => 'Rekap Nilai Kelas', 'siswa_kelas' => 'Daftar Siswa per Kelas'];
if (!$isMgmt) unset($jenisList['absen_guru']);
$jenis = $_GET['jenis'] ?? 'absen_siswa'; if (!isset($jenisList[$jenis])) $jenis = 'absen_siswa';
$bulan = preg_match('/^\d{4}-\d{2}$/', $_GET['bulan'] ?? '') ? $_GET['bulan'] : date('Y-m');
$kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$fKelas = (int)($_GET['kelas'] ?? 0);
$kelasNama = 'Semua Kelas'; foreach ($kelasList as $k) if ($k['id'] == $fKelas) $kelasNama = 'Kelas ' . $k['nama'];
$blnLabel = bulanList()[(int)substr($bulan, 5, 2)] . ' ' . substr($bulan, 0, 4);
$needKelas = in_array($jenis, ['nilai_kelas', 'siswa_kelas'], true);
$H = []; $R = []; $judul = $jenisList[$jenis]; $sub = ''; $pesan = '';
$pct = fn($h, $t, $i, $s, $a) => ($h + $t + $i + $s + $a) ? round(($h + $t) / ($h + $t + $i + $s + $a) * 100) . '%' : '-';

if ($needKelas && !$fKelas) {
    $pesan = 'Pilih kelas terlebih dahulu.';
} elseif ($jenis === 'absen_siswa') {
    $sub = $kelasNama . ' - ' . $blnLabel;
    $H = ['No', 'NIS', 'Nama', 'Kelas', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', '% Hadir'];
    $sql = "SELECT s.nis, s.nama, k.nama AS kelas, SUM(a.status='Hadir') h, SUM(a.status='Terlambat') t, SUM(a.status='Izin') i, SUM(a.status='Sakit') sk, SUM(a.status='Alpa') al
            FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND DATE_FORMAT(a.tanggal,'%Y-%m') = ?
            WHERE s.status = 'Active'" . ($fKelas ? ' AND s.kelas_id = ?' : '') . " GROUP BY s.id, k.id, k.nama, k.tingkat ORDER BY FIELD(k.tingkat,'X','XI','XII'), k.nama, s.nama";
    foreach ($fKelas ? qall($db, $sql, 'si', [$bulan, $fKelas]) : qall($db, $sql, 's', [$bulan]) as $n => $r)
        $R[] = [$n + 1, $r['nis'], $r['nama'], $r['kelas'], (int)$r['h'], (int)$r['t'], (int)$r['i'], (int)$r['sk'], (int)$r['al'], $pct((int)$r['h'], (int)$r['t'], (int)$r['i'], (int)$r['sk'], (int)$r['al'])];
} elseif ($jenis === 'absen_guru') {
    $sub = $blnLabel;
    $H = ['No', 'NIP', 'Nama', 'Jabatan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', '% Hadir'];
    foreach (qall($db, "SELECT g.nip, g.nama, g.jabatan, SUM(a.status='Hadir') h, SUM(a.status='Terlambat') t, SUM(a.status='Izin') i, SUM(a.status='Sakit') sk, SUM(a.status='Alpa') al
            FROM guru g LEFT JOIN absensi_guru a ON a.guru_id = g.id AND DATE_FORMAT(a.tanggal,'%Y-%m') = ? WHERE g.status = 'Active' GROUP BY g.id ORDER BY g.nama", 's', [$bulan]) as $n => $r)
        $R[] = [$n + 1, $r['nip'], $r['nama'], $r['jabatan'] ?: '-', (int)$r['h'], (int)$r['t'], (int)$r['i'], (int)$r['sk'], (int)$r['al'], $pct((int)$r['h'], (int)$r['t'], (int)$r['i'], (int)$r['sk'], (int)$r['al'])];
} elseif ($jenis === 'siswa_kelas') {
    $sub = $kelasNama;
    $H = ['No', 'NIS', 'NISN', 'Nama', 'L/P', 'Tanggal Lahir', 'Nama Wali', 'No. HP Wali'];
    foreach (qall($db, "SELECT * FROM siswa WHERE kelas_id = ? AND status = 'Active' ORDER BY nama", 'i', [$fKelas]) as $n => $r)
        $R[] = [$n + 1, $r['nis'], $r['nisn'] ?: '-', $r['nama'], $r['jenis_kelamin'], $r['tanggal_lahir'] ? date('d/m/Y', strtotime($r['tanggal_lahir'])) : '-', $r['nama_wali'] ?: '-', $r['no_hp_wali'] ?: '-'];
} elseif ($jenis === 'nilai_kelas') {
    $sub = $kelasNama . ($ta ? ' - ' . $ta['nama'] . ' ' . $ta['semester'] : '');
    if (!$ta) { $pesan = 'Belum ada Tahun Ajaran aktif.'; }
    else {
        $mapel = qall($db, "SELECT DISTINCT m.id, m.nama FROM nilai n JOIN mapel m ON m.id = n.mapel_id JOIN siswa s ON s.id = n.siswa_id WHERE n.tahun_ajaran_id = ? AND s.kelas_id = ? ORDER BY m.nama", 'ii', [$ta['id'], $fKelas]);
        $nl = [];
        foreach (qall($db, "SELECT n.siswa_id, n.mapel_id, n.tugas, n.uts, n.uas FROM nilai n JOIN siswa s ON s.id = n.siswa_id WHERE n.tahun_ajaran_id = ? AND s.kelas_id = ?", 'ii', [$ta['id'], $fKelas]) as $r)
            $nl[$r['siswa_id']][$r['mapel_id']] = nilaiAkhir($r['tugas'], $r['uts'], $r['uas']);
        $H = array_merge(['No', 'NIS', 'Nama'], array_column($mapel, 'nama'), ['Rata-rata']);
        foreach (qall($db, "SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 'Active' ORDER BY nama", 'i', [$fKelas]) as $n => $s) {
            $row = [$n + 1, $s['nis'], $s['nama']]; $sum = 0; $c = 0;
            foreach ($mapel as $m) { $v = $nl[$s['id']][$m['id']] ?? null; $row[] = $v === null ? '-' : $v; if ($v !== null) { $sum += $v; $c++; } }
            $row[] = $c ? number_format($sum / $c, 1) : '-'; $R[] = $row;
        }
    }
}
$out = $_GET['out'] ?? '';
$tbl = function ($border) use ($H, $R) {
    $h = '<table' . ($border ? ' border="1" cellspacing="0" cellpadding="4"' : ' class="table table-bordered table-striped table-condensed"') . '><thead><tr>';
    foreach ($H as $c) $h .= '<th>' . e($c) . '</th>';
    $h .= '</tr></thead><tbody>';
    if (!$R) $h .= '<tr><td colspan="' . max(1, count($H)) . '" style="text-align:center">Tidak ada data.</td></tr>';
    foreach ($R as $r) { $h .= '<tr>'; foreach ($r as $v) $h .= '<td' . (is_string($v) && ctype_digit($v) ? " style=\"mso-number-format:'\\@'\"" : '') . '>' . e($v) . '</td>'; $h .= '</tr>'; }
    return $h . '</tbody></table>';
};
if ($out === 'xls' && $H) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_' . $jenis . '_' . date('Ymd') . '.xls"');
    echo "\xEF\xBB\xBF<html><head><meta charset=\"utf-8\"></head><body><h3>MA Ghoyatul Jihad Karawang - " . e($judul) . '</h3><p>' . e($sub) . '</p>' . $tbl(true) . '</body></html>'; exit;
}
if ($out === 'print' && $H) {
    $kepala = qone($db, "SELECT user_fullname FROM master_user WHERE user_role = 'KEPALA MADRASAH' AND user_status = 1 ORDER BY user_id LIMIT 1");
    ?><!doctype html><html lang="id"><head><meta charset="utf-8"><title><?= e($judul) ?></title>
<style>
  @page{size:A4 landscape;margin:12mm}
  body{position:relative;font-family:Arial,sans-serif;font-size:12px;margin:18px}
  .watermark{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:400px;max-width:70%;opacity:0.07;pointer-events:none;z-index:0;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
  .kop{display:flex;align-items:center;gap:12px;border-bottom:3px double #166534;padding-bottom:6px;margin-bottom:8px;position:relative;z-index:1}
  .kop img{height:54px}.kop h2{margin:0;color:#166534;font-size:17px}
  table{width:100%;border-collapse:collapse;position:relative;z-index:1} th,td{border:1px solid #333;padding:4px 6px} th{background:#dcf3e3}
  .ttd{margin-top:26px;float:right;text-align:center;width:240px;position:relative;z-index:1}
  .bar{margin-bottom:10px}
  @media print{.bar{display:none} .watermark{opacity:0.07!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}
</style></head><body>
<div class="bar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
<img class="watermark" src="assets/img/logo.png" alt="Watermark MA Ghoyatul Jihad">
<div class="kop"><img src="assets/img/logo.png" alt=""><div><h2>MA GHOYATUL JIHAD KARAWANG</h2><div>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif</div></div></div>
<h3 style="text-align:center;margin:6px 0;position:relative;z-index:1"><?= e(strtoupper($judul)) ?></h3><p style="text-align:center;margin:0 0 10px;position:relative;z-index:1"><?= e($sub) ?></p>
<?= $tbl(true) ?>
<div class="ttd">Karawang, <?= date('d') . ' ' . bulanList()[(int)date('n')] . ' ' . date('Y') ?><br>Kepala Madrasah<br><br><br><br><u><?= e($kepala['user_fullname'] ?? '........................') ?></u></div>
</body></html><?php exit;
}
include "header.php";
$qs = http_build_query(['page' => 'Laporan', 'jenis' => $jenis, 'kelas' => $fKelas, 'bulan' => $bulan]);
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Laporan</h3></div>
    <div class="title_right"><?php if ($H): ?><a class="btn btn-default btn-sm" target="_blank" href="?<?= $qs ?>&out=print"><i class="fa fa-print"></i> Cetak</a>
      <a class="btn btn-success btn-sm" href="?<?= $qs ?>&out=xls"><i class="fa fa-file-excel-o"></i> Export Excel</a><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:14px"><input type="hidden" name="page" value="Laporan">
      <select name="jenis" class="form-control" onchange="this.form.submit()"><?php foreach ($jenisList as $k => $v) echo '<option value="' . $k . '"' . ($jenis === $k ? ' selected' : '') . '>' . e($v) . '</option>'; ?></select>
      <select name="kelas" class="form-control" onchange="this.form.submit()"><option value=""><?= $needKelas ? '- pilih kelas -' : 'Semua Kelas' ?></option>
        <?php if ($jenis !== 'absen_guru') foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select>
      <?php if (in_array($jenis, ['absen_siswa', 'absen_guru'], true)): ?><input type="month" name="bulan" class="form-control" value="<?= e($bulan) ?>" onchange="this.form.submit()"><?php endif; ?>
    </form>
    <?php if ($pesan): ?><div class="alert alert-info"><?= e($pesan) ?></div>
    <?php else: ?><h4 style="margin-top:0"><?= e($judul) ?> <small><?= e($sub) ?></small></h4>
      <?php if ($jenis === 'absen_siswa' || $jenis === 'absen_guru'): ?><p class="text-muted"><small>% Hadir = (Hadir + Terlambat) dibagi total hari yang tercatat pada bulan tersebut.</small></p><?php endif; ?>
      <div class="table-responsive"><?= $tbl(false) ?></div><?php endif; ?>
  </div></div>
</div>
<?php include "footer.php"; ?>