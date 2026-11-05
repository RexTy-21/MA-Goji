<?php
// Cetak kartu QR (halaman mandiri, tanpa sidebar). ?tipe=siswa&kelas=ID  |  ?tipe=guru
$db = $koneksidb;
$tipe = (($_GET['tipe'] ?? 'siswa') === 'guru') ? 'guru' : 'siswa';
if ($tipe === 'guru' && !in_array(role(), mgmtRoles(), true)) { http_response_code(403); echo 'Akses ditolak'; exit; }
$items = [];
if ($tipe === 'guru') {
    foreach (qall($db, "SELECT nama, nip, jabatan, mapel FROM guru WHERE status = 'Active' ORDER BY nama") as $g)
        $items[] = ['nama' => $g['nama'], 'l2' => $g['jabatan'] ?: $g['mapel'], 'l3' => 'NIP ' . $g['nip'], 'qr' => 'GURU:' . $g['nip']];
    $judul = 'Kartu Guru';
} else {
    $kid = (int)($_GET['kelas'] ?? 0);
    $sql = "SELECT s.nama, s.nis, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.status = 'Active'" . ($kid ? ' AND s.kelas_id = ?' : '') . " ORDER BY FIELD(k.tingkat,'X','XI','XII'), k.jurusan, s.nama";
    foreach ($kid ? qall($db, $sql, 'i', [$kid]) : qall($db, $sql) as $s)
        $items[] = ['nama' => $s['nama'], 'l2' => 'Kelas ' . $s['kelas'], 'l3' => 'NIS ' . $s['nis'], 'qr' => 'SISWA:' . $s['nis']];
    $judul = 'Kartu Siswa';
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><title><?= e($judul) ?> - MA Ghoyatul Jihad</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
  @page { size: A4; margin: 10mm; }
  body { font-family: "Segoe UI", Arial, sans-serif; margin: 0; background: #f2faf5; }
  .bar { padding: 12px 16px; background: #fff; border-bottom: 1px solid #d6eadc; display: flex; justify-content: space-between; align-items: center; }
  .bar button { background: #16a34a; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
  .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 14px; max-width: 800px; margin: 0 auto; }
  .card { background: #fff; border: 2px solid #16a34a; border-radius: 10px; overflow: hidden; page-break-inside: avoid; break-inside: avoid; display: flex; flex-direction: column; }
  .card .hd { background: #16a34a; color: #fff; padding: 6px 12px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; }
  .card .hd img { height: 24px; background: #fff; border-radius: 50%; }
  .card .bd { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; gap: 8px; }
  .nm { font-size: 15px; font-weight: 700; color: #0d4a26; } .l2 { color: #444; font-size: 13px; } .l3 { color: #666; font-size: 12px; margin-top: 4px; }
  .qr { flex: none; }
  @media print { .bar { display: none; } body { background: #fff; } .grid { padding: 0; max-width: none; } }
</style></head><body>
<div class="bar"><b><?= e($judul) ?> (<?= count($items) ?> kartu)</b><button onclick="window.print()">Cetak</button></div>
<div class="grid">
<?php foreach ($items as $i => $it): ?>
  <div class="card"><div class="hd"><img src="assets/img/logo.png" alt="">MA Ghoyatul Jihad Karawang</div>
    <div class="bd"><div><div class="nm"><?= e($it['nama']) ?></div><div class="l2"><?= e($it['l2']) ?></div><div class="l3"><?= e($it['l3']) ?></div></div>
      <div class="qr" id="qr<?= $i ?>" data-text="<?= e($it['qr']) ?>"></div></div></div>
<?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.qr').forEach(function (el) { new QRCode(el, { text: el.dataset.text, width: 92, height: 92 }); });
</script>
</body></html>
