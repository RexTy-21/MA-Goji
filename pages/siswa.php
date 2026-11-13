<?php
// Data Siswa dengan filter per kelas. Edit hanya untuk ADMIN / TATA USAHA.
$db = $koneksidb;
$kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $act = $_POST['act'] ?? ''; $msg = '';
    $f = fn($k) => trim($_POST[$k] ?? '');
    $jk = ($f('jk') === 'P') ? 'P' : 'L';
    $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('tanggal_lahir')) ? $f('tanggal_lahir') : null;
    $kid = (int)$f('kelas_id');
    if ($act === 'add' && $f('nis') !== '' && $f('nama') !== '') {
        $ok = qrun($db, "INSERT INTO siswa (nis, nisn, nama, jenis_kelamin, kelas_id, tanggal_lahir, alamat, nama_wali, no_hp_wali) VALUES (?,?,?,?,?,?,?,?,?)",
            'ssssissss', [$f('nis'), $f('nisn'), $f('nama'), $jk, $kid, $tgl, $f('alamat'), $f('nama_wali'), $f('no_hp_wali')]);
        if ($ok && ($nf = uploadFoto('foto', 'siswa'))) qrun($db, "UPDATE siswa SET foto=? WHERE nis=?", 'ss', [$nf, $f('nis')]);
        $msg = $ok ? 'Siswa ditambahkan.' : 'Gagal: NIS sudah dipakai.';
    } elseif ($act === 'edit' && (int)$f('id') > 0) {
        $st = in_array($f('status'), ['Active', 'Lulus', 'Pindah', 'Keluar'], true) ? $f('status') : 'Active';
        qrun($db, "UPDATE siswa SET nis=?, nisn=?, nama=?, jenis_kelamin=?, kelas_id=?, tanggal_lahir=?, alamat=?, nama_wali=?, no_hp_wali=?, status=? WHERE id=?",
            'ssssisssssi', [$f('nis'), $f('nisn'), $f('nama'), $jk, $kid, $tgl, $f('alamat'), $f('nama_wali'), $f('no_hp_wali'), $st, (int)$f('id')]);
        if ($nf = uploadFoto('foto', 'siswa')) {
            $old = qall($db, "SELECT foto FROM siswa WHERE id=?", 'i', [(int)$f('id')])[0]['foto'] ?? null;
            qrun($db, "UPDATE siswa SET foto=? WHERE id=?", 'si', [$nf, (int)$f('id')]);
            if ($old) @unlink('uploads/foto/' . basename($old));
        }
        $msg = 'Data siswa disimpan.';
    }
    header('Location: ?page=Data-Siswa&kelas=' . urlencode($_GET['kelas'] ?? '') . '&msg=' . urlencode($msg)); exit;
}
$fKelas = (int)($_GET['kelas'] ?? 0); $fSt = $_GET['status'] ?? 'Active'; $fJk = $_GET['jk'] ?? '';
$where = []; $types = ''; $par = [];
if ($fKelas > 0) { $where[] = 's.kelas_id = ?'; $types .= 'i'; $par[] = $fKelas; }
if (in_array($fSt, ['Active', 'Lulus', 'Pindah', 'Keluar'], true)) { $where[] = 's.status = ?'; $types .= 's'; $par[] = $fSt; }
if (in_array($fJk, ['L', 'P'], true)) { $where[] = 's.jenis_kelamin = ?'; $types .= 's'; $par[] = $fJk; }
$rows = qall($db, "SELECT s.*, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id"
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY FIELD(k.tingkat,'X','XI','XII'), k.jurusan, s.nama", $types, $par);
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Data Siswa</h3></div>
    <div class="title_right">
      <a href="?page=Cetak-Kartu&tipe=siswa&kelas=<?= $fKelas ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Cetak Kartu <?= $fKelas ? 'Kelas Ini' : 'Semua Siswa' ?></a>
      <?php if (canEdit()): ?><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="fa fa-plus"></i> Tambah Siswa</button><?php endif; ?>
    </div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <form method="GET" class="form-inline" style="margin-bottom:10px"><input type="hidden" name="page" value="Data-Siswa">
    <label>Kelas&nbsp;</label><select name="kelas" class="form-control input-sm" onchange="this.form.submit()"><option value="0">Semua Kelas</option>
      <?php foreach ($kelasList as $k): ?><option value="<?= $k['id'] ?>"<?= $fKelas === (int)$k['id'] ? ' selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?></select>
    <label style="margin-left:10px">Status&nbsp;</label><select name="status" class="form-control input-sm" onchange="this.form.submit()"><option value="">Semua</option>
      <?php foreach (['Active', 'Lulus', 'Pindah', 'Keluar'] as $s): ?><option<?= $fSt === $s ? ' selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
    <label style="margin-left:10px">L/P&nbsp;</label><select name="jk" class="form-control input-sm" onchange="this.form.submit()"><option value="">Semua</option><option value="L"<?= $fJk === 'L' ? ' selected' : '' ?>>Laki-laki</option><option value="P"<?= $fJk === 'P' ? ' selected' : '' ?>>Perempuan</option></select>
    <span class="label label-success" style="margin-left:12px;font-size:12px"><?= count($rows) ?> siswa</span>
  </form>
  <div class="x_panel"><div class="x_content table-responsive">
    <table id="tbl" class="table table-bordered table-striped" width="100%">
      <thead><tr><th>No</th><th>Foto</th><th>NIS</th><th>NISN</th><th>Nama Lengkap</th><th>L/P</th><th>Kelas</th><th>Nama Wali</th><th>HP Wali</th><th>Status</th><th>Kartu</th><?php if (canEdit()): ?><th>Edit</th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($rows as $i => $r): $on = $r['status'] === 'Active'; ?>
        <tr><td><?= $i + 1 ?></td>
          <td><?php if (!empty($r['foto'])): ?>
            <img src="uploads/foto/<?= e($r['foto']) ?>" alt="" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
          <?php else: ?>
            <span style="display:inline-flex;width:34px;height:34px;border-radius:50%;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;background:<?= avatarColor($r['nis']) ?>"><?= e(initials($r['nama'])) ?></span>
          <?php endif; ?></td>
          <td><?= e($r['nis']) ?></td><td><?= e($r['nisn']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['jenis_kelamin']) ?></td><td><?= e($r['kelas']) ?></td>
          <td><?= e($r['nama_wali']) ?></td><td><?= e($r['no_hp_wali']) ?></td>
          <td><span class="label label-<?= $on ? 'success' : 'default' ?>"><?= e($r['status']) ?></span></td>
          <td><button type="button" class="btn btn-info btn-xs btn-card" data-nama="<?= e($r['nama']) ?>" data-sub="Kelas <?= e($r['kelas']) ?>" data-info="NIS: <?= e($r['nis']) ?>" data-qr="SISWA:<?= e($r['nis']) ?>"><i class="fa fa-id-card-o"></i></button></td>
          <?php if (canEdit()): ?><td><button type="button" class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php
$formFields = function ($p) use ($kelasList) { ?>
  <div class="row">
    <div class="col-sm-4 form-group"><label>NIS</label><input class="form-control" name="nis" id="<?= $p ?>nis" required></div>
    <div class="col-sm-4 form-group"><label>NISN</label><input class="form-control" name="nisn" id="<?= $p ?>nisn"></div>
    <div class="col-sm-4 form-group"><label>Jenis Kelamin</label><select class="form-control" name="jk" id="<?= $p ?>jenis_kelamin"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
    <div class="col-sm-8 form-group"><label>Nama Lengkap</label><input class="form-control" name="nama" id="<?= $p ?>nama" required></div>
    <div class="col-sm-4 form-group"><label>Kelas</label><select class="form-control" name="kelas_id" id="<?= $p ?>kelas_id"><?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '">' . e($k['nama']) . '</option>'; ?></select></div>
    <div class="col-sm-4 form-group"><label>Tanggal Lahir</label><input type="date" class="form-control" name="tanggal_lahir" id="<?= $p ?>tanggal_lahir"></div>
    <div class="col-sm-8 form-group"><label>Alamat</label><input class="form-control" name="alamat" id="<?= $p ?>alamat"></div>
    <div class="col-sm-6 form-group"><label>Nama Wali</label><input class="form-control" name="nama_wali" id="<?= $p ?>nama_wali"></div>
    <div class="col-sm-6 form-group"><label>No. HP Wali</label><input class="form-control" name="no_hp_wali" id="<?= $p ?>no_hp_wali"></div>
    <div class="col-sm-12 form-group"><label>Foto Profil</label><br>
      <img id="<?= $p ?>prev" src="" alt="" style="display:none;width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:6px">
      <input type="file" name="foto" id="<?= $p ?>fotofile" accept="image/jpeg,image/png,image/webp" class="form-control">
      <small class="text-muted">JPG/PNG/WEBP, maks 2 MB. Kosongkan jika tidak ingin mengganti.</small></div>
  </div>
<?php };
if (canEdit()): ?>
<div class="modal fade" id="mdlAdd" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" enctype="multipart/form-data">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Tambah Siswa</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="add"><?php $formFields('a_'); ?></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<div class="modal fade" id="mdlEdit" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" enctype="multipart/form-data">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Edit Siswa</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="edit"><input type="hidden" name="id" id="e_id"><?php $formFields('e_'); ?>
    <div class="form-group"><label>Status</label><select class="form-control" name="status" id="e_status"><option>Active</option><option>Lulus</option><option>Pindah</option><option>Keluar</option></select></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif;
include "footer.php"; ?>
<script src="assets/js/card.js"></script>
<script>
$(function () {
  $('#tbl').DataTable({ dom: 'lBfrtip', pageLength: 25, buttons: ['copy', 'csv', 'excel', 'print'] });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row');
    $.each(d, function (k, v) { $('#e_' + k).val(v); });
    $('#e_fotofile').val('');
    $('#e_prev').attr('src', d.foto ? 'uploads/foto/' + d.foto : '').toggle(!!d.foto);
    $('#mdlEdit').modal('show');
  });
  $(document).on('change', 'input[type=file][name=foto]', function () {
    var p = $('#' + this.id.replace('fotofile', 'prev'));
    if (this.files[0]) p.attr('src', URL.createObjectURL(this.files[0])).show();
  });
});
</script>