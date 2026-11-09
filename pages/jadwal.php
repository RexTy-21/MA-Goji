<?php
// Jadwal Pelajaran (per kelas / per guru), periode = tahun ajaran aktif
$db = $koneksidb;
$ta = taAktif($db);
$kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
$guruList  = qall($db, "SELECT id, nama FROM guru WHERE status='Active' ORDER BY nama");
$mapelList = qall($db, "SELECT id, kode, nama FROM mapel ORDER BY nama");
$hari = hariList();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit() && $ta) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $id = (int)$f('id');
    $kelasId = (int)$f('kelas_id'); $mapelId = (int)$f('mapel_id'); $guruId = (int)$f('guru_id') ?: null;
    $h = (int)$f('hari'); $mulai = $f('jam_mulai'); $selesai = $f('jam_selesai'); $ruang = $f('ruang');
    $back = '?page=Jadwal&kelas=' . $kelasId;
    if ($act === 'del' && $id > 0) {
        qrun($db, "DELETE FROM jadwal WHERE id = ?", 'i', [$id]);
        header('Location: ' . ($f('back') ?: '?page=Jadwal')); exit;
    }
    if (in_array($act, ['add', 'edit'], true)) {
        if (!$kelasId || !$mapelId || $h < 1 || $h > 6 || !$mulai || !$selesai) $err = 'Lengkapi semua isian.';
        elseif ($mulai >= $selesai) $err = 'Jam selesai harus lebih besar dari jam mulai.';
        else {
            // bentrok: kelas yang sama ATAU guru yang sama pada jam yang beririsan
            $bentrok = qone($db, "SELECT j.id, k.nama AS kelas, g.nama AS guru FROM jadwal j
                LEFT JOIN kelas k ON k.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id
                WHERE j.tahun_ajaran_id = ? AND j.hari = ? AND j.id <> ? AND j.jam_mulai < ? AND j.jam_selesai > ?
                  AND (j.kelas_id = ? OR (? IS NOT NULL AND j.guru_id = ?)) LIMIT 1",
                'iiissiii', [$ta['id'], $h, $id, $selesai, $mulai, $kelasId, $guruId, $guruId]);
            if ($bentrok) $err = 'Jadwal bentrok dengan kelas ' . $bentrok['kelas'] . ($bentrok['guru'] ? ' (guru ' . $bentrok['guru'] . ')' : '') . ' pada jam tersebut.';
            elseif ($act === 'add') {
                qrun($db, "INSERT INTO jadwal (tahun_ajaran_id, kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai, ruang) VALUES (?,?,?,?,?,?,?,?)",
                     'iiiiisss', [$ta['id'], $kelasId, $mapelId, $guruId, $h, $mulai, $selesai, $ruang]);
                header('Location: ' . $back); exit;
            } else {
                qrun($db, "UPDATE jadwal SET kelas_id=?, mapel_id=?, guru_id=?, hari=?, jam_mulai=?, jam_selesai=?, ruang=? WHERE id=?",
                     'iiiisssi', [$kelasId, $mapelId, $guruId, $h, $mulai, $selesai, $ruang, $id]);
                header('Location: ' . $back); exit;
            }
        }
    }
}

// filter: guru login (peran GURU) otomatis melihat jadwalnya sendiri
$fKelas = (int)($_GET['kelas'] ?? 0);
$fGuru  = (int)($_GET['guru'] ?? 0);
if (!$fKelas && !$fGuru) {
    if (role() === 'GURU' && !empty($_SESSION['SES_GURU_ID'])) $fGuru = (int)$_SESSION['SES_GURU_ID'];
    elseif ($kelasList) $fKelas = (int)$kelasList[0]['id'];
}
$rows = [];
if ($ta) {
    $where = "j.tahun_ajaran_id = ?"; $types = 'i'; $p = [$ta['id']];
    if ($fGuru) { $where .= " AND j.guru_id = ?"; $types .= 'i'; $p[] = $fGuru; }
    elseif ($fKelas) { $where .= " AND j.kelas_id = ?"; $types .= 'i'; $p[] = $fKelas; }
    $rows = qall($db, "SELECT j.*, m.nama AS mapel, m.kode, k.nama AS kelas, g.nama AS guru FROM jadwal j
        JOIN mapel m ON m.id = j.mapel_id LEFT JOIN kelas k ON k.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id
        WHERE $where ORDER BY j.hari, j.jam_mulai", $types, $p);
}
$byDay = []; foreach ($rows as $r) $byDay[(int)$r['hari']][] = $r;
$back = $fGuru ? '?page=Jadwal&guru=' . $fGuru : '?page=Jadwal&kelas=' . $fKelas;
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Jadwal Pelajaran <small><?= $ta ? e($ta['nama'] . ' - ' . $ta['semester']) : 'belum ada tahun ajaran aktif' ?></small></h3></div>
    <div class="title_right">
      <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Cetak</button>
      <?php if (canEdit() && $ta): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Tambah Jadwal</button><?php endif; ?></div></div>
  <div class="clearfix"></div>
  <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
  <?php if (!$ta): ?><div class="alert alert-warning">Belum ada Tahun Ajaran aktif. Atur dulu di menu <b>Tahun Ajaran</b>.</div><?php endif; ?>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:14px"><input type="hidden" name="page" value="Jadwal">
      <label>Per Kelas&nbsp;</label>
      <select name="kelas" class="form-control" onchange="this.form.guru.value='';this.form.submit()"><option value="">- pilih -</option>
        <?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] && !$fGuru ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select>
      &nbsp;&nbsp;<label>Per Guru&nbsp;</label>
      <select name="guru" class="form-control" onchange="this.form.kelas.value='';this.form.submit()"><option value="">- pilih -</option>
        <?php foreach ($guruList as $g) echo '<option value="' . $g['id'] . '"' . ($fGuru == $g['id'] ? ' selected' : '') . '>' . e($g['nama']) . '</option>'; ?></select>
    </form>
    <div class="row">
    <?php foreach ($hari as $no => $nm): ?>
      <div class="col-md-2 col-sm-4 col-xs-12" style="margin-bottom:14px">
        <div style="background:#166534;color:#fff;padding:6px 10px;border-radius:6px 6px 0 0;font-weight:600"><?= $nm ?></div>
        <div style="border:1px solid #d1e7d9;border-top:0;border-radius:0 0 6px 6px;padding:6px;min-height:90px">
        <?php if (empty($byDay[$no])) echo '<div class="text-muted" style="padding:8px;font-size:12px">Tidak ada jadwal</div>'; ?>
        <?php foreach ($byDay[$no] ?? [] as $r): ?>
          <div style="background:#f0fdf4;border-left:3px solid #16a34a;padding:6px 8px;margin-bottom:6px;border-radius:4px;font-size:12px">
            <b><?= substr($r['jam_mulai'], 0, 5) ?>-<?= substr($r['jam_selesai'], 0, 5) ?></b><br>
            <span style="font-size:13px"><b><?= e($r['mapel']) ?></b></span><br>
            <?= $fGuru ? e($r['kelas']) : e($r['guru'] ?: '(guru belum diatur)') ?><?= $r['ruang'] ? ' &middot; ' . e($r['ruang']) : '' ?>
            <?php if (canEdit()): ?><div class="hidden-print" style="margin-top:4px">
              <button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button>
              <form method="POST" style="display:inline"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="back" value="<?= e($back) ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus jadwal ini?')">&times;</button></form></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
        </div></div>
    <?php endforeach; ?>
    </div>
  </div></div>
</div>
<?php if (canEdit() && $ta): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Jadwal</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="row"><div class="col-xs-6"><label>Kelas</label><select class="form-control" name="kelas_id" id="m_kelas_id" required><?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '">' . e($k['nama']) . '</option>'; ?></select></div>
      <div class="col-xs-6"><label>Hari</label><select class="form-control" name="hari" id="m_hari"><?php foreach ($hari as $n => $h) echo "<option value=\"$n\">$h</option>"; ?></select></div></div><br>
    <div class="row"><div class="col-xs-6"><label>Mata Pelajaran</label><select class="form-control" name="mapel_id" id="m_mapel_id" required><?php foreach ($mapelList as $m) echo '<option value="' . $m['id'] . '">' . e($m['nama']) . '</option>'; ?></select></div>
      <div class="col-xs-6"><label>Guru</label><select class="form-control" name="guru_id" id="m_guru_id"><option value="">-</option><?php foreach ($guruList as $g) echo '<option value="' . $g['id'] . '">' . e($g['nama']) . '</option>'; ?></select></div></div><br>
    <div class="row"><div class="col-xs-4"><label>Mulai</label><input type="time" class="form-control" name="jam_mulai" id="m_jam_mulai" required></div>
      <div class="col-xs-4"><label>Selesai</label><input type="time" class="form-control" name="jam_selesai" id="m_jam_selesai" required></div>
      <div class="col-xs-4"><label>Ruang</label><input class="form-control" name="ruang" id="m_ruang"></div></div>
    <p class="help-block">Sistem menolak jadwal yang bentrok (kelas atau guru yang sama di jam beririsan).</p>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  var kls = <?= (int)$fKelas ?>;
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_jam_mulai,#m_jam_selesai,#m_ruang').val(''); if (kls) $('#m_kelas_id').val(kls); $('#m_guru_id').val(''); $('#mTitle').text('Tambah Jadwal'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id); $('#m_kelas_id').val(d.kelas_id); $('#m_hari').val(d.hari); $('#m_mapel_id').val(d.mapel_id);
    $('#m_guru_id').val(d.guru_id || ''); $('#m_jam_mulai').val(d.jam_mulai.substr(0, 5)); $('#m_jam_selesai').val(d.jam_selesai.substr(0, 5)); $('#m_ruang').val(d.ruang || '');
    $('#mTitle').text('Edit Jadwal'); $('#mdl').modal('show');
  });
  <?php if ($err && ($_POST['act'] ?? '')): ?>$('#mdl').modal('show');<?php endif; ?>
});
</script>