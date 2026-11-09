<?php
// Rekap Rapor per kelas + catatan wali kelas + tombol cetak
$db = $koneksidb;
$ta = taAktif($db);
$isMgmt = in_array(role(), mgmtRoles(), true);
$myGuru = (int)($_SESSION['SES_GURU_ID'] ?? 0);
$kelasList = qall($db, "SELECT id, nama, wali_guru_id FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$fKelas = (int)($_GET['kelas'] ?? 0);
$kelasSel = null; foreach ($kelasList as $k) if ($k['id'] == $fKelas) $kelasSel = $k;
$bolehCatat = $kelasSel && ($isMgmt || ($myGuru && (int)$kelasSel['wali_guru_id'] === $myGuru));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ta && $bolehCatat) {
    $sid = (int)($_POST['siswa_id'] ?? 0);
    $sikap = in_array($_POST['sikap'] ?? '', ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'], true) ? $_POST['sikap'] : null;
    if ($sid && qone($db, "SELECT id FROM siswa WHERE id = ? AND kelas_id = ?", 'ii', [$sid, $fKelas]))
        qrun($db, "INSERT INTO rapor_catatan (tahun_ajaran_id, siswa_id, sikap, catatan) VALUES (?,?,?,?)
                   ON DUPLICATE KEY UPDATE sikap = VALUES(sikap), catatan = VALUES(catatan)", 'iiss', [$ta['id'], $sid, $sikap, mb_substr(trim($_POST['catatan'] ?? ''), 0, 500)]);
    header('Location: ?page=Rapor&kelas=' . $fKelas . '&msg=' . urlencode('Catatan disimpan.')); exit;
}
$rows = [];
if ($ta && $kelasSel) {
    $rows = qall($db, "SELECT s.id, s.nis, s.nama,
        (SELECT COUNT(*) FROM nilai n WHERE n.siswa_id = s.id AND n.tahun_ajaran_id = ?) AS jml,
        (SELECT AVG(n.tugas*0.3 + n.uts*0.3 + n.uas*0.4) FROM nilai n WHERE n.siswa_id = s.id AND n.tahun_ajaran_id = ? AND n.tugas IS NOT NULL AND n.uts IS NOT NULL AND n.uas IS NOT NULL) AS rata,
        c.sikap, c.catatan
        FROM siswa s LEFT JOIN rapor_catatan c ON c.siswa_id = s.id AND c.tahun_ajaran_id = ?
        WHERE s.kelas_id = ? AND s.status = 'Active' ORDER BY s.nama", 'iiii', [$ta['id'], $ta['id'], $ta['id'], $fKelas]);
    // peringkat berdasarkan rata-rata
    $urut = array_filter(array_column($rows, 'rata', 'id'), fn($v) => $v !== null); arsort($urut); $rank = []; $i = 0; foreach ($urut as $id => $v) $rank[$id] = ++$i;
}
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Rapor <small><?= $ta ? e($ta['nama'] . ' - ' . $ta['semester']) : '' ?></small></h3></div>
    <div class="title_right"><?php if ($kelasSel && $rows): ?><a class="btn btn-default btn-sm" target="_blank" href="?page=Cetak-Rapor&kelas=<?= $fKelas ?>"><i class="fa fa-print"></i> Cetak Semua Rapor Kelas</a><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <?php if (!$ta): ?><div class="alert alert-warning">Belum ada Tahun Ajaran aktif.</div><?php endif; ?>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="x_panel"><div class="x_content table-responsive">
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Rapor">
      <label>Kelas&nbsp;</label><select name="kelas" class="form-control" onchange="this.form.submit()"><option value="">- pilih -</option>
        <?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select></form>
    <?php if ($kelasSel): ?>
    <table class="table table-bordered table-striped">
      <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th class="text-center">Mapel Ternilai</th><th class="text-center">Rata-rata</th><th class="text-center">Peringkat</th><th>Sikap</th><th>Aksi</th></tr></thead>
      <tbody><?php if (!$rows) echo '<tr><td colspan="8" class="text-center text-muted">Tidak ada siswa aktif.</td></tr>'; ?>
      <?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><b><?= e($r['nama']) ?></b></td><td class="text-center"><?= (int)$r['jml'] ?></td>
          <td class="text-center"><?= $r['rata'] !== null ? number_format((float)$r['rata'], 1) : '-' ?></td><td class="text-center"><?= $rank[$r['id']] ?? '-' ?></td><td><?= e($r['sikap'] ?: '-') ?></td>
          <td><a class="btn btn-success btn-xs" target="_blank" href="?page=Cetak-Rapor&siswa=<?= $r['id'] ?>"><i class="fa fa-print"></i> Cetak</a>
            <?php if ($bolehCatat): ?><button class="btn btn-warning btn-xs btn-cat" data-row="<?= e(json_encode($r)) ?>">Catatan Wali</button><?php endif; ?></td></tr>
      <?php endforeach; ?></tbody></table>
    <?php if (!$bolehCatat): ?><p class="text-muted"><small>Catatan wali kelas hanya dapat diisi oleh wali kelas yang bersangkutan atau pengelola.</small></p><?php endif; ?>
    <?php endif; ?>
  </div></div>
</div>
<?php if ($bolehCatat): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Catatan Wali Kelas</h4></div>
  <div class="modal-body"><input type="hidden" name="siswa_id" id="m_sid">
    <div class="form-group"><label>Sikap</label><select class="form-control" name="sikap" id="m_sikap"><option value="">-</option><option>Sangat Baik</option><option>Baik</option><option>Cukup</option><option>Kurang</option></select></div>
    <div class="form-group"><label>Catatan untuk rapor</label><textarea class="form-control" name="catatan" id="m_catatan" rows="4" maxlength="500"></textarea></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $(document).on('click', '.btn-cat', function () {
    var d = $(this).data('row'); $('#m_sid').val(d.id); $('#m_sikap').val(d.sikap || ''); $('#m_catatan').val(d.catatan || '');
    $('#mTitle').text('Catatan Wali: ' + d.nama); $('#mdl').modal('show');
  });
});
</script>