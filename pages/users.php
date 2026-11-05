<?php
// Kelola User (ADMIN): daftar akun, aktif/nonaktif, reset password, tambah, dan "masuk sebagai" untuk demo
$db = $koneksidb;
$ROLES = ['ADMIN', 'KEPALA MADRASAH', 'WAKIL KEPALA', 'TATA USAHA', 'GURU'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? ''; $uid = (int)($_POST['user_id'] ?? 0); $msg = '';
    if ($act === 'loginas' && $uid > 0) {
        $u = qone($db, "SELECT user_id, user_name, user_fullname, user_role, guru_id FROM master_user WHERE user_id = ? AND user_status = 1", 'i', [$uid]);
        if ($u) {
            if (!isset($_SESSION['MASTER_LOGIN_SES'])) $_SESSION['MASTER_LOGIN_SES'] = $_SESSION;
            $_SESSION['SES_USERID'] = $u['user_id']; $_SESSION['SES_NAMA'] = $u['user_fullname']; $_SESSION['SES_USER_NAME'] = $u['user_name'];
            $_SESSION['SES_ROLE'] = $u['user_role']; $_SESSION['SES_GURU_ID'] = $u['guru_id'];
            header('Location: ?page=Dashboard'); exit;
        }
    } elseif ($act === 'toggle' && $uid > 0) {
        qrun($db, "UPDATE master_user SET user_status = 1 - user_status WHERE user_id = ? AND user_role <> 'ADMIN'", 'i', [$uid]); $msg = 'Status akun diubah.';
    } elseif ($act === 'reset' && $uid > 0) {
        qrun($db, "UPDATE master_user SET user_password = ? WHERE user_id = ?", 'si', [hash('sha256', 'demo1234'), $uid]); $msg = 'Password direset ke demo1234.';
    } elseif ($act === 'add') {
        $un = trim($_POST['username'] ?? ''); $nm = trim($_POST['nama'] ?? ''); $rl = in_array($_POST['role'] ?? '', $ROLES, true) ? $_POST['role'] : 'GURU'; $gid = (int)($_POST['guru_id'] ?? 0) ?: null;
        if ($un !== '' && $nm !== '') {
            $ok = qrun($db, "INSERT INTO master_user (user_name, user_password, user_fullname, user_role, guru_id) VALUES (?,?,?,?,?)", 'ssssi', [$un, hash('sha256', 'demo1234'), $nm, $rl, $gid]);
            $msg = $ok ? 'User ditambahkan (password: demo1234).' : 'Gagal: username sudah dipakai.';
        }
    }
    header('Location: ?page=Users&msg=' . urlencode($msg)); exit;
}
$rows = qall($db, "SELECT u.*, g.nama AS guru FROM master_user u LEFT JOIN guru g ON g.id = u.guru_id ORDER BY FIELD(u.user_role,'ADMIN','KEPALA MADRASAH','WAKIL KEPALA','TATA USAHA','GURU'), u.user_fullname");
$guruList = qall($db, "SELECT id, nama FROM guru WHERE status = 'Active' ORDER BY nama");
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Kelola User</h3></div>
    <div class="title_right"><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="fa fa-plus"></i> Tambah User</button></div></div>
  <div class="clearfix"></div>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-info"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="x_panel"><div class="x_content table-responsive">
    <table id="tbl" class="table table-bordered table-striped" width="100%">
      <thead><tr><th>No</th><th>Username</th><th>Nama</th><th>Peran</th><th>Guru Terkait</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody><?php foreach ($rows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td><td><?= e($r['user_name']) ?></td><td><b><?= e($r['user_fullname']) ?></b></td><td><?= e($r['user_role']) ?></td><td><?= e($r['guru'] ?: '-') ?></td>
          <td><span class="label label-<?= $r['user_status'] ? 'success' : 'default' ?>"><?= $r['user_status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td><form method="POST" style="display:inline"><input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>">
            <button name="act" value="loginas" class="btn btn-success btn-xs"><i class="fa fa-sign-in"></i> Masuk sebagai</button>
            <button name="act" value="reset" class="btn btn-warning btn-xs" onclick="return confirm('Reset password ke demo1234?')">Reset PW</button>
            <?php if ($r['user_role'] !== 'ADMIN'): ?><button name="act" value="toggle" class="btn btn-default btn-xs"><?= $r['user_status'] ? 'Nonaktifkan' : 'Aktifkan' ?></button><?php endif; ?></form></td></tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div>
</div>
<div class="modal fade" id="mdlAdd" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Tambah User</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="add">
    <div class="form-group"><label>Username</label><input class="form-control" name="username" required></div>
    <div class="form-group"><label>Nama Lengkap</label><input class="form-control" name="nama" required></div>
    <div class="form-group"><label>Peran</label><select class="form-control" name="role"><?php foreach ($ROLES as $r) echo '<option>' . e($r) . '</option>'; ?></select></div>
    <div class="form-group"><label>Guru terkait (opsional)</label><select class="form-control" name="guru_id"><option value="">-</option><?php foreach ($guruList as $g) echo '<option value="' . $g['id'] . '">' . e($g['nama']) . '</option>'; ?></select></div>
    <p class="text-muted" style="font-size:12px">Password awal: demo1234</p></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Tambah</button></div>
</form></div></div>
<?php include "footer.php"; ?>
<script>$(function(){ $('#tbl').DataTable({ pageLength: 25 }); });</script>
