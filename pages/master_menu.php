<?php
// Master Menu: atur menu/halaman apa saja yang boleh dibuka, per ROLE atau per USER (khusus ADMIN)
$db = $koneksidb;
$roles = ['KEPALA MADRASAH', 'WAKIL KEPALA', 'TATA USAHA', 'GURU', 'BK'];   // ADMIN selalu akses penuh
// halaman yang bisa diatur, dikelompokkan seperti sidebar
$groups = []; $valid = [];
foreach (menuRegistry() as $m) {
    $items = $m[3] ?: [[$m[0], $m[2]]]; $g = $m[3] ? $m[0] : 'Menu Utama';
    foreach ($items as $it) { if (in_array($it[1], ['Dashboard', 'Users', 'Master-Menu'], true)) continue; $groups[$g][] = $it; $valid[$it[1]] = $it[0]; }
}
$tableOk = (bool)mysqli_query($db, "SELECT 1 FROM master_menu_akses LIMIT 1");
$users = qall($db, "SELECT user_id, user_fullname, user_name, user_role FROM master_user WHERE user_role <> 'ADMIN' ORDER BY user_role, user_fullname");
$uMap = []; foreach ($users as $u) $uMap[(string)$u['user_id']] = $u;
$type = ($_GET['type'] ?? '') === 'USER' ? 'USER' : (($_GET['type'] ?? '') === 'ROLE' ? 'ROLE' : '');
$key = trim($_GET['key'] ?? '');
$targetOk = $type === 'ROLE' ? in_array($key, $roles, true) : ($type === 'USER' ? isset($uMap[$key]) : false);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tableOk) {
    $t = $_POST['type'] ?? ''; $k = trim($_POST['key'] ?? '');
    $ok = $t === 'ROLE' ? in_array($k, $roles, true) : ($t === 'USER' && isset($uMap[$k]));
    if ($ok) {
        qrun($db, "DELETE FROM master_menu_akses WHERE target_type = ? AND target_key = ?", 'ss', [$t, $k]);
        if (($_POST['act'] ?? '') === 'save') {
            $pages = array_values(array_filter((array)($_POST['pages'] ?? []), fn($p) => isset($valid[$p]))) ?: ['-'];   // '-' = sengaja tanpa menu tambahan
            foreach ($pages as $p) qrun($db, "INSERT INTO master_menu_akses (target_type, target_key, page_key, updated_by, updated_date) VALUES (?,?,?,?,NOW())", 'ssss', [$t, $k, $p, namaUser()]);
            $msg = 'Pengaturan menu disimpan.';
        } else $msg = 'Dikembalikan ke aturan bawaan.';
    } else $msg = 'Target tidak valid.';
    header('Location: ?page=Master-Menu&msg=' . urlencode($msg)); exit;
}
// ringkasan pengaturan tersimpan
$saved = $tableOk ? qall($db, "SELECT target_type, target_key, COUNT(CASE WHEN page_key <> '-' THEN 1 END) AS jml, GROUP_CONCAT(page_key ORDER BY page_key SEPARATOR ', ') AS pages, MAX(updated_by) AS oleh, MAX(updated_date) AS kapan
    FROM master_menu_akses GROUP BY target_type, target_key ORDER BY target_type DESC, target_key") : [];
// centang awal editor: pengaturan tersimpan, atau aturan bawaan role-nya
$checked = []; $isCustom = false; $targetLabel = '';
if ($targetOk) {
    $rows = $tableOk ? qall($db, "SELECT page_key FROM master_menu_akses WHERE target_type = ? AND target_key = ?", 'ss', [$type, $key]) : [];
    $isCustom = (bool)$rows;
    $roleOf = $type === 'ROLE' ? $key : $uMap[$key]['user_role'];
    if ($isCustom) $checked = array_column($rows, 'page_key');
    else foreach ($valid as $pk => $lbl) if (defaultAccess($pk, strtoupper($roleOf))) $checked[] = $pk;
    $targetLabel = $type === 'ROLE' ? 'Role ' . $key : $uMap[$key]['user_fullname'] . ' (' . $uMap[$key]['user_role'] . ')';
}
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Master Menu <small>atur akses menu</small></h3></div></div>
  <div class="clearfix"></div>
  <?php if (!$tableOk): ?><div class="alert alert-danger">Tabel <b>master_menu_akses</b> belum ada. Jalankan <code>update_master_menu.sql</code> di phpMyAdmin dulu.</div><?php endif; ?>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <div class="alert alert-info" style="margin-bottom:10px"><small><b>Cara kerja:</b> pengaturan <b>per user</b> mengalahkan pengaturan <b>per role</b>; jika keduanya belum diatur, dipakai aturan bawaan sistem. ADMIN selalu punya akses penuh. Menu yang tidak dicentang tidak tampil dan halamannya ditolak (403) walau dibuka lewat alamat langsung.</small></div>

  <div class="x_panel"><div class="x_title"><h2>Atur Akses</h2><div class="clearfix"></div></div><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:8px"><input type="hidden" name="page" value="Master-Menu"><input type="hidden" name="type" value="ROLE">
      <label style="width:90px">Per Role</label><select name="key" class="form-control input-sm" style="min-width:220px"><?php foreach ($roles as $r) echo '<option' . ($type === 'ROLE' && $key === $r ? ' selected' : '') . '>' . $r . '</option>'; ?></select>
      <button class="btn btn-primary btn-sm">Atur</button></form>
    <form method="GET" class="form-inline"><input type="hidden" name="page" value="Master-Menu"><input type="hidden" name="type" value="USER">
      <label style="width:90px">Per User</label><select name="key" class="form-control input-sm" style="min-width:220px"><?php foreach ($users as $u) echo '<option value="' . $u['user_id'] . '"' . ($type === 'USER' && $key == $u['user_id'] ? ' selected' : '') . '>' . e($u['user_fullname'] . ' - ' . $u['user_role']) . '</option>'; ?></select>
      <button class="btn btn-primary btn-sm">Atur</button></form>
  </div></div>

  <?php if ($targetOk): ?>
  <form method="POST" class="x_panel"><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="key" value="<?= e($key) ?>">
    <div class="x_title"><h2>Menu untuk: <b><?= e($targetLabel) ?></b> <?= $isCustom ? '<span class="label label-success">diatur khusus</span>' : '<span class="label label-default">masih aturan bawaan</span>' ?></h2><div class="clearfix"></div></div>
    <div class="x_content"><div class="row">
      <?php foreach ($groups as $g => $items): ?>
      <div class="col-md-4 col-sm-6"><div style="border:1px solid #d1e7d9;border-radius:8px;padding:10px 14px;margin-bottom:12px">
        <label style="color:#166534"><input type="checkbox" class="chk-grp"> <b><?= e($g) ?></b></label>
        <?php foreach ($items as $it): ?><div class="checkbox" style="margin:4px 0 4px 18px"><label><input type="checkbox" class="chk-item" name="pages[]" value="<?= e($it[1]) ?>"<?= in_array($it[1], $checked, true) ? ' checked' : '' ?>> <?= e($it[0]) ?></label></div><?php endforeach; ?>
      </div></div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted"><small>Profil Madrasah (dashboard), Profile, dan User Guide selalu bisa dibuka. Kelola User &amp; Master Menu hanya untuk ADMIN.</small></p>
    <button name="act" value="save" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
    <?php if ($isCustom): ?><button name="act" value="reset" class="btn btn-default" onclick="return confirm('Kembalikan ke aturan bawaan?')">Kembalikan ke bawaan</button><?php endif; ?>
    <a href="?page=Master-Menu" class="btn btn-default">Batal</a></div>
  </form>
  <?php endif; ?>

  <div class="x_panel"><div class="x_title"><h2>Pengaturan Tersimpan</h2><div class="clearfix"></div></div><div class="x_content table-responsive">
    <table class="table table-bordered table-striped table-condensed"><thead><tr><th>No</th><th>Jenis</th><th>Target</th><th class="text-center">Jumlah Menu</th><th>Menu</th><th>Terakhir diubah</th><th>Aksi</th></tr></thead>
      <tbody><?php if (!$saved) echo '<tr><td colspan="7" class="text-center text-muted">Belum ada pengaturan: semua role memakai aturan bawaan.</td></tr>'; ?>
      <?php foreach ($saved as $i => $r): $nm = $r['target_type'] === 'USER' ? (isset($uMap[$r['target_key']]) ? $uMap[$r['target_key']]['user_fullname'] . ' (' . $uMap[$r['target_key']]['user_role'] . ')' : 'User #' . $r['target_key'] . ' (dihapus)') : $r['target_key'];
        $lbl = []; foreach (explode(', ', $r['pages']) as $pk) if (isset($valid[$pk])) $lbl[] = $valid[$pk]; ?>
        <tr><td><?= $i + 1 ?></td><td><span class="label label-<?= $r['target_type'] === 'USER' ? 'warning' : 'success' ?>"><?= $r['target_type'] === 'USER' ? 'Per User' : 'Per Role' ?></span></td><td><b><?= e($nm) ?></b></td><td class="text-center"><?= (int)$r['jml'] ?></td>
          <td><small><?= $lbl ? e(implode(', ', $lbl)) : '<i>tanpa menu tambahan</i>' ?></small></td><td><small><?= e($r['oleh']) ?><br><?= $r['kapan'] ? date('d/m/Y H:i', strtotime($r['kapan'])) : '' ?></small></td>
          <td><a class="btn btn-warning btn-xs" href="?page=Master-Menu&type=<?= $r['target_type'] ?>&key=<?= urlencode($r['target_key']) ?>">Edit</a>
            <form method="POST" style="display:inline"><input type="hidden" name="type" value="<?= $r['target_type'] ?>"><input type="hidden" name="key" value="<?= e($r['target_key']) ?>"><button name="act" value="reset" class="btn btn-danger btn-xs" onclick="return confirm('Kembalikan ke aturan bawaan?')">Reset</button></form></td></tr>
      <?php endforeach; ?></tbody></table>
  </div></div>
</div>
<?php include "footer.php"; ?>
<script>
$(function () {
  function sync() { $('.chk-grp').each(function () { var b = $(this).closest('div').find('.chk-item'); $(this).prop('checked', b.length && b.filter(':checked').length === b.length); }); }
  $('.chk-grp').on('change', function () { $(this).closest('div').find('.chk-item').prop('checked', this.checked); });
  $('.chk-item').on('change', sync); sync();
});
</script>