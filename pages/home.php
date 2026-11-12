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
    background: linear-gradient(120deg, #0d6b33, #16a34a 70%, #6fcf97);
    color: #fff;
    border-radius: 12px;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 16px;
    box-shadow: 0 4px 14px rgba(13,107,51,.15);
  }

  .sc-hero img {
    height: 84px;
    background: #fff;
    border-radius: 50%;
    padding: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
  }

  .sc-hero h1 {
    margin: 0 0 6px;
    font-size: 24px;
    font-weight: 800;
  }

  .sc-hero p {
    margin: 0;
    opacity: .95;
    max-width: 720px;
    font-size: 13.5px;
    line-height: 1.5;
  }

  .sc-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
  }

  .sc-stat {
    background: #fff;
    border: 1px solid #d6eadc;
    border-radius: 10px;
    padding: 14px 18px;
    border-left: 5px solid #16a34a;
    transition: transform .15s ease;
  }

  .sc-stat:hover {
    transform: translateY(-2px);
  }

  .sc-stat b {
    display: block;
    font-size: 26px;
    color: #0d6b33;
  }

  .sc-stat span {
    font-size: 13px;
    color: #4b6354;
    font-weight: 600;
  }

  .sc-quick {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
  }

  .sc-quick a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    background: #fff;
    border: 1.5px solid #d1e7dd;
    color: #0d6b33;
    font-weight: 700;
    font-size: 13px;
    border-radius: 8px;
    text-decoration: none;
    transition: all .15s ease;
  }

  .sc-quick a:hover {
    background: #16a34a;
    color: #fff;
    border-color: #16a34a;
  }

  .sc-card {
    background: #fff;
    border: 1px solid #d6eadc;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 16px;
  }

  .sc-card h3 {
    margin: 0 0 14px;
    font-size: 16px;
    font-weight: 700;
    color: #0d6b33;
    border-left: 4px solid #16a34a;
    padding-left: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .sc-card h3 a {
    font-size: 12px;
    font-weight: 600;
    color: #16a34a;
  }

  .news-item {
    border-bottom: 1px dashed #e2ede5;
    padding: 10px 0;
  }
  .news-item:last-child { border-bottom: 0; padding-bottom: 0; }
  .news-item:first-child { padding-top: 0; }

  @media (max-width: 900px) {
    .sc-stats {
      grid-template-columns: 1fr 1fr;
    }
    .sc-hero {
      flex-direction: column;
      text-align: center;
    }
  }
</style>
<div class="right_col" role="main">
  <!-- Hero Banner Profil Madrasah -->
  <div class="sc-hero">
    <img src="assets/img/logo.png" alt="Logo">
    <div>
      <h1>MA Ghoyatul Jihad Karawang</h1>
      <p>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif (SIMPLE). Mengintegrasikan tata kelola akademik, presensi QR Code real-time, otomasi e-rapor, dan pemantauan kedisiplinan kesiswaan.</p>
    </div>
  </div>

  <!-- Aksi Cepat / Shortcut Pintasan -->
  <div class="sc-quick">
    <span style="align-self:center;font-size:12px;font-weight:700;color:#5a7364;margin-right:4px"><i class="fa fa-bolt"></i> PINTASAN:</span>
    <?php if (canAccess('Absen-Scan')): ?><a href="?page=Absen-Scan"><i class="fa fa-qrcode"></i> Scan Presensi</a><?php endif; ?>
    <?php if (canAccess('Input-Nilai')): ?><a href="?page=Input-Nilai"><i class="fa fa-pencil-square-o"></i> Input Nilai</a><?php endif; ?>
    <?php if (canAccess('Data-Siswa')): ?><a href="?page=Data-Siswa"><i class="fa fa-graduation-cap"></i> Data Siswa</a><?php endif; ?>
    <?php if (canAccess('Pelanggaran')): ?><a href="?page=Pelanggaran"><i class="fa fa-shield"></i> Poin Pelanggaran</a><?php endif; ?>
    <?php if (canAccess('Perizinan')): ?><a href="?page=Perizinan"><i class="fa fa-envelope-open-o"></i> Perizinan</a><?php endif; ?>
    <?php if (canAccess('Laporan')): ?><a href="?page=Laporan"><i class="fa fa-bar-chart"></i> Laporan</a><?php endif; ?>
  </div>

  <!-- Statistik Utama -->
  <div class="sc-stats">
    <div class="sc-stat"><b><?= $jmlSiswa ?></b><span>Siswa Aktif</span></div>
    <div class="sc-stat"><b><?= $jmlGuru ?></b><span>Guru &amp; Staf</span></div>
    <div class="sc-stat"><b><?= $jmlKelas ?></b><span>Rombongan Belajar</span></div>
    <div class="sc-stat"><b><?= $hadir ?></b><span>Siswa Hadir Hari Ini</span></div>
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
          <table class="table table-bordered table-striped" style="margin:0;font-size:12.5px">
            <thead>
              <tr style="background:#f7fbf8">
                <th>Kelas</th>
                <th class="text-center">Siswa</th>
                <th class="text-center" style="color:#16a34a">Hadir</th>
                <th class="text-center" style="color:#eab308">Telat</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center" style="color:#dc2626">Alpa</th>
                <th class="text-center">Belum</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($perKelas as $k):
                $belum = max(0, $k['total'] - $k['hadir'] - $k['telat'] - $k['izin'] - $k['sakit'] - $k['alpa']); ?>
                <tr>
                  <td><b><?= e($k['nama']) ?></b></td>
                  <td class="text-center"><?= $k['total'] ?></td>
                  <td class="text-center"><b><?= $k['hadir'] ?></b></td>
                  <td class="text-center"><?= $k['telat'] ?></td>
                  <td class="text-center"><?= $k['izin'] ?></td>
                  <td class="text-center"><?= $k['sakit'] ?></td>
                  <td class="text-center"><?= $k['alpa'] ?></td>
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
          <p class="text-muted text-center" style="padding:15px 0;margin:0;font-size:13px">Belum ada pengumuman aktif saat ini.</p>
        <?php else: ?>
          <?php foreach ($pengumuman as $p): ?>
            <div class="news-item">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                <?php if ($p['penting']): ?><span class="label label-danger" style="font-size:10px">PENTING</span><?php endif; ?>
                <b style="font-size:13px;color:#1e3a29"><?= e($p['judul']) ?></b>
              </div>
              <p style="margin:0 0 4px;font-size:12.5px;color:#4b6354;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
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
          <p class="text-muted text-center" style="padding:15px 0;margin:0;font-size:13px"><i class="fa fa-coffee"></i> Hari Minggu: Kegiatan Belajar Mengajar Libur.</p>
        <?php elseif (!$jadwalHariIni): ?>
          <p class="text-muted text-center" style="padding:15px 0;margin:0;font-size:13px">Belum ada jadwal KBM terdaftar untuk hari <?= $hariNama ?>.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-condensed" style="margin:0;font-size:12px">
              <thead>
                <tr style="color:#4b6354">
                  <th>Waktu</th>
                  <th>Kelas</th>
                  <th>Mata Pelajaran</th>
                  <th>Guru</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($jadwalHariIni as $j): ?>
                  <tr>
                    <td><span class="label label-default" style="background:#eef7f0;color:#0d6b33"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></span></td>
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
<?php include "footer.php"; ?>
