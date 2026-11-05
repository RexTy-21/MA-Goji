<?php
// Rekap Absensi Siswa per bulan + filter kelas + input izin/sakit/alpa manual
$db = $koneksidb;
$kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$bolehInput = canEdit() || in_array(role(), ['GURU', 'WAKIL KEPALA'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bolehInput) {
    $sid = (int)($_POST['siswa_id'] ?? 0); $tgl = $_POST['tanggal'] ?? date('Y-m-d');
    $stt = in_array($_POST['status'] ?? '', ['Hadir', 'Izin', 'Sakit', 'Alpa'], true) ? $_POST['status'] : 'Izin';
    $ket = trim($_POST['keterangan'] ?? '');
    if ($sid > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
        qrun($db, "INSERT INTO absensi_siswa (siswa_id, tanggal, status, keterangan) VALUES (?,?,?,?)
                   ON DUPLICATE KEY UPDATE status = VALUES(status), keterangan = VALUES(keterangan)", 'isss', [$sid, $tgl, $stt, $ket]);
    }
    header('Location: ?page=Absen-Siswa&bulan=' . urlencode(substr($tgl, 0, 7)) . '&kelas=' . (int)($_GET['kelas'] ?? 0) . '&msg=' . urlencode('Absensi disimpan.')); exit;
}
$bulanIni = date('Y-m'); $bulan = $_GET['bulan'] ?? $bulanIni;
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) $bulan = $bulanIni;
$awal = $bulan . '-01'; $akhir = date('Y-m-d', strtotime($awal . ' +1 month'));
$fKelas = (int)($_GET['kelas'] ?? 0);
$sql = "SELECT a.tanggal, a.jam_masuk, a.jam_pulang, a.status, a.keterangan, s.nis, s.nama, k.nama AS kelas
        FROM absensi_siswa a JOIN siswa s ON s.id = a.siswa_id LEFT JOIN kelas k ON k.id = s.kelas_id
        WHERE a.tanggal >= ? AND a.tanggal < ?" . ($fKelas ? ' AND s.kelas_id = ?' : '') . " ORDER BY a.tanggal DESC, k.nama, s.nama";
$rows = $fKelas ? qall($db, $sql, 'ssi', [$awal, $akhir, $fKelas]) : qall($db, $sql, 'ss', [$awal, $akhir]);
$cnt = ['Hadir' => 0, 'Terlambat' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0];
foreach ($rows as $r) if (isset($cnt[$r['status']])) $cnt[$r['status']]++;
$lbl = ['Hadir' => 'success', 'Terlambat' => 'warning', 'Izin' => 'info', 'Sakit' => 'primary', 'Alpa' => 'danger'];
$siswaJs = qall($db, "SELECT id, nama, kelas_id FROM siswa WHERE status = 'Active' ORDER BY nama");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Absensi Siswa</h3></div>
    <div class="title_right"><a href="?page=Absen-Scan" class="btn btn-success btn-sm"><i class="fa fa-qrcode"></i> Scan Absen</a>
      <?php if ($bolehInput): ?><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlManual"><i class="fa fa-pencil"></i> Input Izin / Sakit / Alpa</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:10px"><input type="hidden" name="page" value="Absen-Siswa">
      <label>Periode&nbsp;</label><input type="month" name="bulan" class="form-control input-sm" max="<?= $bulanIni ?>" value="<?= e($bulan) ?>" onchange="this.form.submit()">
      <label style="margin-left:10px">Kelas&nbsp;</label><select name="kelas" class="form-control input-sm" onchange="this.form.submit()"><option value="0">Semua Kelas</option>
        <?php foreach ($kelasList as $k): ?><option value="<?= $k['id'] ?>"<?= $fKelas === (int)$k['id'] ? ' selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?></select>
      <span style="margin-left:14px"><?php foreach ($cnt as $k => $v): ?><span class="label label-<?= $lbl[$k] ?>"><?= $k ?> <?= $v ?></span> <?php endforeach; ?></span>
    </form>
    <div class="table-responsive"><table id="tbl" class="table table-bordered table-striped" width="100%">
      <thead><tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Keterangan</th></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
          <td><?= e(substr((string)$r['jam_masuk'], 0, 5)) ?: '-' ?></td><td><?= e(substr((string)$r['jam_pulang'], 0, 5)) ?: '-' ?></td>
          <td><span class="label label-<?= $lbl[$r['status']] ?? 'default' ?>"><?= e($r['status']) ?></span></td><td><?= e($r['keterangan']) ?></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  </div></div>
</div>
<?php if ($bolehInput): ?>
<div class="modal fade" id="mdlManual" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Input Absensi Manual</h4></div>
  <div class="modal-body">
    <div class="form-group"><label>Kelas</label><select class="form-control" id="mKelas"><?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '">' . e($k['nama']) . '</option>'; ?></select></div>
    <div class="form-group"><label>Siswa</label><select class="form-control" name="siswa_id" id="mSiswa" required></select></div>
    <div class="form-group"><label>Tanggal</label><input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>
    <div class="form-group"><label>Status</label><select class="form-control" name="status"><option>Izin</option><option>Sakit</option><option>Alpa</option><option>Hadir</option></select></div>
    <div class="form-group"><label>Keterangan</label><input class="form-control" name="keterangan" placeholder="mis. surat dokter"></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#tbl').DataTable({ dom: 'lBfrtip', pageLength: 25, buttons: ['copy', 'csv', 'excel', 'print'] });
  var SISWA = <?= json_encode($siswaJs, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  function isi() {
    var k = parseInt($('#mKelas').val(), 10), h = '';
    SISWA.forEach(function (s) { if (parseInt(s.kelas_id, 10) === k) h += '<option value="' + s.id + '">' + $('<i>').text(s.nama).html() + '</option>'; });
    $('#mSiswa').html(h);
  }
  $('#mKelas').on('change', isi); isi();
});
</script>
