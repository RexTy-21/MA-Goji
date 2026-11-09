<?php
// Tahun Ajaran & Semester
$db = $koneksidb;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $sem = in_array($f('semester'), ['Ganjil', 'Genap'], true) ? $f('semester') : 'Ganjil';
    $mulai = $f('tgl_mulai') ?: null; $selesai = $f('tgl_selesai') ?: null;
    if ($act === 'add' && $f('nama') !== '') {
        qrun($db, "INSERT IGNORE INTO tahun_ajaran (nama, semester, tgl_mulai, tgl_selesai) VALUES (?,?,?,?)", 'ssss', [$f('nama'), $sem, $mulai, $selesai]);
    } elseif ($act === 'edit' && (int)$f('id') > 0) {
        qrun($db, "UPDATE tahun_ajaran SET nama=?, semester=?, tgl_mulai=?, tgl_selesai=? WHERE id=?", 'ssssi', [$f('nama'), $sem, $mulai, $selesai, (int)$f('id')]);
    } elseif ($act === 'aktif' && (int)$f('id') > 0) {
        qrun($db, "UPDATE tahun_ajaran SET aktif = 0");
        qrun($db, "UPDATE tahun_ajaran SET aktif = 1 WHERE id = ?", 'i', [(int)$f('id')]);
    }
    header('Location: ?page=Tahun-Ajaran'); exit;
}
$rows = qall($db, "SELECT t.*, (SELECT COUNT(*) FROM jadwal j WHERE j.tahun_ajaran_id = t.id) AS jml_jadwal FROM tahun_ajaran t ORDER BY t.nama DESC, t.semester");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Tahun Ajaran &amp; Semester</h3></div>
    <div class="title_right"><?php if (canEdit()): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Tambah</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <div class="x_panel"><div class="x_content table-responsive">
    <table class="table table-bordered table-striped">
      <thead><tr><th>No</th><th>Tahun Ajaran</th><th>Semester</th><th>Mulai</th><th>Selesai</th><th class="text-center">Jadwal</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['semester']) ?></td>
          <td><?= $r['tgl_mulai'] ? date('d/m/Y', strtotime($r['tgl_mulai'])) : '-' ?></td><td><?= $r['tgl_selesai'] ? date('d/m/Y', strtotime($r['tgl_selesai'])) : '-' ?></td>
          <td class="text-center"><?= (int)$r['jml_jadwal'] ?></td>
          <td><?= $r['aktif'] ? '<span class="label label-success">AKTIF</span>' : '<span class="label label-default">Arsip</span>' ?></td>
          <td><?php if (canEdit()): ?>
            <?php if (!$r['aktif']): ?><form method="POST" style="display:inline"><input type="hidden" name="act" value="aktif"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-success btn-xs" onclick="return confirm('Jadikan periode ini aktif?')">Aktifkan</button></form><?php endif; ?>
            <button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button><?php endif; ?></td></tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<?php if (canEdit()): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Tahun Ajaran</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="form-group"><label>Tahun Ajaran</label><input class="form-control" name="nama" id="m_nama" required placeholder="2026/2027"></div>
    <div class="form-group"><label>Semester</label><select class="form-control" name="semester" id="m_semester"><option>Ganjil</option><option>Genap</option></select></div>
    <div class="row"><div class="col-xs-6"><label>Mulai</label><input type="date" class="form-control" name="tgl_mulai" id="m_tgl_mulai"></div>
      <div class="col-xs-6"><label>Selesai</label><input type="date" class="form-control" name="tgl_selesai" id="m_tgl_selesai"></div></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_nama,#m_tgl_mulai,#m_tgl_selesai').val(''); $('#mTitle').text('Tambah Tahun Ajaran'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id); $('#m_nama').val(d.nama); $('#m_semester').val(d.semester);
    $('#m_tgl_mulai').val(d.tgl_mulai || ''); $('#m_tgl_selesai').val(d.tgl_selesai || ''); $('#mTitle').text('Edit Tahun Ajaran'); $('#mdl').modal('show');
  });
});
</script>