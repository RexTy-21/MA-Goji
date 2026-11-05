<?php
// Data Kelas / Rombel
$db = $koneksidb;
$guruList = qall($db, "SELECT id, nama FROM guru WHERE status = 'Active' ORDER BY nama");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $tingkat = in_array($f('tingkat'), ['X', 'XI', 'XII'], true) ? $f('tingkat') : 'X';
    $wali = (int)$f('wali_guru_id') ?: null;
    if (($_POST['act'] ?? '') === 'add' && $f('nama') !== '') {
        qrun($db, "INSERT INTO kelas (nama, tingkat, jurusan, wali_guru_id) VALUES (?,?,?,?)", 'sssi', [$f('nama'), $tingkat, $f('jurusan'), $wali]);
    } elseif (($_POST['act'] ?? '') === 'edit' && (int)$f('id') > 0) {
        qrun($db, "UPDATE kelas SET nama=?, tingkat=?, jurusan=?, wali_guru_id=? WHERE id=?", 'sssii', [$f('nama'), $tingkat, $f('jurusan'), $wali, (int)$f('id')]);
    }
    header('Location: ?page=Data-Kelas'); exit;
}
$rows = qall($db, "SELECT k.*, g.nama AS wali, (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id AND s.status = 'Active') AS jml
                   FROM kelas k LEFT JOIN guru g ON g.id = k.wali_guru_id ORDER BY FIELD(k.tingkat,'X','XI','XII'), k.jurusan");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Data Kelas</h3></div>
    <div class="title_right"><?php if (canEdit()): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Tambah Kelas</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <div class="x_panel"><div class="x_content table-responsive">
    <table class="table table-bordered table-striped">
      <thead><tr><th>No</th><th>Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Wali Kelas</th><th class="text-center">Jumlah Siswa</th><th>Aksi</th></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['tingkat']) ?></td><td><?= e($r['jurusan']) ?></td><td><?= e($r['wali'] ?: '-') ?></td><td class="text-center"><?= (int)$r['jml'] ?></td>
          <td><a class="btn btn-success btn-xs" href="?page=Data-Siswa&kelas=<?= $r['id'] ?>"><i class="fa fa-users"></i> Lihat Siswa</a>
              <a class="btn btn-default btn-xs" target="_blank" href="?page=Cetak-Kartu&tipe=siswa&kelas=<?= $r['id'] ?>"><i class="fa fa-print"></i> Kartu</a>
              <?php if (canEdit()): ?><button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button><?php endif; ?></td></tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<?php if (canEdit()): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Kelas</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="form-group"><label>Nama Kelas</label><input class="form-control" name="nama" id="m_nama" required placeholder="mis. X IPA"></div>
    <div class="form-group"><label>Tingkat</label><select class="form-control" name="tingkat" id="m_tingkat"><option>X</option><option>XI</option><option>XII</option></select></div>
    <div class="form-group"><label>Jurusan</label><input class="form-control" name="jurusan" id="m_jurusan" placeholder="IPA / IPS / Keagamaan"></div>
    <div class="form-group"><label>Wali Kelas</label><select class="form-control" name="wali_guru_id" id="m_wali_guru_id"><option value="">-</option><?php foreach ($guruList as $g) echo '<option value="' . $g['id'] . '">' . e($g['nama']) . '</option>'; ?></select></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_nama,#m_jurusan').val(''); $('#m_wali_guru_id').val(''); $('#mTitle').text('Tambah Kelas'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id); $('#m_nama').val(d.nama); $('#m_tingkat').val(d.tingkat);
    $('#m_jurusan').val(d.jurusan); $('#m_wali_guru_id').val(d.wali_guru_id || ''); $('#mTitle').text('Edit Kelas'); $('#mdl').modal('show');
  });
});
</script>
