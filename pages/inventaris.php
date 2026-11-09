<?php
// Inventaris sarana & prasarana: daftar barang + peminjaman
$db = $koneksidb;
$tab = ($_GET['tab'] ?? '') === 'pinjam' ? 'pinjam' : 'barang';
$kategori = ['Elektronik', 'Mebel', 'Olahraga', 'Buku & Alat Tulis', 'Laboratorium', 'Kendaraan', 'Kebersihan', 'Lainnya'];
$kondisi = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
$pesan = $_GET['msg'] ?? '';
$tersediaSql = "(b.jumlah - COALESCE((SELECT SUM(p.jumlah) FROM inventaris_pinjam p WHERE p.barang_id = b.id AND p.status = 'Dipinjam'), 0))";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && canEdit()) {
    $f = fn($k) => trim($_POST[$k] ?? '');
    $act = $f('act'); $id = (int)$f('id'); $go = '?page=Inventaris'; $msg = '';
    if (in_array($act, ['add', 'edit'], true)) {
        $kat = in_array($f('kategori'), $kategori, true) ? $f('kategori') : 'Lainnya'; $kon = in_array($f('kondisi'), $kondisi, true) ? $f('kondisi') : 'Baik';
        $jml = max(0, (int)$f('jumlah')); $thn = (int)$f('tahun') ?: null; $hrg = $f('harga') !== '' ? max(0, (float)$f('harga')) : null;
        $kode = $f('kode') !== '' ? strtoupper($f('kode')) : 'INV-' . str_pad((string)(((int)(qone($db, "SELECT MAX(id) m FROM inventaris_barang")['m'] ?? 0)) + 1), 4, '0', STR_PAD_LEFT);
        if ($f('nama') === '') $msg = 'Nama barang wajib diisi.';
        elseif ($act === 'add') $msg = qrun($db, "INSERT INTO inventaris_barang (kode, nama, kategori, lokasi, jumlah, kondisi, tahun, harga, keterangan) VALUES (?,?,?,?,?,?,?,?,?)",
                                          'ssssisids', [$kode, $f('nama'), $kat, $f('lokasi'), $jml, $kon, $thn, $hrg, $f('keterangan')]) ? 'Barang ditambahkan.' : 'Gagal: kode barang mungkin sudah dipakai.';
        else $msg = qrun($db, "UPDATE inventaris_barang SET kode=?, nama=?, kategori=?, lokasi=?, jumlah=?, kondisi=?, tahun=?, harga=?, keterangan=? WHERE id=?",
                         'ssssisidsi', [$kode, $f('nama'), $kat, $f('lokasi'), $jml, $kon, $thn, $hrg, $f('keterangan'), $id]) ? 'Barang diperbarui.' : 'Gagal menyimpan (kode ganda?).';
    } elseif ($act === 'del' && $id) {
        $c = qone($db, "SELECT COUNT(*) n FROM inventaris_pinjam WHERE barang_id = ?", 'i', [$id]);
        if ((int)$c['n']) $msg = 'Barang punya riwayat peminjaman, tidak bisa dihapus.'; else { qrun($db, "DELETE FROM inventaris_barang WHERE id = ?", 'i', [$id]); $msg = 'Barang dihapus.'; }
    } elseif ($act === 'pinjam') {
        $go = '?page=Inventaris&tab=pinjam'; $bid = (int)$f('barang_id'); $jml = (int)$f('jumlah');
        $b = qone($db, "SELECT b.id, $tersediaSql AS sedia FROM inventaris_barang b WHERE b.id = ?", 'i', [$bid]);
        $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('tgl_pinjam')) ? $f('tgl_pinjam') : date('Y-m-d'); $rc = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('tgl_rencana')) ? $f('tgl_rencana') : null;
        if (!$b || $f('peminjam') === '' || $jml < 1) $msg = 'Lengkapi data peminjaman.';
        elseif ($jml > (int)$b['sedia']) $msg = 'Stok tersedia hanya ' . (int)$b['sedia'] . '.';
        else { qrun($db, "INSERT INTO inventaris_pinjam (barang_id, peminjam, jumlah, tgl_pinjam, tgl_rencana, status, keterangan, dicatat_oleh) VALUES (?,?,?,?,?,'Dipinjam',?,?)",
                    'isissss', [$bid, $f('peminjam'), $jml, $tgl, $rc, $f('keterangan'), namaUser()]); $msg = 'Peminjaman dicatat.'; }
    } elseif ($act === 'kembali' && $id) {
        $go = '?page=Inventaris&tab=pinjam'; qrun($db, "UPDATE inventaris_pinjam SET status = 'Kembali', tgl_kembali = CURDATE() WHERE id = ? AND status = 'Dipinjam'", 'i', [$id]); $msg = 'Barang dikembalikan.';
    }
    header('Location: ' . $go . '&msg=' . urlencode($msg)); exit;
}
$fKat = $_GET['kategori'] ?? ''; $fKon = $_GET['kondisi'] ?? ''; $q = trim($_GET['q'] ?? '');
$w = []; $ty = ''; $pp = [];
if (in_array($fKat, $kategori, true)) { $w[] = 'b.kategori = ?'; $ty .= 's'; $pp[] = $fKat; }
if (in_array($fKon, $kondisi, true)) { $w[] = 'b.kondisi = ?'; $ty .= 's'; $pp[] = $fKon; }
if ($q !== '') { $w[] = '(b.nama LIKE ? OR b.kode LIKE ? OR b.lokasi LIKE ?)'; $ty .= 'sss'; array_push($pp, "%$q%", "%$q%", "%$q%"); }
$barang = qall($db, "SELECT b.*, $tersediaSql AS sedia FROM inventaris_barang b " . ($w ? 'WHERE ' . implode(' AND ', $w) : '') . " ORDER BY b.kategori, b.nama", $ty, $pp);
$sum = qone($db, "SELECT COUNT(*) jenis, COALESCE(SUM(jumlah),0) unit, COALESCE(SUM(CASE WHEN kondisi <> 'Baik' THEN jumlah END),0) rusak,
    (SELECT COALESCE(SUM(jumlah),0) FROM inventaris_pinjam WHERE status = 'Dipinjam') AS dipinjam FROM inventaris_barang");
$pinjam = $tab === 'pinjam' ? qall($db, "SELECT p.*, b.nama AS barang, b.kode FROM inventaris_pinjam p JOIN inventaris_barang b ON b.id = p.barang_id ORDER BY p.status = 'Dipinjam' DESC, p.id DESC LIMIT 200") : [];
$barangAll = $tab === 'pinjam' ? qall($db, "SELECT b.id, b.kode, b.nama, $tersediaSql AS sedia FROM inventaris_barang b HAVING sedia > 0 ORDER BY b.nama") : [];
$konCls = ['Baik' => 'success', 'Rusak Ringan' => 'warning', 'Rusak Berat' => 'danger'];
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Inventaris</h3></div>
    <div class="title_right"><?php if (canEdit()): ?><?php if ($tab === 'barang'): ?><button class="btn btn-primary btn-sm" id="btnAdd"><i class="fa fa-plus"></i> Tambah Barang</button>
      <?php else: ?><button class="btn btn-primary btn-sm" id="btnPinjam"><i class="fa fa-plus"></i> Catat Peminjaman</button><?php endif; endif; ?></div></div>
  <div class="clearfix"></div>
  <?php if ($pesan): ?><div class="alert alert-info"><?= e($pesan) ?></div><?php endif; ?>
  <div class="row" style="margin-bottom:10px">
    <?php foreach ([['Jenis Barang', $sum['jenis']], ['Total Unit', $sum['unit']], ['Unit Rusak', $sum['rusak']], ['Sedang Dipinjam', $sum['dipinjam']]] as $c): ?>
    <div class="col-sm-3 col-xs-6"><div class="x_panel" style="padding:10px 14px;margin-bottom:8px;border-left:4px solid #16a34a"><div style="font-size:22px;font-weight:700;color:#166534"><?= (int)$c[1] ?></div><div class="text-muted"><?= $c[0] ?></div></div></div><?php endforeach; ?>
  </div>
  <ul class="nav nav-tabs" style="margin-bottom:12px"><li class="<?= $tab === 'barang' ? 'active' : '' ?>"><a href="?page=Inventaris">Daftar Barang</a></li><li class="<?= $tab === 'pinjam' ? 'active' : '' ?>"><a href="?page=Inventaris&tab=pinjam">Peminjaman</a></li></ul>
  <div class="x_panel"><div class="x_content table-responsive">
  <?php if ($tab === 'barang'): ?>
    <form method="GET" class="form-inline" style="margin-bottom:12px"><input type="hidden" name="page" value="Inventaris">
      <input name="q" class="form-control input-sm" placeholder="Cari nama / kode / lokasi" value="<?= e($q) ?>">
      <select name="kategori" class="form-control input-sm" onchange="this.form.submit()"><option value="">Semua kategori</option><?php foreach ($kategori as $k) echo '<option' . ($fKat === $k ? ' selected' : '') . '>' . $k . '</option>'; ?></select>
      <select name="kondisi" class="form-control input-sm" onchange="this.form.submit()"><option value="">Semua kondisi</option><?php foreach ($kondisi as $k) echo '<option' . ($fKon === $k ? ' selected' : '') . '>' . $k . '</option>'; ?></select>
      <button class="btn btn-default btn-sm">Cari</button></form>
    <table class="table table-bordered table-striped table-condensed"><thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Lokasi</th><th class="text-center">Jumlah</th><th class="text-center">Tersedia</th><th>Kondisi</th><th>Tahun</th><?php if (canEdit()): ?><th>Aksi</th><?php endif; ?></tr></thead>
      <tbody><?php if (!$barang) echo '<tr><td colspan="9" class="text-center text-muted">Tidak ada data.</td></tr>'; ?>
      <?php foreach ($barang as $r): ?><tr><td><?= e($r['kode']) ?></td><td><b><?= e($r['nama']) ?></b><?= $r['keterangan'] ? '<br><small class="text-muted">' . e($r['keterangan']) . '</small>' : '' ?></td><td><?= e($r['kategori']) ?></td><td><?= e($r['lokasi']) ?></td>
        <td class="text-center"><?= (int)$r['jumlah'] ?></td><td class="text-center"><?= (int)$r['sedia'] ?></td><td><span class="label label-<?= $konCls[$r['kondisi']] ?? 'default' ?>"><?= e($r['kondisi']) ?></span></td><td><?= e($r['tahun']) ?></td>
        <?php if (canEdit()): ?><td><button class="btn btn-warning btn-xs btn-edit" data-row="<?= e(json_encode($r)) ?>">Edit</button>
          <form method="POST" style="display:inline"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-xs" onclick="return confirm('Hapus barang ini?')">Hapus</button></form></td><?php endif; ?></tr><?php endforeach; ?></tbody></table>
  <?php else: ?>
    <table class="table table-bordered table-striped table-condensed"><thead><tr><th>Barang</th><th>Peminjam</th><th class="text-center">Jml</th><th>Dipinjam</th><th>Rencana Kembali</th><th>Status</th><?php if (canEdit()): ?><th></th><?php endif; ?></tr></thead>
      <tbody><?php if (!$pinjam) echo '<tr><td colspan="7" class="text-center text-muted">Belum ada peminjaman.</td></tr>'; ?>
      <?php foreach ($pinjam as $r): $telat = $r['status'] === 'Dipinjam' && $r['tgl_rencana'] && $r['tgl_rencana'] < date('Y-m-d'); ?><tr><td><b><?= e($r['barang']) ?></b><br><small class="text-muted"><?= e($r['kode']) ?></small></td><td><?= e($r['peminjam']) ?><?= $r['keterangan'] ? '<br><small class="text-muted">' . e($r['keterangan']) . '</small>' : '' ?></td><td class="text-center"><?= (int)$r['jumlah'] ?></td>
        <td><?= date('d/m/Y', strtotime($r['tgl_pinjam'])) ?></td><td><?= $r['tgl_rencana'] ? date('d/m/Y', strtotime($r['tgl_rencana'])) : '-' ?></td>
        <td><?php if ($r['status'] === 'Kembali'): ?><span class="label label-default">Kembali <?= date('d/m/Y', strtotime($r['tgl_kembali'])) ?></span><?php else: ?><span class="label label-<?= $telat ? 'danger' : 'warning' ?>"><?= $telat ? 'Terlambat' : 'Dipinjam' ?></span><?php endif; ?></td>
        <?php if (canEdit()): ?><td><?php if ($r['status'] === 'Dipinjam'): ?><form method="POST"><input type="hidden" name="act" value="kembali"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-success btn-xs" onclick="return confirm('Tandai sudah dikembalikan?')">Dikembalikan</button></form><?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table>
  <?php endif; ?>
  </div></div>
</div>
<?php if (canEdit() && $tab === 'barang'): ?>
<div class="modal fade" id="mdl" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mTitle">Barang</h4></div>
  <div class="modal-body"><input type="hidden" name="act" id="m_act" value="add"><input type="hidden" name="id" id="m_id">
    <div class="row"><div class="col-xs-4"><label>Kode</label><input class="form-control" name="kode" id="m_kode" placeholder="otomatis"></div><div class="col-xs-8"><label>Nama Barang</label><input class="form-control" name="nama" id="m_nama" required></div></div><br>
    <div class="row"><div class="col-xs-6"><label>Kategori</label><select class="form-control" name="kategori" id="m_kategori"><?php foreach ($kategori as $k) echo "<option>$k</option>"; ?></select></div><div class="col-xs-6"><label>Lokasi / Ruang</label><input class="form-control" name="lokasi" id="m_lokasi"></div></div><br>
    <div class="row"><div class="col-xs-4"><label>Jumlah</label><input type="number" min="0" class="form-control" name="jumlah" id="m_jumlah" value="1"></div><div class="col-xs-4"><label>Kondisi</label><select class="form-control" name="kondisi" id="m_kondisi"><?php foreach ($kondisi as $k) echo "<option>$k</option>"; ?></select></div><div class="col-xs-4"><label>Tahun</label><input type="number" class="form-control" name="tahun" id="m_tahun"></div></div><br>
    <div class="row"><div class="col-xs-5"><label>Harga satuan (Rp)</label><input type="number" min="0" class="form-control" name="harga" id="m_harga"></div><div class="col-xs-7"><label>Keterangan</label><input class="form-control" name="keterangan" id="m_keterangan" maxlength="255"></div></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php elseif (canEdit() && $tab === 'pinjam'): ?>
<div class="modal fade" id="mdlP" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST">
  <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Catat Peminjaman</h4></div>
  <div class="modal-body"><input type="hidden" name="act" value="pinjam">
    <div class="form-group"><label>Barang (hanya yang tersedia)</label><select class="form-control" name="barang_id" required><option value="">- pilih -</option><?php foreach ($barangAll as $b) echo '<option value="' . $b['id'] . '">' . e($b['kode'] . ' - ' . $b['nama']) . ' (tersedia ' . (int)$b['sedia'] . ')</option>'; ?></select></div>
    <div class="row"><div class="col-xs-8"><label>Peminjam</label><input class="form-control" name="peminjam" required maxlength="120" placeholder="Nama guru / siswa / kelas"></div><div class="col-xs-4"><label>Jumlah</label><input type="number" min="1" class="form-control" name="jumlah" value="1"></div></div><br>
    <div class="row"><div class="col-xs-6"><label>Tgl pinjam</label><input type="date" class="form-control" name="tgl_pinjam" value="<?= date('Y-m-d') ?>"></div><div class="col-xs-6"><label>Rencana kembali</label><input type="date" class="form-control" name="tgl_rencana"></div></div><br>
    <div class="form-group"><label>Keterangan</label><input class="form-control" name="keterangan" maxlength="255"></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<?php endif; include "footer.php"; ?>
<script>
$(function () {
  $('#btnAdd').on('click', function () { $('#m_act').val('add'); $('#m_id').val(''); $('#m_kode,#m_nama,#m_lokasi,#m_tahun,#m_harga,#m_keterangan').val(''); $('#m_jumlah').val(1); $('#m_kategori').val('Elektronik'); $('#m_kondisi').val('Baik'); $('#mTitle').text('Tambah Barang'); $('#mdl').modal('show'); });
  $(document).on('click', '.btn-edit', function () {
    var d = $(this).data('row'); $('#m_act').val('edit'); $('#m_id').val(d.id);
    $.each(['kode', 'nama', 'kategori', 'lokasi', 'jumlah', 'kondisi', 'tahun', 'keterangan'], function (_, k) { $('#m_' + k).val(d[k] || ''); });
    $('#m_harga').val(d.harga ? Math.round(d.harga) : ''); $('#mTitle').text('Edit Barang'); $('#mdl').modal('show');
  });
  $('#btnPinjam').on('click', function () { $('#mdlP').modal('show'); });
});
</script>