<?php
// Pengumuman & Agenda
$db = $koneksidb;
$isMgmt = in_array(role(), mgmtRoles(), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isMgmt) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $id = (int)$f('id');
    $sampai = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('berlaku_sampai')) ? $f('berlaku_sampai') : null;
    $penting = !empty($_POST['penting']) ? 1 : 0;
    if ($act === 'add' && $f('judul') !== '' && $f('isi') !== '') {
        qrun($db, "INSERT INTO pengumuman (judul, isi, penting, tanggal, berlaku_sampai, penulis) VALUES (?,?,?,CURDATE(),?,?)", 'ssiss', [$f('judul'), $f('isi'), $penting, $sampai, namaUser()]);
    } elseif ($act === 'edit' && $id > 0) {
        qrun($db, "UPDATE pengumuman SET judul=?, isi=?, penting=?, berlaku_sampai=? WHERE id=?", 'ssisi', [$f('judul'), $f('isi'), $penting, $sampai, $id]);
    } elseif ($act === 'del' && $id > 0) {
        qrun($db, "DELETE FROM pengumuman WHERE id = ?", 'i', [$id]);
    }
    header('Location: ?page=Pengumuman'); exit;
}
$rows = qall($db, "SELECT * FROM pengumuman " . ($isMgmt ? '' : "WHERE berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE()") . " ORDER BY penting DESC, tanggal DESC, id DESC");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Pengumuman &amp; Agenda</h3></div>
    <div class="title_right"><?php if ($isMgmt): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Buat Pengumuman</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <?php if (!$rows) echo '<div class="x_panel text-center text-muted" style="padding:30px">Belum ada pengumuman.</div>'; ?>
  <?php foreach ($rows as $r): $exp = $r['berlaku_sampai'] && $r['berlaku_sampai'] < date('Y-m-d'); ?>
  <div class="x_panel" style="border-left:4px solid <?= $r['penting'] ? '#dc2626' : '#16a34a' ?><?= $exp ? ';opacity:.6' : '' ?>">
    <div class="x_content">
      <h4 style="margin-top:0"><?= $r['penting'] ? '<span class="label label-danger">PENTING</span> ' : '' ?><b><?= e($r['judul']) ?></b><?= $exp ? ' <span class="label label-default">kedaluwarsa</span>' : '' ?></h4>
      <p style="white-space:pre-line"><?= e($r['isi']) ?></p>
      <small class="text-muted"><i class="fa fa-calendar"></i> <?= date('d/m/Y', strtotime($r['tanggal'])) ?> &middot; <?= e($r['penulis']) ?><?= $r['berlaku_sampai'] ? ' &middot; berlaku s/d ' . date('d/m/Y', strtotime($r['berlaku_sampai'])) : '' ?></small>
      <?php if ($isMgmt): ?><div style="margin-top:8px"><button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button>
        <form method="POST" style="display:inline"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus pengumuman ini?')">Hapus</button></form></div><?php endif; ?>
    </div></div>
  <?php endforeach; ?>
</div>
<?php if ($isMgmt): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Pengumuman</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="form-group"><label>Judul</label><input class="form-control" name="judul" id="m_judul" required maxlength="150"></div>
    <div class="form-group"><label>Isi</label><textarea class="form-control" name="isi" id="m_isi" rows="5" required></textarea></div>
    <div class="row"><div class="col-xs-6"><label>Berlaku sampai (opsional)</label><input type="date" class="form-control" name="berlaku_sampai" id="m_berlaku_sampai"></div>
      <div class="col-xs-6"><label>&nbsp;</label><div class="checkbox"><label><input type="checkbox" name="penting" id="m_penting" value="1"> Tandai penting</label></div></div></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_judul,#m_isi,#m_berlaku_sampai').val(''); $('#m_penting').prop('checked', false); $('#mTitle').text('Buat Pengumuman'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id); $('#m_judul').val(d.judul); $('#m_isi').val(d.isi);
    $('#m_berlaku_sampai').val(d.berlaku_sampai || ''); $('#m_penting').prop('checked', d.penting == 1); $('#mTitle').text('Edit Pengumuman'); $('#mdl').modal('show');
  });
});
</script>