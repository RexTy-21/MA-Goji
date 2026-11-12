<?php
// Profile pengguna + ubah password + riwayat login terakhir
$db = $koneksidb;
$uid = (int)($_SESSION['SES_USERID'] ?? 0);
$errPw = '';
$succPw = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? 'profile';
    if ($act === 'profile') {
        $gid = (int)($_SESSION['SES_GURU_ID'] ?? 0);
        if ($gid > 0) {
            qrun($db, "UPDATE guru SET no_hp = ?, email = ? WHERE id = ?", 'ssi', [trim($_POST['phone'] ?? ''), trim($_POST['email'] ?? ''), $gid]);
        }
        header('Location: ?page=Profile&saved=1');
        exit;
    } elseif ($act === 'password') {
        $old = trim($_POST['old_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $cfm = trim($_POST['confirm_password'] ?? '');

        if (!$old || !$new || !$cfm) {
            $errPw = 'Semua kolom password wajib diisi.';
        } elseif ($new !== $cfm) {
            $errPw = 'Konfirmasi password baru tidak cocok.';
        } elseif (strlen($new) < 4) {
            $errPw = 'Password baru minimal 4 karakter.';
        } else {
            $curr = qone($db, "SELECT user_password FROM master_user WHERE user_id = ?", 'i', [$uid]);
            if (!$curr || hash('sha256', $old) !== $curr['user_password']) {
                $errPw = 'Password lama tidak sesuai.';
            } else {
                qrun($db, "UPDATE master_user SET user_password = ? WHERE user_id = ?", 'si', [hash('sha256', $new), $uid]);
                $succPw = 'Password berhasil diubah. Gunakan password baru untuk login berikutnya.';
            }
        }
    }
}

$u = qone($db, "SELECT u.user_fullname, u.user_role, u.user_name, g.nip, g.mapel, g.jabatan, g.no_hp, g.email, g.id AS gid FROM master_user u LEFT JOIN guru g ON g.id = u.guru_id WHERE u.user_id = ?", 'i', [$uid]) ?: [];
$log = qall($db, "SELECT login_time, ip_address, status FROM login_log WHERE user_id = ? ORDER BY login_time DESC, id DESC LIMIT 10", 'i', [$uid]);
$editMode = isset($_GET['edit']) && !empty($u['gid']);
$nm = $u['user_fullname'] ?? ($_SESSION['SES_NAMA'] ?? '');
$bln = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
include "header.php";
?>
<style>
  .pf-ava { width: 140px; height: 140px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 52px; font-weight: 700; color: #fff; background: linear-gradient(135deg, #16a34a, #0d6b33); box-shadow: 0 4px 12px rgba(22,163,74,.25); }
  .pf-name { text-align: center; font-size: 20px; font-weight: 800; margin: 0; } 
  .pf-sub { text-align: center; color: #666; margin-bottom: 14px; }
  .pf-row { margin: 12px 0; } 
  .pf-row b { display: block; color: #2e4334; font-size: 12px; }
  .pf-log th { background: #0d6b33 !important; color: #fff !important; font-size: 12px; } 
  .pf-log td { font-size: 12.5px; vertical-align: middle !important; }
  .ipb { background: #dcfce7; color: #166534; border-radius: 6px; padding: 2px 8px; font-family: monospace; font-size: 12px; }
</style>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3><i class="fa fa-user"></i> Profil Pengguna</h3></div></div>
  <div class="clearfix"></div>

  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success"><i class="fa fa-check-circle"></i> Profil berhasil diperbarui.</div><?php endif; ?>
  <?php if ($succPw): ?><div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= e($succPw) ?></div><?php endif; ?>
  <?php if ($errPw): ?><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?= e($errPw) ?></div><?php endif; ?>

  <div class="row">
    <div class="col-md-4">
      <div class="x_panel">
        <div class="x_title"><h2>Identitas Akun</h2><div class="clearfix"></div></div>
        <div class="x_content">
          <div class="pf-ava"><?= e(initials($nm)) ?></div>
          <p class="pf-name"><?= e(strtoupper($nm)) ?></p>
          <p class="pf-sub"><span class="label label-success"><i class="fa fa-shield"></i> <?= e($u['user_role'] ?? '') ?></span></p>
          
          <form method="POST">
            <input type="hidden" name="act" value="profile">
            <div class="pf-row"><b>Username:</b><code><?= e($u['user_name'] ?? '') ?></code></div>
            <div class="pf-row"><b>Instansi:</b>MA Ghoyatul Jihad Karawang</div>
            <?php if (!empty($u['nip'])): ?>
              <div class="pf-row"><b>NIP:</b><?= e($u['nip']) ?></div>
              <div class="pf-row"><b>Jabatan / Mapel:</b><?= e(($u['jabatan'] ?? '') . ' / ' . ($u['mapel'] ?? '')) ?></div>
            <?php endif; ?>
            <div class="pf-row"><b>No. HP / WhatsApp:</b>
              <?php if ($editMode): ?>
                <input class="form-control input-sm" name="phone" value="<?= e($u['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
              <?php else: echo e($u['no_hp'] ?? '') ?: '-'; endif; ?>
            </div>
            <div class="pf-row"><b>Email:</b>
              <?php if ($editMode): ?>
                <input type="email" class="form-control input-sm" name="email" value="<?= e($u['email'] ?? '') ?>" placeholder="nama@email.com">
              <?php else: echo e($u['email'] ?? '') ?: '-'; endif; ?>
            </div>
            <?php if ($editMode): ?>
              <button class="btn btn-primary btn-block"><i class="fa fa-save"></i> Simpan Profil</button>
              <a href="?page=Profile" class="btn btn-default btn-block">Batal</a>
            <?php elseif (!empty($u['gid'])): ?>
              <a href="?page=Profile&edit=1" class="btn btn-block" style="background:#0d6b33;color:#fff"><i class="fa fa-pencil"></i> Edit Kontak</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Kotak Ganti Password -->
      <div class="x_panel">
        <div class="x_title"><h2><i class="fa fa-key"></i> Ubah Password</h2><div class="clearfix"></div></div>
        <div class="x_content">
          <form method="POST">
            <input type="hidden" name="act" value="password">
            <div class="form-group">
              <label style="font-size:12px">Password Saat Ini</label>
              <input type="password" class="form-control input-sm" name="old_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
              <label style="font-size:12px">Password Baru</label>
              <input type="password" class="form-control input-sm" name="new_password" required minlength="4" autocomplete="new-password">
            </div>
            <div class="form-group">
              <label style="font-size:12px">Ulangi Password Baru</label>
              <input type="password" class="form-control input-sm" name="confirm_password" required minlength="4" autocomplete="new-password">
            </div>
            <button class="btn btn-warning btn-block btn-sm"><i class="fa fa-lock"></i> Perbarui Password</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="x_panel">
        <div class="x_title">
          <h2><i class="fa fa-history"></i> Riwayat Login Terakhir <small>Audit keamanan akses akun</small></h2>
          <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
          <table class="table table-striped table-hover pf-log">
            <thead>
              <tr><th>#</th><th>Waktu Login</th><th>IP Address</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (!$log): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:20px">Belum ada riwayat login tercatat.</td></tr>
              <?php else: ?>
                <?php foreach ($log as $i => $l): $t = strtotime($l['login_time']); ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= date('d', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t) ?></b> <small class="text-muted">pukul <?= date('H:i:s', $t) ?> WIB</small></td>
                    <td><span class="ipb"><?= e($l['ip_address'] ?: '127.0.0.1') ?></span></td>
                    <td><span class="label label-success"><?= e($l['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
          <p class="text-muted" style="font-size:12px;margin-top:10px"><i class="fa fa-info-circle"></i> Sistem mencatat 10 aktivitas login terakhir untuk mencegah penyalahgunaan akun.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include "footer.php"; ?>
