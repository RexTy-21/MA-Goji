<?php
// Data Guru (CRUD + kartu QR). Edit hanya untuk ADMIN / TATA USAHA.
$db = $koneksidb;
$STATUS_KEP = ['PNS', 'PPPK', 'GTY', 'GTT / Honorer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $act = $_POST['act'] ?? ''; $msg = '';
    $f = fn($k) => trim($_POST[$k] ?? '');
    $jk = ($f('jk') === 'P') ? 'P' : 'L';
    $sk = in_array($f('status_kepegawaian'), $STATUS_KEP, true) ? $f('status_kepegawaian') : 'GTY';
    if ($act === 'add' && $f('nip') !== '' && $f('nama') !== '') {
        $ok = qrun($db, "INSERT INTO guru (nip, nama, jenis_kelamin, mapel, jabatan, status_kepegawaian, no_hp, email) VALUES (?,?,?,?,?,?,?,?)",
            'ssssssss', [$f('nip'), $f('nama'), $jk, $f('mapel'), $f('jabatan'), $sk, $f('no_hp'), $f('email')]);
        $msg = $ok ? 'Guru ditambahkan.' : 'Gagal: NIP sudah dipakai.';
    } elseif ($act === 'edit' && (int)$f('id') > 0) {
        $st = in_array($f('status'), ['Active', 'NonActive'], true) ? $f('status') : 'Active';
        qrun($db, "UPDATE guru SET nip=?, nama=?, jenis_kelamin=?, mapel=?, jabatan=?, status_kepegawaian=?, no_hp=?, email=?, status=? WHERE id=?",
            'sssssssssi', [$f('nip'), $f('nama'), $jk, $f('mapel'), $f('jabatan'), $sk, $f('no_hp'), $f('email'), $st, (int)$f('id')]);
        $msg = 'Data guru disimpan.';
    }
    header('Location: ?page=Data-Guru&msg=' . urlencode($msg)); exit;
}
$fSk = $_GET['sk'] ?? ''; $fSt = $_GET['status'] ?? 'Active';
$where = []; $types = ''; $par = [];
if (in_array($fSk, $STATUS_KEP, true)) { $where[] = 'status_kepegawaian = ?'; $types .= 's'; $par[] = $fSk; }
if (in_array($fSt, ['Active', 'NonActive'], true)) { $where[] = 'status = ?'; $types .= 's'; $par[] = $fSt; }
$rows = qall($db, "SELECT * FROM guru" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY nama", $types, $par);
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Data Guru</h3></div>
    <div class="title_right">
      <a href="?page=Cetak-Kartu&tipe=guru" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak Kartu Semua Guru</a>
      <?php if (canEdit()): ?><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="fa fa-plus"></i> Tambah Guru</button><?php endif; ?>
    </div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <form method="GET" class="form-inline" style="margin-bottom:10px"><input type="hidden" name="page" value="Data-Guru">
    <label>Status Kepegawaian&nbsp;</label><select name="sk" class="form-control input-sm"><option value="">Semua</option><?php foreach ($STATUS_KEP as $s): ?><option<?= $fSk === $s ? ' selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
    <label style="margin-left:10px">Status&nbsp;</label><select name="status" class="form-control input-sm"><option value="">Semua</option><option<?= $fSt === 'Active' ? ' selected' : '' ?>>Active</option><option<?= $fSt === 'NonActive' ? ' selected' : '' ?>>NonActive</option></select>
    <button class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
  </form>
  <div class="x_panel"><div class="x_content table-responsive">
    <table id="tbl" class="table table-bordered table-striped" width="100%">
      <thead><tr><th>No</th><th>Foto</th><th>NIP</th><th>Nama Lengkap</th><th>L/P</th><th>Mapel</th><th>Jabatan</th><th>Status Kepegawaian</th><th>No. HP</th><th>Status</th><th>Kartu</th><?php if (canEdit()): ?><th>Edit</th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($rows as $i => $r): $on = $r['status'] === 'Active'; ?>
        <tr><td><?= $i + 1 ?></td>
          <td><span style="display:inline-flex;width:34px;height:34px;border-radius:50%;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;background:<?= avatarColor($r['nip']) ?>"><?= e(initials($r['nama'])) ?></span></td>
          <td><?= e($r['nip']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['jenis_kelamin']) ?></td><td><?= e($r['mapel']) ?></td><td><?= e($r['jabatan']) ?></td>
          <td><?= e($r['status_kepegawaian']) ?></td><td><?= e($r['no_hp']) ?></td>
          <td><span class="label label-<?= $on ? 'success' : 'danger' ?>"><?= e($r['status']) ?></span></td>
          <td><button type="button" class="btn btn-info btn-xs btn-card" data-nama="<?= e($r['nama']) ?>" data-sub="<?= e($r['jabatan'] ?: $r['mapel']) ?>" data-info="NIP: <?= e($r['nip']) ?>" data-qr="GURU:<?= e($r['nip']) ?>"><i class="fa fa-id-card-o"></i></button></td>
          <?php if (canEdit()): ?><td><button type="button" class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php
$formFields = function ($p) use ($STATUS_KEP) { ?>
  <div class="row">
    <div class="col-sm-6 form-group"><label>NIP</label><input class="form-control" name="nip" id="<?= $p ?>nip" required></div>
    <div class="col-sm-6 form-group"><label>Nama Lengkap</label><input class="form-control" name="nama" id="<?= $p ?>nama" required></div>
    <div class="col-sm-4 form-group"><label>Jenis Kelamin</label><select class="form-control" name="jk" id="<?= $p ?>jenis_kelamin"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
    <div class="col-sm-8 form-group"><label>Mata Pelajaran</label><input class="form-control" name="mapel" id="<?= $p ?>mapel"></div>
    <div class="col-sm-6 form-group"><label>Jabatan</label><input class="form-control" name="jabatan" id="<?= $p ?>jabatan" placeholder="mis. Guru Mapel / Wali Kelas"></div>
    <div class="col-sm-6 form-group"><label>Status Kepegawaian</label><select class="form-control" name="status_kepegawaian" id="<?= $p ?>status_kepegawaian"><?php foreach ($STATUS_KEP as $s) echo '<option>' . e($s) . '</option>'; ?></select></div>
    <div class="col-sm-6 form-group"><label>No. HP</label><input class="form-control" name="no_hp" id="<?= $p ?>no_hp"></div>
    <div class="col-sm-6 form-group"><label>Email</label><input class="form-control" name="email" id="<?= $p ?>email"></div>
  </div>
<?php };
if (canEdit()): ?>
<div class="modal fade" id="mdlAdd" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Tambah Guru</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="add"><?php $formFields('a_'); ?></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<div class="modal fade" id="mdlEdit" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Edit Guru</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="edit"><input type="hidden" name="id" id="e_id"><?php $formFields('e_'); ?>
    <div class="form-group"><label>Status</label><select class="form-control" name="status" id="e_status"><option>Active</option><option>NonActive</option></select></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif;
include "footer.php"; ?>
<script src="assets/js/card.js"></script>
<script>
$(function () {
  $('#tbl').DataTable({ dom: 'lBfrtip', pageLength: 10, buttons: ['copy', 'csv', 'excel', 'print'] });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row');
    $.each(d, function (k, v) { $('#e_' + k).val(v); });
    $('#mdlEdit').modal('show');
  });
});
</script>
