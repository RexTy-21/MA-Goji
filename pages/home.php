<?php
// Profil Madrasah (halaman utama) + ringkasan absensi, pengumuman & jadwal KBM hari ini
$db = $koneksidb;
$jmlSiswa = (int) (qone($db, "SELECT COUNT(*) c FROM siswa WHERE status = 'Active'")['c'] ?? 0);
$jmlGuru = (int) (qone($db, "SELECT COUNT(*) c FROM guru WHERE status = 'Active'")['c'] ?? 0);
$jmlKelas = (int) (qone($db, "SELECT COUNT(*) c FROM kelas")['c'] ?? 0);
$hadir = (int) (qone($db, "SELECT COUNT(*) c FROM absensi_siswa WHERE tanggal = CURDATE() AND status IN ('Hadir','Terlambat')")['c'] ?? 0);
$perKelas = qall($db, "SELECT k.nama,
        COUNT(s.id) AS total,
        COALESCE(SUM(a.status = 'Hadir'), 0) AS hadir, COALESCE(SUM(a.status = 'Terlambat'), 0) AS telat,
        COALESCE(SUM(a.status = 'Izin'), 0) AS izin, COALESCE(SUM(a.status = 'Sakit'), 0) AS sakit, COALESCE(SUM(a.status = 'Alpa'), 0) AS alpa
    FROM kelas k
    LEFT JOIN siswa s ON s.kelas_id = k.id AND s.status = 'Active'
    LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND a.tanggal = CURDATE()
    GROUP BY k.id, k.nama, k.tingkat, k.jurusan
    ORDER BY FIELD(k.tingkat, 'X', 'XI', 'XII'), k.jurusan");

$ta = taAktif($db);
$hariIdx = (int)date('N'); // 1 = Senin, ..., 7 = Minggu
$hariMap = hariList();
$hariNama = $hariMap[$hariIdx] ?? 'Minggu';

// Ringkasan data untuk diagram absensi hari ini
$chartLabels = [];
$chartHadir = [];
$chartTelat = [];
$chartIzinSakit = [];
$chartAlpa = [];
$chartBelum = [];
$totHadir = $totTelat = $totIzin = $totSakit = $totAlpa = $totBelum = 0;
foreach ($perKelas as $k) {
    $belum = max(0, (int)$k['total'] - (int)$k['hadir'] - (int)$k['telat'] - (int)$k['izin'] - (int)$k['sakit'] - (int)$k['alpa']);
    $chartLabels[] = $k['nama'];
    $chartHadir[] = (int)$k['hadir'];
    $chartTelat[] = (int)$k['telat'];
    $chartIzinSakit[] = (int)$k['izin'] + (int)$k['sakit'];
    $chartAlpa[] = (int)$k['alpa'];
    $chartBelum[] = $belum;

    $totHadir += (int)$k['hadir'];
    $totTelat += (int)$k['telat'];
    $totIzin += (int)$k['izin'];
    $totSakit += (int)$k['sakit'];
    $totAlpa += (int)$k['alpa'];
    $totBelum += $belum;
}

// Pengumuman terbaru
$pengumuman = [];
$chkPengumuman = @mysqli_query($db, "SHOW TABLES LIKE 'pengumuman'");
if ($chkPengumuman && mysqli_num_rows($chkPengumuman) > 0) {
    $pengumuman = qall($db, "SELECT * FROM pengumuman WHERE berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE() ORDER BY penting DESC, tanggal DESC, id DESC LIMIT 3");
}

// Jadwal KBM hari ini
$jadwalHariIni = [];
$chkJadwal = @mysqli_query($db, "SHOW TABLES LIKE 'jadwal'");
if ($chkJadwal && mysqli_num_rows($chkJadwal) > 0 && $ta && $hariIdx <= 6) {
    $jadwalHariIni = qall($db, "SELECT j.*, m.nama AS mapel, k.nama AS kelas, g.nama AS guru 
        FROM jadwal j 
        JOIN mapel m ON m.id = j.mapel_id 
        JOIN kelas k ON k.id = j.kelas_id 
        LEFT JOIN guru g ON g.id = j.guru_id 
        WHERE j.tahun_ajaran_id = ? AND j.hari = ? 
        ORDER BY j.jam_mulai ASC LIMIT 5", 'ii', [$ta['id'], $hariIdx]);
}

include "header.php";
?>
<style>
  .sc-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(125deg, #084a23 0%, #0f7a3d 48%, #16a34a 100%);
    color: #fff;
    border-radius: 16px;
    padding: 26px 30px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 18px;
    box-shadow: 0 12px 28px -10px rgba(13, 107, 51, .35);
  }
  .sc-hero::before {
    content: "";
    position: absolute;
    right: -60px;
    top: -60px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, .16) 0%, transparent 70%);
    pointer-events: none;
  }
  .sc-hero::after {
    content: "";
    position: absolute;
    right: 120px;
    bottom: -90px;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(111, 207, 151, .18) 0%, transparent 70%);
    pointer-events: none;
  }

  .sc-hero-logo {
    position: relative;
    z-index: 1;
    width: 88px;
    height: 88px;
    flex: none;
    background: #fff;
    border-radius: 50%;
    padding: 8px;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .18), 0 0 0 4px rgba(255, 255, 255, .2);
    display: grid;
    place-items: center;
  }
  .sc-hero-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .sc-hero-body {
    position: relative;
    z-index: 1;
    flex: 1;
    min-width: 0;
  }
  .sc-hero-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }
  .sc-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 11px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .16);
    border: 1px solid rgba(255, 255, 255, .22);
    font-size: 11.5px;
    font-weight: 600;
    color: #f0fdf4;
  }

  .sc-hero h1 {
    margin: 0 0 6px;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.01em;
  }

  .sc-hero p {
    margin: 0;
    color: rgba(255, 255, 255, .92);
    max-width: 740px;
    font-size: 13.5px;
    line-height: 1.55;
  }

  /* Quick shortcuts */
  .sc-quick {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 9px;
    margin-bottom: 18px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid #dceee2;
    border-radius: 14px;
    box-shadow: 0 2px 10px -4px rgba(13, 107, 51, .06);
  }
  .sc-quick-label {
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: .06em;
    color: #4f6b5a;
    margin-right: 4px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .sc-quick a {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    background: #f4fbf6;
    border: 1px solid #cde6d5;
    color: #0d6b33 !important;
    font-weight: 700;
    font-size: 12.5px;
    border-radius: 10px;
    text-decoration: none;
    transition: all .16s ease;
  }
  .sc-quick a:hover {
    background: #16a34a;
    color: #fff !important;
    border-color: #16a34a;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px -6px rgba(22, 163, 74, .5);
  }

  /* KPI Stats */
  .sc-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
  }
  .sc-stat {
    position: relative;
    background: #fff;
    border: 1px solid #dceee2;
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    box-shadow: 0 4px 14px -6px rgba(13, 107, 51, .08);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    overflow: hidden;
  }
  .sc-stat::before {
    content: "";
    position: absolute;
    left: 0;
    top: 12px;
    bottom: 12px;
    width: 4px;
    border-radius: 0 4px 4px 0;
    background: #16a34a;
  }
  .sc-stat.is-teal::before { background: #0d9488; }
  .sc-stat.is-blue::before { background: #0284c7; }
  .sc-stat.is-emerald::before { background: #059669; }

  .sc-stat:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -10px rgba(13, 107, 51, .18);
    border-color: #b7dec4;
  }
  .sc-stat-info b {
    display: block;
    font-size: 28px;
    font-weight: 800;
    line-height: 1.1;
    color: #0d6b33;
    font-variant-numeric: tabular-nums;
    margin-bottom: 4px;
  }
  .sc-stat.is-teal .sc-stat-info b { color: #0f766e; }
  .sc-stat.is-blue .sc-stat-info b { color: #0369a1; }
  .sc-stat.is-emerald .sc-stat-info b { color: #047857; }

  .sc-stat-info span {
    font-size: 12.5px;
    color: #546e5e;
    font-weight: 600;
  }
  .sc-stat-ico {
    width: 46px;
    height: 46px;
    flex: none;
    border-radius: 12px;
    background: #eaf7ee;
    color: #16a34a;
    display: grid;
    place-items: center;
    font-size: 20px;
  }
  .sc-stat.is-teal .sc-stat-ico { background: #e6f7f5; color: #0d9488; }
  .sc-stat.is-blue .sc-stat-ico { background: #e8f4fc; color: #0284c7; }
  .sc-stat.is-emerald .sc-stat-ico { background: #e6f6ef; color: #059669; }

  /* Cards */
  .sc-card {
    background: #fff;
    border: 1px solid #dceee2;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px -6px rgba(13, 107, 51, .07);
  }
  .sc-card h3 {
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eaf3ed;
    font-size: 15.5px;
    font-weight: 700;
    color: #0d6b33;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }
  .sc-card h3 > span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .sc-card h3 > span i {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #eaf7ee;
    color: #16a34a;
    display: inline-grid;
    place-items: center;
    font-size: 13px;
  }
  .sc-card h3 a {
    font-size: 12px;
    font-weight: 700;
    color: #0f7a3d;
    padding: 5px 10px;
    border-radius: 8px;
    background: #f2faf5;
    border: 1px solid #d5ebdc;
    text-decoration: none;
    transition: background .15s ease, color .15s ease;
    white-space: nowrap;
  }
  .sc-card h3 a:hover {
    background: #16a34a;
    color: #fff;
    border-color: #16a34a;
  }

  /* Table polish */
  .sc-table {
    margin: 0 !important;
    font-size: 12.5px;
    border-collapse: separate;
    border-spacing: 0;
  }
  .sc-table thead th {
    background: #eef8f1 !important;
    color: #16452b !important;
    font-weight: 700;
    border-bottom: 1px solid #d2e8d9 !important;
    padding: 10px 10px !important;
    white-space: nowrap;
  }
  .sc-table tbody td {
    padding: 9px 10px !important;
    vertical-align: middle !important;
    border-color: #edf5ef !important;
  }
  .sc-table tbody tr:hover td {
    background: #f5fbf7 !important;
  }
  .sc-num-pill {
    display: inline-block;
    min-width: 26px;
    padding: 2px 7px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 11.5px;
  }
  .sc-num-ok { background: #dcfce7; color: #15803d; }
  .sc-num-warn { background: #fef9c3; color: #a16207; }
  .sc-num-bad { background: #fee2e2; color: #b91c1c; }

  /* News / Pengumuman */
  .news-item {
    border: 1px solid #ebf4ee;
    background: #fafdfb;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
    transition: border-color .15s ease, background .15s ease;
  }
  .news-item:last-child { margin-bottom: 0; }
  .news-item:hover {
    background: #f3faf5;
    border-color: #cce5d5;
  }
  .sc-empty {
    padding: 22px 12px;
    margin: 0;
    font-size: 13px;
    color: #6b8274;
    text-align: center;
    background: #f8fcf9;
    border: 1px dashed #d6eadc;
    border-radius: 10px;
  }

  .sc-chart-box {
    position: relative;
    height: 250px;
    width: 100%;
  }
  .sc-chart-donut-wrap {
    position: relative;
    height: 200px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .sc-chart-center {
    position: absolute;
    text-align: center;
    pointer-events: none;
  }
  .sc-chart-center b {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: #0d6b33;
    line-height: 1.1;
  }
  .sc-chart-center small {
    font-size: 11px;
    color: #5b7264;
    font-weight: 600;
  }
  .sc-donut-pills {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin-top: 12px;
  }
  .sc-donut-pills span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 600;
    color: #365041;
    background: #f4fbf6;
    border: 1px solid #dceee2;
    border-radius: 999px;
    padding: 3px 9px;
  }
  .sc-donut-pills i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }

  @media (max-width: 992px) {
    .sc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 720px) {
    .sc-hero {
      flex-direction: column;
      text-align: center;
      padding: 22px 18px;
    }
    .sc-hero-meta { justify-content: center; }
    .sc-hero h1 { font-size: 20px; }
    .sc-stats { grid-template-columns: 1fr; }
    .sc-card { padding: 16px; }
  }
</style>
<div class="right_col" role="main">
  <!-- Hero Banner Profil Madrasah -->
  <div class="sc-hero">
    <div class="sc-hero-logo">
      <img src="assets/img/logo.png" alt="Logo">
    </div>
    <div class="sc-hero-body">
      <div class="sc-hero-meta">
        <span class="sc-hero-pill"><i class="fa fa-calendar"></i> <?= $hariNama ?>, <?= date('d M Y') ?></span>
        <?php if ($ta): ?>
          <span class="sc-hero-pill"><i class="fa fa-graduation-cap"></i> TA <?= e($ta['nama'] ?? ($ta['tahun'] ?? 'Aktif')) ?></span>
        <?php endif; ?>
      </div>
      <h1>MA Ghoyatul Jihad Karawang</h1>
      <p>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif (SIMPLE). Mengintegrasikan tata kelola akademik, presensi QR Code real-time, otomasi e-rapor, dan pemantauan kedisiplinan kesiswaan.</p>
    </div>
  </div>

  <!-- Aksi Cepat / Shortcut Pintasan -->
  <div class="sc-quick">
    <span class="sc-quick-label"><i class="fa fa-bolt"></i> PINTASAN</span>
    <?php if (canAccess('Absen-Scan')): ?><a href="?page=Absen-Scan"><i class="fa fa-qrcode"></i> Scan Presensi</a><?php endif; ?>
    <?php if (canAccess('Input-Nilai')): ?><a href="?page=Input-Nilai"><i class="fa fa-pencil-square-o"></i> Input Nilai</a><?php endif; ?>
    <?php if (canAccess('Data-Siswa')): ?><a href="?page=Data-Siswa"><i class="fa fa-graduation-cap"></i> Data Siswa</a><?php endif; ?>
    <?php if (canAccess('Pelanggaran')): ?><a href="?page=Pelanggaran"><i class="fa fa-shield"></i> Poin Pelanggaran</a><?php endif; ?>
    <?php if (canAccess('Perizinan')): ?><a href="?page=Perizinan"><i class="fa fa-envelope-open-o"></i> Perizinan</a><?php endif; ?>
    <?php if (canAccess('Laporan')): ?><a href="?page=Laporan"><i class="fa fa-bar-chart"></i> Laporan</a><?php endif; ?>
  </div>

  <!-- Statistik Utama -->
  <div class="sc-stats">
    <div class="sc-stat">
      <div class="sc-stat-info"><b><?= $jmlSiswa ?></b><span>Siswa Aktif</span></div>
      <div class="sc-stat-ico"><i class="fa fa-users"></i></div>
    </div>
    <div class="sc-stat is-teal">
      <div class="sc-stat-info"><b><?= $jmlGuru ?></b><span>Guru &amp; Staf</span></div>
      <div class="sc-stat-ico"><i class="fa fa-id-badge"></i></div>
    </div>
    <div class="sc-stat is-blue">
      <div class="sc-stat-info"><b><?= $jmlKelas ?></b><span>Rombongan Belajar</span></div>
      <div class="sc-stat-ico"><i class="fa fa-building-o"></i></div>
    </div>
    <div class="sc-stat is-emerald">
      <div class="sc-stat-info"><b><?= $hadir ?></b><span>Siswa Hadir Hari Ini</span></div>
      <div class="sc-stat-ico"><i class="fa fa-check-circle-o"></i></div>
    </div>
  </div>

  <!-- Diagram Ringkasan Kehadiran -->
  <div class="row">
    <div class="col-md-8">
      <div class="sc-card">
        <h3>
          <span><i class="fa fa-bar-chart"></i> Diagram Kehadiran per Kelas (Hari Ini)</span>
          <a href="?page=Absen-Siswa"><i class="fa fa-external-link"></i> Detail</a>
        </h3>
        <?php if (!$perKelas): ?>
          <p class="sc-empty">Belum ada data kelas untuk ditampilkan pada diagram.</p>
        <?php else: ?>
          <div class="sc-chart-box">
            <canvas id="chartKelas"></canvas>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sc-card">
        <h3>
          <span><i class="fa fa-pie-chart"></i> Komposisi Kehadiran</span>
          <a href="?page=Laporan"><i class="fa fa-arrow-right"></i> Laporan</a>
        </h3>
        <?php
          $totSemua = $totHadir + $totTelat + $totIzin + $totSakit + $totAlpa + $totBelum;
          $pctHadir = $totSemua > 0 ? round((($totHadir + $totTelat) / $totSemua) * 100) : 0;
        ?>
        <?php if ($totSemua === 0): ?>
          <p class="sc-empty">Belum ada data siswa aktif hari ini.</p>
        <?php else: ?>
          <div class="sc-chart-donut-wrap">
            <canvas id="chartKomposisi"></canvas>
            <div class="sc-chart-center">
              <b><?= $pctHadir ?>%</b>
              <small>Hadir</small>
            </div>
          </div>
          <div class="sc-donut-pills">
            <span><i style="background:#16a34a"></i> Hadir: <?= $totHadir ?></span>
            <span><i style="background:#eab308"></i> Telat: <?= $totTelat ?></span>
            <span><i style="background:#0284c7"></i> Izin/Sakit: <?= $totIzin + $totSakit ?></span>
            <span><i style="background:#dc2626"></i> Alpa: <?= $totAlpa ?></span>
            <span><i style="background:#cbd5e1"></i> Belum: <?= $totBelum ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Kolom Kiri: Absensi Siswa Hari Ini -->
    <div class="col-md-7">
      <div class="sc-card">
        <h3>
          <span><i class="fa fa-check-square-o"></i> Absensi Siswa Hari Ini per Kelas</span>
          <a href="?page=Absen-Siswa"><i class="fa fa-external-link"></i> Rekap Lengkap</a>
        </h3>
        <div class="table-responsive">
          <table class="table table-bordered table-striped sc-table">
            <thead>
              <tr>
                <th>Kelas</th>
                <th class="text-center">Siswa</th>
                <th class="text-center" style="color:#15803d">Hadir</th>
                <th class="text-center" style="color:#b45309">Telat</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center" style="color:#b91c1c">Alpa</th>
                <th class="text-center">Belum</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($perKelas as $k):
                $belum = max(0, $k['total'] - $k['hadir'] - $k['telat'] - $k['izin'] - $k['sakit'] - $k['alpa']); ?>
                <tr>
                  <td><b><?= e($k['nama']) ?></b></td>
                  <td class="text-center"><?= $k['total'] ?></td>
                  <td class="text-center"><span class="sc-num-pill <?= $k['hadir'] ? 'sc-num-ok' : '' ?>"><?= $k['hadir'] ?></span></td>
                  <td class="text-center"><span class="sc-num-pill <?= $k['telat'] ? 'sc-num-warn' : '' ?>"><?= $k['telat'] ?></span></td>
                  <td class="text-center"><?= $k['izin'] ?></td>
                  <td class="text-center"><?= $k['sakit'] ?></td>
                  <td class="text-center"><span class="sc-num-pill <?= $k['alpa'] ? 'sc-num-bad' : '' ?>"><?= $k['alpa'] ?></span></td>
                  <td class="text-center"><span class="badge" style="background:<?= $belum ? '#e2e8f0;color:#475569' : '#16a34a' ?>"><?= $belum ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Pengumuman & Jadwal Hari Ini -->
    <div class="col-md-5">
      <!-- Card Pengumuman -->
      <div class="sc-card">
        <h3>
          <span><i class="fa fa-bullhorn"></i> Pengumuman &amp; Agenda</span>
          <a href="?page=Pengumuman"><i class="fa fa-arrow-right"></i> Semua</a>
        </h3>
        <?php if (!$pengumuman): ?>
          <p class="sc-empty">Belum ada pengumuman aktif saat ini.</p>
        <?php else: ?>
          <?php foreach ($pengumuman as $p): ?>
            <div class="news-item">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                <?php if ($p['penting']): ?><span class="label label-danger" style="font-size:10px;border-radius:6px">PENTING</span><?php endif; ?>
                <b style="font-size:13px;color:#143822"><?= e($p['judul']) ?></b>
              </div>
              <p style="margin:0 0 6px;font-size:12.5px;color:#4b6354;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                <?= e($p['isi']) ?>
              </p>
              <small class="text-muted" style="font-size:11px"><i class="fa fa-clock-o"></i> <?= date('d M Y', strtotime($p['tanggal'])) ?> &middot; <?= e($p['penulis'] ?: 'Madrasah') ?></small>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Card Jadwal KBM Hari Ini -->
      <div class="sc-card">
        <h3>
          <span><i class="fa fa-calendar-check-o"></i> Jadwal KBM Hari Ini (<?= $hariNama ?>)</span>
          <a href="?page=Jadwal"><i class="fa fa-arrow-right"></i> Selengkapnya</a>
        </h3>
        <?php if ($hariIdx > 6): ?>
          <p class="sc-empty"><i class="fa fa-coffee"></i> Hari Minggu: Kegiatan Belajar Mengajar Libur.</p>
        <?php elseif (!$jadwalHariIni): ?>
          <p class="sc-empty">Belum ada jadwal KBM terdaftar untuk hari <?= $hariNama ?>.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-condensed sc-table" style="font-size:12px">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Kelas</th>
                  <th>Mata Pelajaran</th>
                  <th>Guru</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($jadwalHariIni as $j): ?>
                  <tr>
                    <td style="white-space:nowrap"><span class="label label-default" style="background:#e8f6ed;color:#0d6b33;border-radius:6px;padding:4px 7px"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></span></td>
                    <td><b><?= e($j['kelas']) ?></b></td>
                    <td><?= e($j['mapel']) ?></td>
                    <td><small class="text-muted"><?= e($j['guru'] ?: '-') ?></small></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return;
  Chart.defaults.font.family = '"Plus Jakarta Sans", "DM Sans", sans-serif';
  Chart.defaults.color = '#4b6354';

  var elKelas = document.getElementById('chartKelas');
  if (elKelas) {
    new Chart(elKelas, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
          {
            label: 'Hadir',
            data: <?= json_encode($chartHadir) ?>,
            backgroundColor: '#16a34a',
            borderRadius: 5,
            maxBarThickness: 28
          },
          {
            label: 'Telat',
            data: <?= json_encode($chartTelat) ?>,
            backgroundColor: '#eab308',
            borderRadius: 5,
            maxBarThickness: 28
          },
          {
            label: 'Izin/Sakit',
            data: <?= json_encode($chartIzinSakit) ?>,
            backgroundColor: '#0284c7',
            borderRadius: 5,
            maxBarThickness: 28
          },
          {
            label: 'Alpa',
            data: <?= json_encode($chartAlpa) ?>,
            backgroundColor: '#dc2626',
            borderRadius: 5,
            maxBarThickness: 28
          },
          {
            label: 'Belum',
            data: <?= json_encode($chartBelum) ?>,
            backgroundColor: '#cbd5e1',
            borderRadius: 5,
            maxBarThickness: 28
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 12, usePointStyle: true, pointStyle: 'circle', padding: 14 }
          }
        },
        scales: {
          x: { stacked: true, grid: { display: false } },
          y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf5ef' } }
        }
      }
    });
  }

  var elKomp = document.getElementById('chartKomposisi');
  if (elKomp) {
    new Chart(elKomp, {
      type: 'doughnut',
      data: {
        labels: ['Hadir', 'Telat', 'Izin/Sakit', 'Alpa', 'Belum'],
        datasets: [{
          data: [<?= $totHadir ?>, <?= $totTelat ?>, <?= $totIzin + $totSakit ?>, <?= $totAlpa ?>, <?= $totBelum ?>],
          backgroundColor: ['#16a34a', '#eab308', '#0284c7', '#dc2626', '#cbd5e1'],
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
})();
</script>
<?php include "footer.php"; ?>
