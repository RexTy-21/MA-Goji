<?php
// Scan QR absensi untuk SISWA dan GURU. QR berisi "SISWA:<nis>" atau "GURU:<nip>".
$db = $koneksidb;
$BATAS = '07:00:00';   // masuk setelah jam ini = Terlambat
$TOLERANSI_MENIT = 15; // siswa yang masuk lebih dari ini setelah $BATAS otomatis dikurangi poin kedisiplinannya
$NAMA_JENIS_OTOMATIS = 'Terlambat lebih dari 15 menit';   // harus ada di tabel jenis_pelanggaran (lihat SQL)

function potongPoinTerlambat($db, $siswaId, $jam, $batas, $menit, $namaJenis) {
    if ($jam <= date('H:i:s', strtotime($batas) + $menit * 60)) return 0;
    $j = qone($db, "SELECT id, poin FROM jenis_pelanggaran WHERE nama = ? LIMIT 1", 's', [$namaJenis]);
    if (!$j) return 0;
    $tgl = date('Y-m-d');
    if (qone($db, "SELECT id FROM pelanggaran_siswa WHERE siswa_id = ? AND jenis_id = ? AND tanggal = ?", 'iis', [$siswaId, $j['id'], $tgl])) return 0;   // sekali per hari
    qrun($db, "INSERT INTO pelanggaran_siswa (siswa_id, jenis_id, tanggal, poin, keterangan, dicatat_oleh, dibuat) VALUES (?,?,?,?,?,?,NOW())",
         'iisiss', [$siswaId, $j['id'], $tgl, (int)$j['poin'], 'Otomatis: scan masuk pukul ' . substr($jam, 0, 5), 'Sistem']);
    return (int)$j['poin'];
}

function catat($db, $tbl, $fk, $id, $batas) {
    $tgl = date('Y-m-d'); $jam = date('H:i:s');
    $row = qone($db, "SELECT id, jam_masuk, jam_pulang FROM $tbl WHERE $fk = ? AND tanggal = ?", 'is', [$id, $tgl]);
    if (!$row) {
        $stt = $jam > $batas ? 'Terlambat' : 'Hadir';
        qrun($db, "INSERT INTO $tbl ($fk, tanggal, jam_masuk, status) VALUES (?,?,?,?)", 'isss', [$id, $tgl, $jam, $stt]);
        return ['tipe' => 'MASUK', 'jam' => substr($jam, 0, 5), 'status' => $stt];
    }
    if ($row['jam_masuk'] === null) {            // sebelumnya ditandai izin/sakit/alpa, lalu ternyata hadir
        $stt = $jam > $batas ? 'Terlambat' : 'Hadir';
        qrun($db, "UPDATE $tbl SET jam_masuk = ?, status = ? WHERE id = ?", 'ssi', [$jam, $stt, $row['id']]);
        return ['tipe' => 'MASUK', 'jam' => substr($jam, 0, 5), 'status' => $stt];
    }
    if ($row['jam_pulang'] === null) {
        qrun($db, "UPDATE $tbl SET jam_pulang = ? WHERE id = ?", 'si', [$jam, $row['id']]);
        return ['tipe' => 'PULANG', 'jam' => substr($jam, 0, 5), 'status' => 'Pulang'];
    }
    return null;
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $code = trim($_POST['code'] ?? ''); $tipe = ''; $id = $code;
    if (preg_match('/^(SISWA|GURU):(.+)$/i', $code, $m)) { $tipe = strtolower($m[1]); $id = trim($m[2]); }
    $s = $g = null;
    if ($tipe !== 'guru') $s = qone($db, "SELECT s.id, s.nama, s.status, k.nama AS kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.nis = ? LIMIT 1", 's', [$id]);
    if (!$s && $tipe !== 'siswa') $g = qone($db, "SELECT id, nama, status, jabatan FROM guru WHERE nip = ? LIMIT 1", 's', [$id]);
    if (!$s && !$g) { echo json_encode(['ok' => false, 'msg' => 'QR / nomor tidak dikenali.']); exit; }
    if ($s) {
        if ($s['status'] !== 'Active') { echo json_encode(['ok' => false, 'msg' => $s['nama'] . ' berstatus ' . $s['status'] . '.']); exit; }
        $r = catat($db, 'absensi_siswa', 'siswa_id', (int)$s['id'], $BATAS);
        $nama = $s['nama']; $ket = 'Siswa - Kelas ' . $s['kelas'];
        if ($r && $r['tipe'] === 'MASUK') {
            $potong = potongPoinTerlambat($db, (int)$s['id'], date('H:i:s'), $BATAS, $TOLERANSI_MENIT, $NAMA_JENIS_OTOMATIS);
            if ($potong) $ket .= ' | Poin disiplin -' . $potong . ' (terlambat > ' . $TOLERANSI_MENIT . ' menit)';
        }
    } else {
        if ($g['status'] !== 'Active') { echo json_encode(['ok' => false, 'msg' => $g['nama'] . ' berstatus NonActive.']); exit; }
        $r = catat($db, 'absensi_guru', 'guru_id', (int)$g['id'], $BATAS);
        $nama = $g['nama']; $ket = 'Guru - ' . $g['jabatan'];
    }
    if (!$r) { echo json_encode(['ok' => false, 'msg' => $nama . ' sudah absen masuk dan pulang hari ini.']); exit; }
    echo json_encode(['ok' => true, 'tipe' => $r['tipe'], 'nama' => $nama, 'ket' => $ket, 'jam' => $r['jam'], 'status' => $r['status']]);
    exit;
}
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3>Scan Absen <small>QR Code Siswa &amp; Guru</small></h3></div></div>
  <div class="clearfix"></div>
  <div class="row">
    <div class="col-md-6"><div class="x_panel"><div class="x_title"><h2>Kamera</h2><div class="clearfix"></div></div><div class="x_content">
      <div id="reader" style="width:100%"></div>
      <p class="text-muted" style="margin-top:8px">Arahkan QR di kartu ke kamera. Kamera hanya jalan di <code>http://localhost/...</code> atau HTTPS. Batas masuk: <b>07:00</b>.</p>
      <hr><label>Atau ketik NIS / NIP (cadangan):</label>
      <div class="input-group"><input id="manual" class="form-control" placeholder="mis. 2526001"><span class="input-group-btn"><button id="btnManual" class="btn btn-primary">Absen</button></span></div>
    </div></div></div>
    <div class="col-md-6"><div class="x_panel"><div class="x_title"><h2>Hasil Scan</h2><div class="clearfix"></div></div><div class="x_content">
      <div id="hasil" class="well" style="font-size:16px;min-height:120px">Menunggu scan...</div>
      <h4>Riwayat sesi ini</h4><ul id="riwayat" class="list-unstyled"></ul>
    </div></div></div>
  </div>
</div>
<?php include "footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
$(function () {
  var last = '', lastAt = 0;
  function esc(s) { return $('<i>').text(s == null ? '' : s).html(); }
  function kirim(code) {
    $.post('?page=Absen-Scan&ajax=1', { code: code }, function (r) {
      if (r.ok) {
        var w = r.tipe === 'MASUK' ? (r.status === 'Terlambat' ? '#f39c12' : '#16a34a') : '#0a8cff';
        $('#hasil').css('border-left', '6px solid ' + w).html('<b style="font-size:20px">' + esc(r.tipe) + ' - ' + esc(r.nama) + '</b><br>' + esc(r.ket) + '<br>Jam ' + esc(r.jam) + ' &middot; ' + esc(r.status));
        $('#riwayat').prepend('<li>' + esc(r.jam) + ' &middot; ' + esc(r.nama) + ' (' + esc(r.tipe) + ')</li>');
      } else { $('#hasil').css('border-left', '6px solid #e74c3c').html('<b>' + esc(r.msg) + '</b>'); }
    }, 'json');
  }
  $('#btnManual').on('click', function () { var v = $('#manual').val().trim(); if (v) { kirim(v); $('#manual').val(''); } });
  $('#manual').on('keydown', function (e) { if (e.key === 'Enter') $('#btnManual').click(); });
  if (window.Html5Qrcode) {
    var sc = new Html5Qrcode('reader');
    sc.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, function (txt) {
      var now = Date.now(); if (txt === last && now - lastAt < 4000) return; last = txt; lastAt = now; kirim(txt);
    }).catch(function () { $('#reader').html('<div class="alert alert-warning">Kamera tidak tersedia. Pakai input NIS/NIP di bawah, atau buka lewat http://localhost.</div>'); });
  }
});
</script>