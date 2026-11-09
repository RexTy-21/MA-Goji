<?php
// Input Nilai per kelas & mapel (tahun ajaran aktif). Guru hanya mapel/kelas yang ia ajar (berdasarkan Jadwal).
$db = $koneksidb;
$ta = taAktif($db);
$isMgmt = in_array(role(), mgmtRoles(), true);
$myGuru = (int)($_SESSION['SES_GURU_ID'] ?? 0);
$fKelas = (int)($_GET['kelas'] ?? 0); $fMapel = (int)($_GET['mapel'] ?? 0);

if ($isMgmt) {
    $kelasList = qall($db, "SELECT id, nama FROM kelas ORDER BY FIELD(tingkat,'X','XI','XII'), jurusan");
} else {
    $kelasList = ($ta && $myGuru) ? qall($db, "SELECT DISTINCT k.id, k.nama FROM jadwal j JOIN kelas k ON k.id = j.kelas_id WHERE j.guru_id = ? AND j.tahun_ajaran_id = ? ORDER BY k.nama", 'ii', [$myGuru, $ta['id']]) : [];
}
function mapelBoleh($db, $isMgmt, $myGuru, $ta, $kelas) {
    if ($isMgmt) return qall($db, "SELECT id, nama, kkm FROM mapel ORDER BY nama");
    if (!$ta || !$myGuru) return [];
    return qall($db, "SELECT DISTINCT m.id, m.nama, m.kkm FROM jadwal j JOIN mapel m ON m.id = j.mapel_id WHERE j.guru_id = ? AND j.kelas_id = ? AND j.tahun_ajaran_id = ? ORDER BY m.nama", 'iii', [$myGuru, $kelas, $ta['id']]);
}
$mapelList = $fKelas ? mapelBoleh($db, $isMgmt, $myGuru, $ta, $fKelas) : [];
$mapelSel = null; foreach ($mapelList as $m) if ($m['id'] == $fMapel) $mapelSel = $m;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ta && $mapelSel) {
    $num = function ($v) { $v = str_replace(',', '.', trim((string)$v)); return ($v === '' || !is_numeric($v)) ? null : max(0, min(100, (float)$v)); };
    $n = 0;
    foreach (($_POST['n'] ?? []) as $sid => $v) {
        $sid = (int)$sid;
        if (!qone($db, "SELECT id FROM siswa WHERE id = ? AND kelas_id = ? AND status = 'Active'", 'ii', [$sid, $fKelas])) continue;
        $t = $num($v['tugas'] ?? ''); $u = $num($v['uts'] ?? ''); $a = $num($v['uas'] ?? '');
        if ($t === null && $u === null && $a === null) { qrun($db, "DELETE FROM nilai WHERE tahun_ajaran_id=? AND siswa_id=? AND mapel_id=?", 'iii', [$ta['id'], $sid, $fMapel]); continue; }
        qrun($db, "INSERT INTO nilai (tahun_ajaran_id, siswa_id, mapel_id, tugas, uts, uas, diubah_oleh, diubah_pada) VALUES (?,?,?,?,?,?,?,NOW())
                   ON DUPLICATE KEY UPDATE tugas=VALUES(tugas), uts=VALUES(uts), uas=VALUES(uas), diubah_oleh=VALUES(diubah_oleh), diubah_pada=NOW()",
             'iiiddds', [$ta['id'], $sid, $fMapel, $t, $u, $a, namaUser()]);
        $n++;
    }
    header('Location: ?page=Input-Nilai&kelas=' . $fKelas . '&mapel=' . $fMapel . '&msg=' . urlencode("Nilai $n siswa disimpan.")); exit;
}
$siswa = []; $nilai = [];
if ($ta && $mapelSel) {
    $siswa = qall($db, "SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 'Active' ORDER BY nama", 'i', [$fKelas]);
    foreach (qall($db, "SELECT * FROM nilai WHERE tahun_ajaran_id = ? AND mapel_id = ?", 'ii', [$ta['id'], $fMapel]) as $r) $nilai[$r['siswa_id']] = $r;
}
include "header.php";
$fmt = fn($v) => $v === null ? '' : rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Input Nilai <small><?= $ta ? e($ta['nama'] . ' - ' . $ta['semester']) : '' ?></small></h3></div></div>
  <div class="clearfix"></div>
  <?php if (!$ta): ?><div class="alert alert-warning">Belum ada Tahun Ajaran aktif (menu Tahun Ajaran).</div><?php endif; ?>
  <?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
  <?php if (!$isMgmt && !$kelasList): ?><div class="alert alert-info">Belum ada jadwal mengajar untuk akun Anda, jadi belum ada kelas yang bisa dinilai. Minta TU mengisi Jadwal Pelajaran.</div><?php endif; ?>
  <div class="x_panel"><div class="x_content">
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Input-Nilai">
      <label>Kelas&nbsp;</label><select name="kelas" class="form-control" onchange="this.form.mapel.value='';this.form.submit()"><option value="">- pilih -</option>
        <?php foreach ($kelasList as $k) echo '<option value="' . $k['id'] . '"' . ($fKelas == $k['id'] ? ' selected' : '') . '>' . e($k['nama']) . '</option>'; ?></select>
      &nbsp;<label>Mata Pelajaran&nbsp;</label><select name="mapel" class="form-control" onchange="this.form.submit()"><option value="">- pilih -</option>
        <?php foreach ($mapelList as $m) echo '<option value="' . $m['id'] . '"' . ($fMapel == $m['id'] ? ' selected' : '') . '>' . e($m['nama']) . '</option>'; ?></select></form>
    <?php if ($mapelSel): ?>
    <p class="text-muted">Nilai akhir = Tugas 30% + UTS 30% + UAS 40%. KKM <b><?= (int)$mapelSel['kkm'] ?></b>. Kosongkan semua kolom untuk menghapus nilai siswa. Gunakan angka 0–100.</p>
    <form method="POST" id="frmNilai"><div class="table-responsive"><table class="table table-bordered table-striped table-condensed">
      <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th style="width:110px">Tugas</th><th style="width:110px">UTS</th><th style="width:110px">UAS</th><th class="text-center">Akhir</th><th class="text-center">Predikat</th></tr></thead>
      <tbody><?php if (!$siswa) echo '<tr><td colspan="8" class="text-center text-muted">Tidak ada siswa aktif.</td></tr>'; ?>
      <?php foreach ($siswa as $i => $s): $v = $nilai[$s['id']] ?? []; ?>
        <tr data-kkm="<?= (int)$mapelSel['kkm'] ?>"><td><?= $i + 1 ?></td><td><?= e($s['nis']) ?></td><td><?= e($s['nama']) ?></td>
          <?php foreach (['tugas', 'uts', 'uas'] as $c): ?><td><input type="number" step="0.01" min="0" max="100" class="form-control input-sm sc-in" name="n[<?= $s['id'] ?>][<?= $c ?>]" value="<?= $fmt($v[$c] ?? null) ?>"></td><?php endforeach; ?>
          <td class="text-center sc-akhir"><b>-</b></td><td class="text-center sc-pred">-</td></tr>
      <?php endforeach; ?></tbody></table></div>
      <?php if ($siswa): ?><button class="btn btn-primary"><i class="fa fa-save"></i> Simpan Nilai</button><?php endif; ?></form>
    <?php endif; ?>
  </div></div>
</div>
<?php include "footer.php"; ?>
<script>
$(function () {
  function hitung(tr) {
    var v = tr.find('.sc-in').map(function () { return this.value === '' ? null : parseFloat(this.value); }).get();
    var cell = tr.find('.sc-akhir b'), pred = tr.find('.sc-pred');
    if (v.some(function (x) { return x === null || isNaN(x); })) { cell.text('-').css('color', ''); pred.text('-'); return; }
    var a = Math.round((v[0] * 0.3 + v[1] * 0.3 + v[2] * 0.4) * 10) / 10;
    cell.text(a).css('color', a < tr.data('kkm') ? '#dc2626' : '#166534');
    pred.text(a >= 90 ? 'A' : a >= 80 ? 'B' : a >= 70 ? 'C' : 'D');
  }
  $('#frmNilai tbody tr').each(function () { hitung($(this)); });
  $(document).on('input', '.sc-in', function () { hitung($(this).closest('tr')); });
});
</script>