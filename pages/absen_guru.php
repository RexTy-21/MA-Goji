<?php
// Rekap Absensi Guru per bulan
$db = $koneksidb;
$bulanIni = date('Y-m'); $bulan = $_GET['bulan'] ?? $bulanIni;
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) $bulan = $bulanIni;
$awal = $bulan . '-01'; $akhir = date('Y-m-d', strtotime($awal . ' +1 month'));
$rows = qall($db, "SELECT a.tanggal, a.jam_masuk, a.jam_pulang, a.status, a.keterangan, g.nip, g.nama, g.jabatan
                   FROM absensi_guru a JOIN guru g ON g.id = a.guru_id
                   WHERE a.tanggal >= ? AND a.tanggal < ? ORDER BY a.tanggal DESC, g.nama", 'ss', [$awal, $akhir]);
$cnt = ['Hadir' => 0, 'Terlambat' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0];
foreach ($rows as $r) if (isset($cnt[$r['status']])) $cnt[$r['status']]++;
$lbl = ['Hadir' => 'success', 'Terlambat' => 'warning', 'Izin' => 'info', 'Sakit' => 'primary', 'Alpa' => 'danger'];
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Absensi Guru</h3></div>
    <div class="title_right"><a href="?page=Absen-Scan" class="btn btn-success btn-sm"><i class="fa fa-qrcode"></i> Scan Absen</a></div></div>
  <div class="clearfix"></div>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:10px"><input type="hidden" name="page" value="Absen-Guru">
      <label>Periode&nbsp;</label><input type="month" name="bulan" class="form-control input-sm" max="<?= $bulanIni ?>" value="<?= e($bulan) ?>" onchange="this.form.submit()">
      <span style="margin-left:14px"><?php foreach ($cnt as $k => $v): ?><span class="label label-<?= $lbl[$k] ?>"><?= $k ?> <?= $v ?></span> <?php endforeach; ?></span>
    </form>
    <div class="table-responsive"><table id="tbl" class="table table-bordered table-striped" width="100%">
      <thead><tr><th>No</th><th>Tanggal</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['jabatan']) ?></td>
          <td><?= e(substr((string)$r['jam_masuk'], 0, 5)) ?: '-' ?></td><td><?= e(substr((string)$r['jam_pulang'], 0, 5)) ?: '-' ?></td>
          <td><span class="label label-<?= $lbl[$r['status']] ?? 'default' ?>"><?= e($r['status']) ?></span></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  </div></div>
</div>
<?php include "footer.php"; ?>
<script>$(function(){ $('#tbl').DataTable({ dom: 'lBfrtip', pageLength: 25, buttons: ['copy','csv','excel','print'] }); });</script>
