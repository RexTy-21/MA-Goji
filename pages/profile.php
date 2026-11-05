<?php
// Profile pengguna + riwayat login terakhir
$db = $koneksidb;
$uid = (int)($_SESSION['SES_USERID'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gid = (int)($_SESSION['SES_GURU_ID'] ?? 0);
    if ($gid > 0) qrun($db, "UPDATE guru SET no_hp = ?, email = ? WHERE id = ?", 'ssi', [trim($_POST['phone'] ?? ''), trim($_POST['email'] ?? ''), $gid]);
    header('Location: ?page=Profile&saved=1'); exit;
}
$u = qone($db, "SELECT u.user_fullname, u.user_role, g.nip, g.mapel, g.jabatan, g.no_hp, g.email, g.id AS gid FROM master_user u LEFT JOIN guru g ON g.id = u.guru_id WHERE u.user_id = ?", 'i', [$uid]) ?: [];
$log = qall($db, "SELECT login_time, ip_address, status FROM login_log WHERE user_id = ? ORDER BY login_time DESC, id DESC LIMIT 10", 'i', [$uid]);
$editMode = isset($_GET['edit']) && !empty($u['gid']);
$nm = $u['user_fullname'] ?? ($_SESSION['SES_NAMA'] ?? '');
$bln = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
include "header.php";
?>
<style>
  .pf-ava { width: 180px; height: 180px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: 700; color: #fff; background: linear-gradient(135deg, #16a34a, #0d6b33); }
  .pf-name { text-align: center; font-size: 21px; font-weight: 800; margin: 0; } .pf-sub { text-align: center; color: #666; margin-bottom: 14px; }
  .pf-row { margin: 12px 0; } .pf-row b { display: block; }
  .pf-log th { background: #0d6b33 !important; color: #fff !important; font-size: 12px; } .pf-log td { font-size: 12.5px; vertical-align: middle !important; }
  .ipb { background: #6fcf97; color: #0d4a26; border-radius: 10px; padding: 2px 10px; font-family: monospace; }
</style>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3><i class="fa fa-user"></i> User Profile</h3></div></div><div class="clearfix"></div>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Profil disimpan.</div><?php endif; ?>
  <div class="x_panel"><div class="x_title"><h2>Personal Information <small><?= $editMode ? 'Edit Mode' : 'View Mode' ?></small></h2><div class="clearfix"></div></div>
  <div class="x_content"><div class="row">
    <div class="col-md-4">
      <div class="pf-ava"><?= e(initials($nm)) ?></div><p class="pf-name"><?= e(strtoupper($nm)) ?></p>
      <p class="pf-sub"><i class="fa fa-briefcase"></i> <?= e($u['user_role'] ?? '') ?></p>
      <form method="POST">
        <div class="pf-row"><b>Madrasah:</b>MA Ghoyatul Jihad Karawang</div>
        <?php if (!empty($u['nip'])): ?><div class="pf-row"><b>NIP:</b><?= e($u['nip']) ?></div><div class="pf-row"><b>Jabatan / Mapel:</b><?= e(($u['jabatan'] ?? '') . ' / ' . ($u['mapel'] ?? '')) ?></div><?php endif; ?>
        <div class="pf-row"><b>Phone:</b><?php if ($editMode): ?><input class="form-control input-sm" name="phone" value="<?= e($u['no_hp'] ?? '') ?>"><?php else: echo e($u['no_hp'] ?? '') ?: '-'; endif; ?></div>
        <div class="pf-row"><b>Email:</b><?php if ($editMode): ?><input class="form-control input-sm" name="email" value="<?= e($u['email'] ?? '') ?>"><?php else: echo e($u['email'] ?? '') ?: '-'; endif; ?></div>
        <?php if ($editMode): ?><button class="btn btn-primary btn-block"><i class="fa fa-save"></i> Simpan</button><a href="?page=Profile" class="btn btn-default btn-block">Batal</a>
        <?php elseif (!empty($u['gid'])): ?><a href="?page=Profile&edit=1" class="btn btn-block" style="background:#0d6b33;color:#fff"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?>
      </form>
    </div>
    <div class="col-md-8">
      <div class="well well-sm" style="overflow:hidden"><b style="font-size:16px"><i class="fa fa-history"></i> Riwayat Login Terakhir</b><small class="pull-right text-muted">Memantau aktivitas akun demi keamanan.</small></div>
      <table class="table table-striped pf-log"><thead><tr><th>#</th><th>Waktu Login</th><th>IP Address</th><th>Status</th></tr></thead><tbody>
        <?php if (!$log) echo '<tr><td colspan="4" class="text-center text-muted">Belum ada riwayat login.</td></tr>';
        foreach ($log as $i => $l): $t = strtotime($l['login_time']); ?>
          <tr><td><?= $i + 1 ?></td><td><b><?= date('d', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t) ?></b> <small class="text-muted">pukul <?= date('H:i', $t) ?></small></td>
            <td><span class="ipb"><?= e($l['ip_address']) ?></span></td><td><span class="label label-success"><?= e($l['status']) ?></span></td></tr>
        <?php endforeach; ?></tbody></table>
    </div>
  </div></div></div>
</div>
<?php include "footer.php"; ?>
