<?php
// Kenaikan Kelas / Mutasi Siswa (naik/pindah kelas massal, lulus, pindah sekolah, keluar) + riwayat
$db = $koneksidb;
$kelasList = qall($db, "SELECT id, nama, tingkat FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$kMap = []; foreach ($kelasList as $k) $kMap[$k['id']] = $k;
$fKelas = (int)($_GET['kelas'] ?? 0);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $src = (int)($_POST['kelas'] ?? 0);
    $ids = array_values(array_filter(array_map('intval', $_POST['ids'] ?? [])));
    $aksi = $_POST['aksi'] ?? ''; $tujuan = (int)($_POST['tujuan'] ?? 0); $ket = mb_substr(trim($_POST['keterangan'] ?? ''), 0, 200);
    $map = ['lulus' => ['Lulus', 'Lulus'], 'pindah' => ['Pindah Sekolah', 'Pindah'], 'keluar' => ['Keluar', 'Keluar']];
    $n = 0;
    if ($ids && isset($kMap[$src]) && ($aksi === 'kelas' ? isset($kMap[$tujuan]) && $tujuan !== $src : isset($map[$aksi]))) {
        foreach ($ids as $sid) {
            $s = qone($db, "SELECT id FROM siswa WHERE id = ? AND kelas_id = ? AND status = 'Active'", 'ii', [$sid, $src]);
            if (!$s) continue;
            if ($aksi === 'kelas') {
                $jenis = $kMap[$tujuan]['tingkat'] !== $kMap[$src]['tingkat'] ? 'Naik Kelas' : 'Pindah Kelas';
                qrun($db, "UPDATE siswa SET kelas_id = ? WHERE id = ?", 'ii', [$tujuan, $sid]);
                qrun($db, "INSERT INTO mutasi_siswa (siswa_id, jenis, dari_kelas_id, ke_kelas_id, tanggal, keterangan, oleh) VALUES (?,?,?,?,CURDATE(),?,?)", 'isiiss', [$sid, $jenis, $src, $tujuan, $ket, namaUser()]);
            } else {
                qrun($db, "UPDATE siswa SET status = ? WHERE id = ?", 'si', [$map[$aksi][1], $sid]);
                qrun($db, "INSERT INTO mutasi_siswa (siswa_id, jenis, dari_kelas_id, ke_kelas_id, tanggal, keterangan, oleh) VALUES (?,?,?,NULL,CURDATE(),?,?)", 'isiss', [$sid, $map[$aksi][0], $src, $ket, namaUser()]);
            }
            $n++;
        }
        $msg = "$n siswa berhasil diproses.";
    } else $msg = 'Pilih siswa dan tujuan yang valid.';
    header('Location: ?page=Kenaikan-Kelas&kelas=' . $src . '&msg=' . urlencode($msg)); exit;
}
$siswa = $fKelas ? qall($db, "SELECT id, nis, nama, jenis_kelamin FROM siswa WHERE kelas_id = ? AND status = 'Active' ORDER BY nama", 'i', [$fKelas]) : [];
$riwayat = qall($db, "SELECT m.*, s.nama, s.nis, k1.nama AS dari, k2.nama AS ke FROM mutasi_siswa m JOIN siswa s ON s.id = m.siswa_id
    LEFT JOIN kelas k1 ON k1.id = m.dari_kelas_id LEFT JOIN kelas k2 ON k2.id = m.ke_kelas_id ORDER BY m.id DESC LIMIT 50");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Kenaikan Kelas &amp; Mutasi Siswa</h3></div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Kenaikan-Kelas">
      <label>Kelas asal&nbsp;</label><select name="kelas" class="form-control" onchange="this.form.submit()"><option value="">- pilih kelas -</option>
        <?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select></form>
    <?php if ($fKelas && !canEdit()): ?><div class="alert alert-warning">Hanya Admin / Tata Usaha yang dapat memproses mutasi.</div><?php endif; ?>
    <?php if ($fKelas): ?>
    <form method="POST" onsubmit="return cekForm(this)"><input type="hidden" name="kelas" value="<?= $fKelas ?>">
      <div class="table-responsive"><table class="table table-bordered table-striped table-condensed">
        <thead><tr><th style="width:40px"><input type="checkbox" id="chkAll" checked></th><th>NIS</th><th>Nama</th><th>L/P</th></tr></thead>
        <tbody><?php if (!$siswa) echo '<tr><td colspan="4" class="text-center text-muted">Tidak ada siswa aktif di kelas ini.</td></tr>'; ?>
        <?php foreach ($siswa as $s): ?><tr><td><input type="checkbox" name="ids[]" value="<?= $s['id'] ?>" class="chk" checked></td><td><?= e($s['nis']) ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['jenis_kelamin']) ?></td></tr><?php endforeach; ?></tbody>
      </table></div>
      <?php if (canEdit() && $siswa): ?>
      <div class="row">
        <div class="col-sm-3"><label>Tindakan</label><select name="aksi" id="aksi" class="form-control"><option value="kelas">Naik / Pindah Kelas</option><option value="lulus">Luluskan</option><option value="pindah">Pindah Sekolah</option><option value="keluar">Keluar / Berhenti</option></select></div>
        <div class="col-sm-3" id="boxTujuan"><label>Kelas tujuan</label><select name="tujuan" id="tujuan" class="form-control"><option value="">- pilih -</option>
          <?php foreach ($kelasList as $k) if ($k['id'] != $fKelas) echo '<option value="' . $k['id'] . '">' . e($k['nama']) . '</option>'; ?></select></div>
        <div class="col-sm-4"><label>Keterangan (opsional)</label><input name="keterangan" class="form-control" maxlength="200" placeholder="mis. Kenaikan kelas 2026/2027"></div>
        <div class="col-sm-2"><label>&nbsp;</label><br><button class="btn btn-primary btn-block">Proses</button></div>
      </div><?php endif; ?>
    </form><?php endif; ?>
  </div></div>
  <div class="x_panel"><div class="x_title"><h2>Riwayat Mutasi (50 terakhir)</h2><div class="clearfix"></div></div><div class="x_content table-responsive">
    <table class="table table-bordered table-striped table-condensed">
      <thead><tr><th>Tanggal</th><th>NIS</th><th>Nama</th><th>Jenis</th><th>Dari</th><th>Ke</th><th>Keterangan</th><th>Oleh</th></tr></thead>
      <tbody><?php if (!$riwayat) echo '<tr><td colspan="8" class="text-center text-muted">Belum ada mutasi.</td></tr>'; ?>
      <?php foreach ($riwayat as $r): ?><tr><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><span class="label label-success"><?= e($r['jenis']) ?></span></td>
        <td><?= e($r['dari'] ?: '-') ?></td><td><?= e($r['ke'] ?: '-') ?></td><td><?= e($r['keterangan']) ?></td><td><?= e($r['oleh']) ?></td></tr><?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<?php include "footer.php"; ?>
<script>
function cekForm(f) {
  var n = $('.chk:checked').length; if (!n) { alert('Pilih minimal satu siswa.'); return false; }
  if (f.aksi.value === 'kelas' && !f.tujuan.value) { alert('Pilih kelas tujuan.'); return false; }
  return confirm('Proses ' + n + ' siswa? Tindakan ini tercatat di riwayat.');
}
$(function () {
  $('#chkAll').on('change', function () { $('.chk').prop('checked', this.checked); });
  $('#aksi').on('change', function () { $('#boxTujuan').toggle(this.value === 'kelas'); });
});
</script>