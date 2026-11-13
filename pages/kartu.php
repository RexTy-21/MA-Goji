<?php
// Cetak kartu QR (halaman mandiri, tanpa sidebar). ?tipe=siswa&kelas=ID  |  ?tipe=guru
$db = $koneksidb;
$tipe = (($_GET['tipe'] ?? 'siswa') === 'guru') ? 'guru' : 'siswa';
if ($tipe === 'guru' && !in_array(role(), mgmtRoles(), true)) { http_response_code(403); echo 'Akses ditolak'; exit; }
$items = [];
if ($tipe === 'guru') {
    foreach (qall($db, "SELECT nama, nip, jabatan, mapel FROM guru WHERE status = 'Active' ORDER BY nama") as $g)
        $items[] = ['nama' => $g['nama'], 'l2' => $g['jabatan'] ?: $g['mapel'], 'l3' => 'NIP ' . $g['nip'], 'qr' => 'GURU:' . $g['nip'], 'role' => 'GURU'];
    $judul = 'Kartu Guru';
} else {
    $kid = (int)($_GET['kelas'] ?? 0);
    $sql = "SELECT s.nama, s.nis, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.status = 'Active'" . ($kid ? ' AND s.kelas_id = ?' : '') . " ORDER BY FIELD(k.tingkat,'X','XI','XII'), k.jurusan, s.nama";
    foreach ($kid ? qall($db, $sql, 'i', [$kid]) : qall($db, $sql) as $s)
        $items[] = ['nama' => $s['nama'], 'l2' => 'Kelas ' . $s['kelas'], 'l3' => 'NIS ' . $s['nis'], 'qr' => 'SISWA:' . $s['nis'], 'role' => 'SISWA'];
    $judul = 'Kartu Siswa';
}

// Inisial untuk avatar (2 huruf pertama dari kata awal)
function inisial($nama) {
    $parts = preg_split('/\s+/', trim($nama));
    $s = mb_substr($parts[0] ?? '', 0, 1);
    if (count($parts) > 1) $s .= mb_substr($parts[1], 0, 1);
    return mb_strtoupper($s);
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><title><?= e($judul) ?> - MA Ghoyatul Jihad</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
  @page { size: A4; margin: 10mm; }
  * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  body { font-family: "Segoe UI", Arial, sans-serif; margin: 0; background: #f2faf5; }
  .bar { padding: 12px 16px; background: #fff; border-bottom: 1px solid #d6eadc; display: flex; justify-content: space-between; align-items: center; }
  .bar button { background: #16a34a; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }

  /* 3 kartu per baris, ukuran ID card portrait */
  .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 14px; max-width: 900px; margin: 0 auto; }

  .card { position: relative; background: #fff; border: 1px solid #cfe6d7; border-radius: 12px; overflow: hidden;
          break-inside: avoid; page-break-inside: avoid; display: flex; flex-direction: column; align-items: center;
          text-align: center; height: 86mm; }

  /* Lubang gantungan lanyard */
  .card::before { content: ""; position: absolute; top: 6px; left: 50%; transform: translateX(-50%);
                  width: 34px; height: 7px; border-radius: 4px; background: #f2faf5; border: 1px solid #b7d9c3; z-index: 3; }

  /* Header hijau melengkung */
  .hd { position: relative; width: 100%; background: linear-gradient(135deg, #15803d, #22c55e); color: #fff;
        padding: 18px 8px 34px; font-size: 10.5px; font-weight: 700; letter-spacing: .3px; line-height: 1.25; }
  .hd img { height: 26px; width: 26px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; display: block; margin: 0 auto 4px; }
  .hd small { display: block; font-weight: 400; font-size: 8.5px; opacity: .9; }
  .hd::after { content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 18px; background: #fff;
               border-radius: 50% 50% 0 0 / 100% 100% 0 0; }

  /* Foto profil bulat */
  .foto { position: relative; z-index: 2; margin-top: -30px; width: 64px; height: 64px; border-radius: 50%;
          background: linear-gradient(135deg, #bbf7d0, #86efac); border: 3px solid #fff; box-shadow: 0 0 0 2px #16a34a;
          display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; color: #14532d; }

  .nm { margin-top: 8px; padding: 0 10px; font-size: 13.5px; font-weight: 700; color: #0d4a26; line-height: 1.2; }
  .badge { display: inline-block; margin-top: 4px; padding: 1px 10px; border-radius: 99px; background: #dcfce7; color: #15803d;
           font-size: 9px; font-weight: 700; letter-spacing: 1px; }
  .l2 { color: #444; font-size: 11px; margin-top: 4px; }
  .l3 { color: #666; font-size: 10.5px; }

  /* QR di bawah */
  .qrwrap { margin-top: auto; margin-bottom: 8px; padding: 5px; border: 1px dashed #b7d9c3; border-radius: 8px; background: #fff; }
  .qr img, .qr canvas { display: block; }
  .ft { width: 100%; background: #16a34a; color: #fff; font-size: 8px; padding: 3px 0; letter-spacing: .4px; }

  @media print {
    .bar { display: none; } body { background: #fff; }
    .grid { padding: 0; max-width: none; gap: 6mm; }
  }
</style></head><body>
<div class="bar"><b><?= e($judul) ?> (<?= count($items) ?> kartu)</b><button onclick="window.print()">Cetak</button></div>
<div class="grid">
<?php foreach ($items as $i => $it): ?>
  <div class="card">
    <div class="hd">
      <img src="assets/img/logo.png" alt="">
      MA GHOYATUL JIHAD
      <small>Karawang</small>
    </div>
    <div class="foto"><?= e(inisial($it['nama'])) ?></div>
    <div class="nm"><?= e($it['nama']) ?></div>
    <span class="badge"><?= e($it['role']) ?></span>
    <div class="l2"><?= e($it['l2']) ?></div>
    <div class="l3"><?= e($it['l3']) ?></div>
    <div class="qrwrap"><div class="qr" id="qr<?= $i ?>" data-text="<?= e($it['qr']) ?>"></div></div>
  </div>
<?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.qr').forEach(function (el) {
  new QRCode(el, { text: el.dataset.text, width: 84, height: 84 });
});
</script>
</body></html>