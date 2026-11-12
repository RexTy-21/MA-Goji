<?php
// Cetak rapor (halaman mandiri). ?siswa=ID  atau  ?kelas=ID (semua siswa aktif kelas itu)
$db = $koneksidb;
$ta = taAktif($db);
$sid = (int) ($_GET['siswa'] ?? 0);
$kid = (int) ($_GET['kelas'] ?? 0);
if (!$ta || (!$sid && !$kid)) {
  echo 'Tahun ajaran aktif / siswa belum dipilih.';
  exit;
}
$daftar = $sid ? qall($db, "SELECT s.*, k.nama AS kelas, k.wali_guru_id FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.id = ?", 'i', [$sid])
  : qall($db, "SELECT s.*, k.nama AS kelas, k.wali_guru_id FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.kelas_id = ? AND s.status = 'Active' ORDER BY s.nama", 'i', [$kid]);
$kepala = qone($db, "SELECT user_fullname FROM master_user WHERE user_role = 'KEPALA MADRASAH' AND user_status = 1 ORDER BY user_id LIMIT 1");
$tglCetak = date('d') . ' ' . bulanList()[(int) date('n')] . ' ' . date('Y');
?><!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Rapor - MA Ghoyatul Jihad</title>
  <style>
    @page {
      size: A4;
      margin: 14mm;
    }

    body {
      font-family: "Times New Roman", serif;
      font-size: 12.5px;
      color: #000;
      margin: 0;
      background: #eef5f0;
    }

    .bar {
      padding: 10px 16px;
      background: #fff;
      border-bottom: 1px solid #cfe3d6;
      font-family: Arial, sans-serif;
    }

    .bar button {
      background: #16a34a;
      color: #fff;
      border: 0;
      border-radius: 6px;
      padding: 8px 16px;
      cursor: pointer;
      font-weight: bold;
    }

    .page {
      position: relative;
      background: #fff;
      max-width: 780px;
      margin: 14px auto;
      padding: 26px 34px;
      page-break-after: always;
      box-shadow: 0 1px 6px rgba(0, 0, 0, .12);
      min-height: 980px;
      box-sizing: border-box;
    }

    /* Watermark logo besar transparan di tengah kertas */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 380px;
      max-width: 75%;
      opacity: 0.08;
      pointer-events: none;
      z-index: 0;
      user-select: none;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .page>*:not(.watermark) {
      position: relative;
      z-index: 1;
    }

    .kop {
      display: flex;
      align-items: center;
      gap: 14px;
      border-bottom: 3px double #166534;
      padding-bottom: 8px;
      margin-bottom: 10px;
    }

    .kop img {
      height: 62px;
    }

    .kop h2 {
      margin: 0;
      font-size: 18px;
      color: #166534;
    }

    .kop div {
      text-align: center;
      flex: 1;
    }

    h3.jd {
      text-align: center;
      margin: 8px 0 10px;
      text-decoration: underline;
      font-size: 15px;
    }

    table.id td {
      padding: 1px 6px 1px 0;
      vertical-align: top;
    }

    table.nl {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    table.nl th,
    table.nl td {
      border: 1px solid #000;
      padding: 4px 6px;
    }

    table.nl th {
      background: #dcf3e3;
    }

    table.nl tr.grp td {
      background: #f3faf5;
      font-weight: bold;
    }

    .c {
      text-align: center;
    }

    .low {
      color: #b91c1c;
      font-weight: bold;
    }

    .ttd {
      display: flex;
      justify-content: space-between;
      margin-top: 26px;
      text-align: center;
    }

    .ttd div {
      width: 32%;
    }

    .ttd .sp {
      height: 62px;
    }

    @media print {
      body {
        background: #fff;
      }

      .bar {
        display: none;
      }

      .page {
        box-shadow: none;
        margin: 0;
        max-width: none;
        padding: 0;
        min-height: 260mm;
        page-break-after: always;
      }

      .watermark {
        opacity: 0.08 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
    }
  </style>
</head>

<body>
  <div class="bar"><button onclick="window.print()"><i class="fa fa-print"></i> Cetak / Simpan PDF</button> &nbsp;
    <?= count($daftar) ?> rapor &middot; <?= e($ta['nama'] . ' ' . $ta['semester']) ?></div>
  <?php foreach ($daftar as $s):
    $rows = qall($db, "SELECT m.nama, m.kelompok, m.kkm, n.tugas, n.uts, n.uas FROM nilai n JOIN mapel m ON m.id = n.mapel_id
        WHERE n.siswa_id = ? AND n.tahun_ajaran_id = ? ORDER BY FIELD(m.kelompok,'Agama','Umum','Peminatan','Muatan Lokal'), m.nama", 'ii', [$s['id'], $ta['id']]);
    $cat = qone($db, "SELECT sikap, catatan FROM rapor_catatan WHERE siswa_id = ? AND tahun_ajaran_id = ?", 'ii', [$s['id'], $ta['id']]);
    $abs = ['Hadir' => 0, 'Terlambat' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0];
    $w = "siswa_id = ?";
    $ty = 'i';
    $pp = [$s['id']];
    if ($ta['tgl_mulai'] && $ta['tgl_selesai']) {
      $w .= " AND tanggal BETWEEN ? AND ?";
      $ty .= 'ss';
      $pp[] = $ta['tgl_mulai'];
      $pp[] = $ta['tgl_selesai'];
    }
    foreach (qall($db, "SELECT status, COUNT(*) n FROM absensi_siswa WHERE $w GROUP BY status", $ty, $pp) as $a)
      if (isset($abs[$a['status']]))
        $abs[$a['status']] = (int) $a['n'];
    $wali = $s['wali_guru_id'] ? qone($db, "SELECT nama, nip FROM guru WHERE id = ?", 'i', [$s['wali_guru_id']]) : null;
    $sum = 0;
    $cnt = 0;
    $grp = '';
    ?>
    <div class="page">
      <!-- Watermark Logo MA di Tengah Halaman -->
      <img class="watermark" src="assets/img/logo.png" alt="Watermark MA Ghoyatul Jihad">

      <div class="kop"><img src="assets/img/logo.png" alt="">
        <div>
          <h2>MA GHOYATUL JIHAD KARAWANG</h2>
          <div style="font-size:11px">Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif</div>
        </div>
      </div>
      <h3 class="jd">LAPORAN HASIL BELAJAR (RAPOR)</h3>
      <table class="id">
        <tr>
          <td>Nama Siswa</td>
          <td>: <b><?= e($s['nama']) ?></b></td>
          <td style="padding-left:30px">Kelas</td>
          <td>: <?= e($s['kelas']) ?></td>
        </tr>
        <tr>
          <td>NIS</td>
          <td>: <?= e($s['nis']) ?> </td>
          <td style="padding-left:30px">Semester</td>
          <td>: <?= e($ta['semester']) ?></td>
        </tr>
        <tr>
          <td>NISN</td>
          <td> <?= e($s['nisn'] ?: '-') ?></td>
          <td style="padding-left:30px">Tahun Ajaran</td>
          <td>: <?= e($ta['nama']) ?></td>
        </tr>

      </table>
      <table class="nl">
        <thead>
          <tr>
            <th style="width:30px">No</th>
            <th>Mata Pelajaran</th>
            <th class="c">KKM</th>
            <th class="c">Tugas</th>
            <th class="c">UTS</th>
            <th class="c">UAS</th>
            <th class="c">Nilai Akhir</th>
            <th class="c">Predikat</th>
            <th class="c">Ket.</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows)
            echo '<tr><td colspan="9" class="c">Belum ada nilai.</td></tr>';
          $no = 0;
          foreach ($rows as $r):
            $ak = nilaiAkhir($r['tugas'], $r['uts'], $r['uas']);
            if ($ak !== null) {
              $sum += $ak;
              $cnt++;
            }
            if ($r['kelompok'] !== $grp) {
              $grp = $r['kelompok'];
              echo '<tr class="grp"><td colspan="9">Kelompok ' . e($grp) . '</td></tr>';
            }
            $no++; ?>
            <tr>
              <td class="c"><?= $no ?></td>
              <td><?= e($r['nama']) ?></td>
              <td class="c"><?= (int) $r['kkm'] ?></td>
              <td class="c"><?= $r['tugas'] !== null ? (float) $r['tugas'] : '-' ?></td>
              <td class="c"><?= $r['uts'] !== null ? (float) $r['uts'] : '-' ?></td>
              <td class="c"><?= $r['uas'] !== null ? (float) $r['uas'] : '-' ?></td>
              <td class="c <?= $ak !== null && $ak < $r['kkm'] ? 'low' : '' ?>"><b><?= $ak !== null ? $ak : '-' ?></b></td>
              <td class="c"><?= predikat($ak) ?></td>
              <td class="c"><?= $ak === null ? '-' : ($ak >= $r['kkm'] ? 'Tuntas' : 'Belum') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if ($cnt): ?>
            <tr>
              <td colspan="6" style="text-align:right"><b>Rata-rata</b></td>
              <td class="c"><b><?= number_format($sum / $cnt, 1) ?></b></td>
              <td class="c"><b><?= predikat($sum / $cnt) ?></b></td>
              <td></td>
            </tr><?php endif; ?>
        </tbody>
      </table>
      <table style="width:100%;margin-top:12px">
        <tr>
          <td style="width:50%;vertical-align:top">
            <b>Kehadiran</b>
            <table class="id">
              <?php foreach (['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'] as $k)
                echo '<tr><td>' . $k . '</td><td>: ' . $abs[$k] . ' hari</td></tr>'; ?>
            </table>
          </td>
          <td style="vertical-align:top"><b>Sikap</b>: <?= e($cat['sikap'] ?? '-') ?><br><b>Catatan Wali
              Kelas</b>:<br><?= nl2br(e($cat['catatan'] ?? '-')) ?></td>
        </tr>
      </table>
      <div class="ttd">
        <div>Mengetahui,<br>Orang Tua / Wali<div class="sp"></div>( ........................ )</div>
        <div>Wali Kelas<div class="sp"></div>
          <u><?= e($wali['nama'] ?? '........................') ?></u><br><?= !empty($wali['nip']) ? 'NIP ' . e($wali['nip']) : '' ?>
        </div>
        <div>Karawang, <?= $tglCetak ?><br>Kepala Madrasah<div class="sp"></div>
          <u><?= e($kepala['user_fullname'] ?? '........................') ?></u></div>
      </div>
    </div>
  <?php endforeach; ?>
</body>

</html>