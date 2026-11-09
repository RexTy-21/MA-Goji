<?php
// Mata Pelajaran
$db = $koneksidb;
$kelompok = ['Agama', 'Umum', 'Peminatan', 'Muatan Lokal'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $kel = in_array($f('kelompok'), $kelompok, true) ? $f('kelompok') : 'Umum';
    $kkm = max(0, min(100, (int)$f('kkm') ?: 75));
    if ($act === 'add' && $f('kode') !== '' && $f('nama') !== '') {
        qrun($db, "INSERT IGNORE INTO mapel (kode, nama, kelompok, kkm) VALUES (?,?,?,?)", 'sssi', [strtoupper($f('kode')), $f('nama'), $kel, $kkm]);
    } elseif ($act === 'edit' && (int)$f('id') > 0) {
        qrun($db, "UPDATE mapel SET kode=?, nama=?, kelompok=?, kkm=? WHERE id=?", 'sssii', [strtoupper($f('kode')), $f('nama'), $kel, $kkm, (int)$f('id')]);
    } elseif ($act === 'del' && (int)$f('id') > 0) {
        $pakai = qone($db, "SELECT COUNT(*) n FROM jadwal WHERE mapel_id = ?", 'i', [(int)$f('id')]);
        if (!$pakai || (int)$pakai['n'] === 0) qrun($db, "DELETE FROM mapel WHERE id = ?", 'i', [(int)$f('id')]);
    }
    header('Location: ?page=Mata-Pelajaran'); exit;
}
$rows = qall($db, "SELECT m.*, (SELECT COUNT(*) FROM jadwal j WHERE j.mapel_id = m.id) AS jml,
                   (SELECT GROUP_CONCAT(g.nama SEPARATOR ', ') FROM guru g WHERE g.status='Active' AND g.mapel LIKE CONCAT('%', m.nama, '%')) AS pengampu
                   FROM mapel m ORDER BY FIELD(m.kelompok,'Agama','Umum','Peminatan','Muatan Lokal'), m.nama");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Mata Pelajaran</h3></div>
    <div class="title_right"><?php if (canEdit()): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Tambah Mapel</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <div class="x_panel"><div class="x_content table-responsive">
    <table class="table table-bordered table-striped" id="tbl">
      <thead><tr><th>No</th><th>Kode</th><th>Mata Pelajaran</th><th>Kelompok</th><th class="text-center">KKM</th><th>Guru Pengampu</th><th class="text-center">Di Jadwal</th><?php if (canEdit()): ?><th>Aksi</th><?php endif; ?></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><?= e($r['kode']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['kelompok']) ?></td><td class="text-center"><?= (int)$r['kkm'] ?></td>
          <td><?= e($r['pengampu'] ?: '-') ?></td><td class="text-center"><?= (int)$r['jml'] ?></td>
          <?php if (canEdit()): ?><td><button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button>
            <?php if (!$r['jml']): ?><form method="POST" style="display:inline"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus mapel ini?')">Hapus</button></form><?php endif; ?></td><?php endif; ?></tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<?php if (canEdit()): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Mapel</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="row"><div class="col-xs-4"><label>Kode</label><input class="form-control" name="kode" id="m_kode" required maxlength="15"></div>
      <div class="col-xs-8"><label>Nama Mata Pelajaran</label><input class="form-control" name="nama" id="m_nama" required></div></div><br>
    <div class="row"><div class="col-xs-8"><label>Kelompok</label><select class="form-control" name="kelompok" id="m_kelompok"><?php foreach ($kelompok as $k) echo "<option>$k</option>"; ?></select></div>
      <div class="col-xs-4"><label>KKM</label><input type="number" min="0" max="100" class="form-control" name="kkm" id="m_kkm" value="75"></div></div>
    <p class="help-block">Guru pengampu diambil otomatis dari kolom "Mapel" di Data Guru (nama mapel harus sama).</p>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_kode,#m_nama').val(''); $('#m_kkm').val(75); $('#mTitle').text('Tambah Mapel'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id); $('#m_kode').val(d.kode); $('#m_nama').val(d.nama);
    $('#m_kelompok').val(d.kelompok); $('#m_kkm').val(d.kkm); $('#mTitle').text('Edit Mapel'); $('#mdl').modal('show');
  });
});
</script>