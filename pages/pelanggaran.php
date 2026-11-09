<?php
// Pelanggaran Siswa: catatan, rekap poin, master jenis pelanggaran
$db = $koneksidb;
$ta = taAktif($db);
$isMgmt = in_array(role(), mgmtRoles(), true);
$tab = in_array($_GET['tab'] ?? '', ['catatan', 'rekap', 'jenis'], true) ? $_GET['tab'] : 'catatan';
const POIN_AWAL = 100;   // setiap siswa mulai dari 100 poin, tiap pelanggaran mengurangi poin
function levelPoin($sisa) {   // berdasarkan SISA poin
    if ($sisa <= 0)  return ['Sanksi Berat', 'danger'];
    if ($sisa <= 25) return ['Panggilan Orang Tua', 'danger'];
    if ($sisa <= 50) return ['Peringatan Tertulis', 'warning'];
    if ($sisa <= 75) return ['Teguran', 'info'];
    return ['Aman', 'success'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $id = (int)$f('id'); $go = '?page=Pelanggaran&tab=catatan';
    if ($act === 'add') {
        $j = qone($db, "SELECT id, poin FROM jenis_pelanggaran WHERE id = ?", 'i', [(int)$f('jenis_id')]);
        $sid = (int)$f('siswa_id'); $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('tanggal')) ? $f('tanggal') : date('Y-m-d');
        if ($j && $sid && qone($db, "SELECT id FROM siswa WHERE id = ?", 'i', [$sid]))
            qrun($db, "INSERT INTO pelanggaran_siswa (siswa_id, jenis_id, tanggal, poin, keterangan, dicatat_oleh, dibuat) VALUES (?,?,?,?,?,?,NOW())",
                 'iisiss', [$sid, $j['id'], $tgl, (int)$j['poin'], mb_substr($f('keterangan'), 0, 255), namaUser()]);
    } elseif ($act === 'del' && $isMgmt && $id) {
        qrun($db, "DELETE FROM pelanggaran_siswa WHERE id = ?", 'i', [$id]);
    } elseif (in_array($act, ['jenis_add', 'jenis_edit', 'jenis_del'], true) && canEdit()) {
        $go = '?page=Pelanggaran&tab=jenis';
        $kat = in_array($f('kategori'), ['Ringan', 'Sedang', 'Berat'], true) ? $f('kategori') : 'Ringan'; $poin = max(0, min(100, (int)$f('poin')));
        if ($act === 'jenis_add' && $f('nama') !== '') qrun($db, "INSERT INTO jenis_pelanggaran (nama, kategori, poin) VALUES (?,?,?)", 'ssi', [$f('nama'), $kat, $poin]);
        elseif ($act === 'jenis_edit' && $id) qrun($db, "UPDATE jenis_pelanggaran SET nama=?, kategori=?, poin=? WHERE id=?", 'ssii', [$f('nama'), $kat, $poin, $id]);
        elseif ($act === 'jenis_del' && $id) { $c = qone($db, "SELECT COUNT(*) n FROM pelanggaran_siswa WHERE jenis_id = ?", 'i', [$id]); if (!(int)$c['n']) qrun($db, "DELETE FROM jenis_pelanggaran WHERE id = ?", 'i', [$id]); }
    }
    header('Location: ' . $go); exit;
}
$kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$fKelas = (int)($_GET['kelas'] ?? 0);
$jenisList = qall($db, "SELECT j.*, (SELECT COUNT(*) FROM pelanggaran_siswa p WHERE p.jenis_id = j.id) AS dipakai FROM jenis_pelanggaran j ORDER BY FIELD(kategori,'Ringan','Sedang','Berat'), poin, nama");
$katCls = ['Ringan' => 'info', 'Sedang' => 'warning', 'Berat' => 'danger'];
$data = [];
if ($tab === 'catatan') {
    $bulan = preg_match('/^\d{4}-\d{2}$/', $_GET['bulan'] ?? '') ? $_GET['bulan'] : '';
    $w = []; $ty = ''; $pp = [];
    if ($fKelas) { $w[] = 's.kelas_id = ?'; $ty .= 'i'; $pp[] = $fKelas; }
    if ($bulan) { $w[] = "DATE_FORMAT(p.tanggal,'%Y-%m') = ?"; $ty .= 's'; $pp[] = $bulan; }
    $data = qall($db, "SELECT p.*, s.nama AS siswa, s.nis, k.nama AS kelas, j.nama AS jenis, j.kategori FROM pelanggaran_siswa p JOIN siswa s ON s.id = p.siswa_id
        LEFT JOIN kelas k ON k.id = s.kelas_id JOIN jenis_pelanggaran j ON j.id = p.jenis_id " . ($w ? 'WHERE ' . implode(' AND ', $w) : '') . " ORDER BY p.tanggal DESC, p.id DESC LIMIT 300", $ty, $pp);
    $siswaList = qall($db, "SELECT s.id, s.nama, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.status = 'Active' ORDER BY k.nama, s.nama");
} elseif ($tab === 'rekap') {
    $per = ($_GET['per'] ?? 'ta') === 'all' || !$ta || !$ta['tgl_mulai'] || !$ta['tgl_selesai'] ? 'all' : 'ta';
    $jt = ''; $ty = ''; $pp = [];
    if ($per === 'ta') { $jt = ' AND p.tanggal BETWEEN ? AND ?'; $ty .= 'ss'; $pp[] = $ta['tgl_mulai']; $pp[] = $ta['tgl_selesai']; }
    $w = "s.status = 'Active'"; if ($fKelas) { $w .= ' AND s.kelas_id = ?'; $ty .= 'i'; $pp[] = $fKelas; }
    $data = qall($db, "SELECT s.id, s.nis, s.nama, k.nama AS kelas, COALESCE(SUM(p.poin),0) AS total, COUNT(p.id) AS jml
        FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN pelanggaran_siswa p ON p.siswa_id = s.id$jt
        WHERE $w GROUP BY s.id, k.id, k.nama ORDER BY total DESC, s.nama", $ty, $pp);
}
include "header.php";
$tabs = ['catatan' => 'Catatan Pelanggaran', 'rekap' => 'Rekap Poin', 'jenis' => 'Jenis Pelanggaran'];
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Pelanggaran Siswa</h3></div>
    <div class="title_right"><?php if ($tab === 'catatan'): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Catat Pelanggaran</button>
      <?php elseif ($tab === 'jenis' && canEdit()): ?><button class="btn btn-primary btn-sm" id="btnJenis"><i class="fa fa-plus"></i> Tambah Jenis</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <ul class="nav nav-tabs" style="margin-bottom:12px"><?php foreach ($tabs as $k => $v) echo '<li class="' . ($tab === $k ? 'active' : '') . '"><a href="?page=Pelanggaran&tab=' . $k . '">' . $v . '</a></li>'; ?></ul>
  <div class="x_panel"><div class="x_content table-responsive">
  <?php if ($tab !== 'jenis'): ?>
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Pelanggaran"><input type="hidden" name="tab" value="<?= $tab ?>">
      <select name="kelas" class="form-control input-sm" onchange="this.form.submit()"><option value="">Semua Kelas</option><?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select>
      <?php if ($tab === 'catatan'): ?><input type="month" name="bulan" class="form-control input-sm" value="<?= e($bulan) ?>" onchange="this.form.submit()">
      <?php else: ?><select name="per" class="form-control input-sm" onchange="this.form.submit()"><option value="ta"<?= $per === 'ta' ? ' selected' : '' ?>>Semester aktif</option><option value="all"<?= $per === 'all' ? ' selected' : '' ?>>Semua waktu</option></select><?php endif; ?></form>
  <?php endif; ?>
  <?php if ($tab === 'catatan'): ?>
    <table class="table table-bordered table-striped table-condensed"><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th class="text-center">Poin</th><th>Keterangan</th><th>Dicatat</th><?php if ($isMgmt): ?><th></th><?php endif; ?></tr></thead>
      <tbody><?php if (!$data) echo '<tr><td colspan="8" class="text-center text-muted">Belum ada catatan.</td></tr>'; ?>
      <?php foreach ($data as $r): ?><tr><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><b><?= e($r['siswa']) ?></b><br><small class="text-muted"><?= e($r['nis']) ?></small></td><td><?= e($r['kelas']) ?></td>
        <td><span class="label label-<?= $katCls[$r['kategori']] ?? 'default' ?>"><?= e($r['kategori']) ?></span> <?= e($r['jenis']) ?></td><td class="text-center"><b style="color:#b91c1c">-<?= (int)$r['poin'] ?></b></td><td><?= e($r['keterangan']) ?></td><td><small><?= e($r['dicatat_oleh']) ?></small></td>
        <?php if ($isMgmt): ?><td><form method="POST"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus catatan ini?')"><i class="fa fa-trash"></i></button></form></td><?php endif; ?></tr><?php endforeach; ?></tbody></table>
  <?php elseif ($tab === 'rekap'): ?>
    <p class="text-muted"><small>Setiap siswa memiliki <b><?= POIN_AWAL ?> poin</b>; tiap pelanggaran mengurangi poin. Tindakan: sisa ≤75 Teguran · ≤50 Peringatan Tertulis · ≤25 Panggilan Orang Tua · 0 Sanksi Berat.</small></p>
    <table class="table table-bordered table-striped"><thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th class="text-center">Pelanggaran</th><th class="text-center">Dikurangi</th><th style="width:200px">Sisa Poin</th><th>Status</th></tr></thead>
      <tbody><?php if (!$data) echo '<tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>'; ?>
      <?php foreach ($data as $i => $r): $sisa = max(0, POIN_AWAL - (int)$r['total']); [$lv, $cl] = levelPoin($sisa); ?><tr><td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['kelas']) ?></td><td class="text-center"><?= (int)$r['jml'] ?></td><td class="text-center"><?= (int)$r['total'] ? '-' . (int)$r['total'] : '0' ?></td>
        <td><div class="progress" style="margin:0;height:20px"><div class="progress-bar progress-bar-<?= $cl ?>" style="width:<?= $sisa ?>%;min-width:2em"><?= $sisa ?></div></div></td><td><span class="label label-<?= $cl ?>"><?= $lv ?></span></td></tr><?php endforeach; ?></tbody></table>
  <?php else: ?>
    <table class="table table-bordered table-striped"><thead><tr><th>No</th><th>Pelanggaran</th><th>Kategori</th><th class="text-center">Pengurang Poin</th><th class="text-center">Dipakai</th><?php if (canEdit()): ?><th>Aksi</th><?php endif; ?></tr></thead>
      <tbody><?php foreach ($jenisList as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['nama']) ?></td><td><span class="label label-<?= $katCls[$r['kategori']] ?? 'default' ?>"><?= e($r['kategori']) ?></span></td><td class="text-center"><?= (int)$r['poin'] ?></td><td class="text-center"><?= (int)$r['dipakai'] ?></td>
        <?php if (canEdit()): ?><td><button class="btn btn-warning btn-xs btn-jenis" data-row="<?= e(json_encode($r)) ?>">Edit</button>
          <?php if (!$r['dipakai']): ?><form method="POST" style="display:inline"><input type="hidden" name="act" value="jenis_del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus jenis ini?')">Hapus</button></form><?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table>
  <?php endif; ?>
  </div></div>
</div>
<?php if ($tab === 'catatan'): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Catat Pelanggaran</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="add">
    <div class="form-group"><label>Siswa</label><select class="form-control" name="siswa_id" id="p_siswa" required style="width:100%"><option value="">- pilih siswa -</option><?php foreach ($siswaList as $s) echo '<option value="' . $s['id'] . '">' . e($s['nama'] . ' (' . $s['kelas'] . ')') . '</option>'; ?></select></div>
    <div class="form-group"><label>Jenis pelanggaran</label><select class="form-control" name="jenis_id" required><option value="">- pilih -</option><?php foreach ($jenisList as $j) echo '<option value="' . $j['id'] . '">[' . e($j['kategori']) . '] ' . e($j['nama']) . ' (-' . (int)$j['poin'] . ' poin)</option>'; ?></select></div>
    <div class="form-group"><label>Tanggal</label><input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required></div>
    <div class="form-group"><label>Keterangan</label><textarea class="form-control" name="keterangan" rows="2" maxlength="255"></textarea></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php elseif ($tab === 'jenis' && canEdit()): ?>
<div class="modal fade" id="mdlJ" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="jTitle">Jenis Pelanggaran</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="j_act" value="jenis_add"><input type="hidden" name="id" id="j_id">
    <div class="form-group"><label>Nama pelanggaran</label><input class="form-control" name="nama" id="j_nama" required maxlength="120"></div>
    <div class="row"><div class="col-xs-6"><label>Kategori</label><select class="form-control" name="kategori" id="j_kategori"><option>Ringan</option><option>Sedang</option><option>Berat</option></select></div>
      <div class="col-xs-6"><label>Poin yang dikurangi</label><input type="number" min="0" max="100" class="form-control" name="poin" id="j_poin" value="5"></div></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#mdl').modal('show'); if ($.fn.select2) $('#p_siswa').select2({ dropdownParent: $('#mdl') }); });
  $('#btnJenis').on('click', function () { $('#j_act').val('jenis_add'); $('#j_id').val(''); $('#j_nama').val(''); $('#j_poin').val(5); $('#j_kategori').val('Ringan'); $('#jTitle').text('Tambah Jenis'); $('#mdlJ').modal('show'); });
  $(document).on('click', '.btn-jenis', function () { var d = $(this).data('row'); $('#j_act').val('jenis_edit'); $('#j_id').val(d.id); $('#j_nama').val(d.nama); $('#j_kategori').val(d.kategori); $('#j_poin').val(d.poin); $('#jTitle').text('Edit Jenis'); $('#mdlJ').modal('show'); });
});
</script>