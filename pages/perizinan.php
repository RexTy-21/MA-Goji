<?php
// Perizinan siswa & guru (izin / sakit) + persetujuan -> otomatis masuk rekap absensi
$db = $koneksidb;
$isMgmt = in_array(role(), mgmtRoles(), true);
$myGuru = (int)($_SESSION['SES_GURU_ID'] ?? 0);
$msg = '';

function izinTerapkan($db, $z) {   // tulis ke absensi untuk tiap tanggal (Senin-Sabtu, maks 60 hari), tidak menimpa yang sudah scan
    $d = new DateTime($z['tgl_mulai']); $end = new DateTime($z['tgl_selesai']); $n = 0;
    $ket = mb_substr($z['jenis'] . ': ' . $z['alasan'], 0, 100);
    while ($d <= $end && $n < 60) {
        if ($d->format('N') != 7) {
            $tgl = $d->format('Y-m-d');
            if ($z['pemohon'] === 'Siswa')
                qrun($db, "INSERT INTO absensi_siswa (siswa_id, tanggal, status, keterangan) VALUES (?,?,?,?)
                           ON DUPLICATE KEY UPDATE status = IF(jam_masuk IS NULL, VALUES(status), status), keterangan = IF(jam_masuk IS NULL, VALUES(keterangan), keterangan)", 'isss', [$z['siswa_id'], $tgl, $z['jenis'], $ket]);
            else
                qrun($db, "INSERT INTO absensi_guru (guru_id, tanggal, status, keterangan) VALUES (?,?,?,?)
                           ON DUPLICATE KEY UPDATE status = IF(jam_masuk IS NULL, VALUES(status), status), keterangan = IF(jam_masuk IS NULL, VALUES(keterangan), keterangan)", 'isss', [$z['guru_id'], $tgl, $z['jenis'], $ket]);
        }
        $d->modify('+1 day'); $n++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $id = (int)$f('id');
    if ($act === 'add') {
        $pem = $f('pemohon') === 'Siswa' ? 'Siswa' : 'Guru';
        $sid = (int)$f('siswa_id') ?: null; $gid = (int)$f('guru_id') ?: null;
        if (!$isMgmt) { $pem = 'Guru'; $gid = $myGuru ?: null; $sid = null; }   // guru hanya untuk dirinya sendiri
        $jenis = $f('jenis') === 'Sakit' ? 'Sakit' : 'Izin';
        $a = $f('tgl_mulai'); $b = $f('tgl_selesai') ?: $a;
        $ok = preg_match('/^\d{4}-\d{2}-\d{2}$/', $a) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $b) && $a <= $b && (($pem === 'Siswa' && $sid) || ($pem === 'Guru' && $gid));
        if ($ok) { qrun($db, "INSERT INTO perizinan (pemohon, siswa_id, guru_id, jenis, tgl_mulai, tgl_selesai, alasan, status, diajukan_oleh, dibuat) VALUES (?,?,?,?,?,?,?,'Menunggu',?,NOW())",
                'siiissss', [$pem, $pem === 'Siswa' ? $sid : null, $pem === 'Guru' ? $gid : null, $jenis, $a, $b, mb_substr($f('alasan'), 0, 255), namaUser()]); $msg = 'Pengajuan dikirim.'; }
        else $msg = 'Data pengajuan tidak lengkap / tanggal salah.';
    } elseif (in_array($act, ['setuju', 'tolak'], true) && $isMgmt && $id > 0) {
        $z = qone($db, "SELECT * FROM perizinan WHERE id = ? AND status = 'Menunggu'", 'i', [$id]);
        if ($z) {
            $st = $act === 'setuju' ? 'Disetujui' : 'Ditolak';
            qrun($db, "UPDATE perizinan SET status=?, diproses_oleh=?, diproses_pada=NOW(), catatan=? WHERE id=?", 'sssi', [$st, namaUser(), mb_substr($f('catatan'), 0, 255), $id]);
            if ($st === 'Disetujui') izinTerapkan($db, $z);
            $msg = 'Pengajuan ' . strtolower($st) . '.';
        }
    } elseif ($act === 'del' && $id > 0) {
        $z = qone($db, "SELECT * FROM perizinan WHERE id = ?", 'i', [$id]);
        if ($z && $z['status'] === 'Menunggu' && ($isMgmt || ((int)$z['guru_id'] === $myGuru && $myGuru))) { qrun($db, "DELETE FROM perizinan WHERE id = ?", 'i', [$id]); $msg = 'Pengajuan dihapus.'; }
    }
    header('Location: ?page=Perizinan&msg=' . urlencode($msg)); exit;
}
$fSt = $_GET['status'] ?? 'Menunggu';
if (!in_array($fSt, ['Menunggu', 'Disetujui', 'Ditolak', ''], true)) $fSt = 'Menunggu';
$where = []; $types = ''; $p = [];
if ($fSt !== '') { $where[] = 'z.status = ?'; $types .= 's'; $p[] = $fSt; }
if (!$isMgmt) { $where[] = 'z.guru_id = ?'; $types .= 'i'; $p[] = $myGuru; }
$rows = qall($db, "SELECT z.*, s.nama AS siswa, k.nama AS kelas, g.nama AS guru FROM perizinan z
    LEFT JOIN siswa s ON s.id = z.siswa_id LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN guru g ON g.id = z.guru_id
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . " ORDER BY z.id DESC LIMIT 200", $types, $p);
$siswaList = $isMgmt ? qall($db, "SELECT s.id, s.nama, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.status='Active' ORDER BY k.nama, s.nama") : [];
$guruList = $isMgmt ? qall($db, "SELECT id, nama FROM guru WHERE status='Active' ORDER BY nama") : [];
$bdg = ['Menunggu' => 'warning', 'Disetujui' => 'success', 'Ditolak' => 'danger'];
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Perizinan <small>izin &amp; sakit</small></h3></div>
    <div class="title_right"><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Ajukan Izin</button></div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="x_panel"><div class="x_content table-responsive">
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Perizinan">
      <label>Status&nbsp;</label><select name="status" class="form-control input-sm" onchange="this.form.submit()">
        <?php foreach (['Menunggu', 'Disetujui', 'Ditolak', ''] as $s) echo '<option value="' . $s . '"' . ($fSt === $s ? ' selected' : '') . '>' . ($s ?: 'Semua') . '</option>'; ?></select></form>
    <table class="table table-bordered table-striped">
      <thead><tr><th>No</th><th>Pemohon</th><th>Jenis</th><th>Tanggal</th><th>Alasan</th><th>Status</th><th>Diproses</th><th>Aksi</th></tr></thead>
      <tbody><?php if (!$rows) echo '<tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>'; ?>
      <?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td>
          <td><b><?= e($r['pemohon'] === 'Siswa' ? $r['siswa'] : $r['guru']) ?></b><br><small class="text-muted"><?= $r['pemohon'] === 'Siswa' ? 'Siswa ' . e($r['kelas']) : 'Guru' ?></small></td>
          <td><?= e($r['jenis']) ?></td>
          <td><?= date('d/m/Y', strtotime($r['tgl_mulai'])) ?><?= $r['tgl_selesai'] !== $r['tgl_mulai'] ? ' - ' . date('d/m/Y', strtotime($r['tgl_selesai'])) : '' ?></td>
          <td><?= e($r['alasan']) ?><br><small class="text-muted">diajukan <?= e($r['diajukan_oleh']) ?></small></td>
          <td><span class="label label-<?= $bdg[$r['status']] ?? 'default' ?>"><?= e($r['status']) ?></span></td>
          <td><small><?= $r['diproses_oleh'] ? e($r['diproses_oleh']) . '<br>' . e($r['catatan']) : '-' ?></small></td>
          <td><?php if ($r['status'] === 'Menunggu'): ?>
            <form method="POST" style="display:inline"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <?php if ($isMgmt): ?><input type="hidden" name="catatan" class="catatan">
                <button name="act" value="setuju" class="btn btn-success btn-xs" onclick="return confirm('Setujui? Absensi akan otomatis terisi.')">Setujui</button>
                <button name="act" value="tolak" class="btn btn-danger btn-xs" onclick="var c=prompt('Alasan penolakan (opsional):');if(c===null)return false;this.form.catatan.value=c;return true">Tolak</button><?php endif; ?>
              <?php if ($isMgmt || ($myGuru && (int)$r['guru_id'] === $myGuru)): ?><button name="act" value="del" class="btn btn-default btn-xs" onclick="return confirm('Hapus pengajuan?')"><i class="fa fa-trash"></i></button><?php endif; ?>
            </form><?php endif; ?></td></tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Ajukan Izin / Sakit</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="add">
    <?php if ($isMgmt): ?>
    <div class="form-group"><label>Pemohon</label><select class="form-control" name="pemohon" id="m_pem"><option>Siswa</option><option>Guru</option></select></div>
    <div class="form-group" id="g_siswa"><label>Siswa</label><select class="form-control" name="siswa_id" style="width:100%"><option value="">- pilih -</option>
      <?php foreach ($siswaList as $s) echo '<option value="' . $s['id'] . '">' . e($s['nama'] . ' (' . $s['kelas'] . ')') . '</option>'; ?></select></div>
    <div class="form-group" id="g_guru" style="display:none"><label>Guru</label><select class="form-control" name="guru_id"><option value="">- pilih -</option>
      <?php foreach ($guruList as $g) echo '<option value="' . $g['id'] . '">' . e($g['nama']) . '</option>'; ?></select></div>
    <?php elseif (!$myGuru): ?><div class="alert alert-warning">Akun Anda belum terhubung ke data guru, hubungi admin.</div><?php endif; ?>
    <div class="form-group"><label>Jenis</label><select class="form-control" name="jenis"><option>Izin</option><option>Sakit</option></select></div>
    <div class="row"><div class="col-xs-6"><label>Dari tanggal</label><input type="date" class="form-control" name="tgl_mulai" required value="<?= date('Y-m-d') ?>"></div>
      <div class="col-xs-6"><label>Sampai (opsional)</label><input type="date" class="form-control" name="tgl_selesai"></div></div><br>
    <div class="form-group"><label>Alasan</label><textarea class="form-control" name="alasan" rows="2" maxlength="255"></textarea></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Kirim</button></div>
</form></div></div>
<?php include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#mdl').modal('show'); });
  $('#m_pem').on('change', function () { var s = this.value === 'Siswa'; $('#g_siswa').toggle(s); $('#g_guru').toggle(!s); });
});
</script>