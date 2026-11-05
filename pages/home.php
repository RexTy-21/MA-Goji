<?php
// Profil Madrasah (halaman utama) + ringkasan absensi hari ini
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
include "header.php";
?>
<style>
  .sc-hero {
    background: linear-gradient(120deg, #0d6b33, #16a34a 70%, #6fcf97);
    color: #fff;
    border-radius: 12px;
    padding: 28px 30px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 16px;
  }

  .sc-hero img {
    height: 92px;
    background: #fff;
    border-radius: 50%;
    padding: 6px;
  }

  .sc-hero h1 {
    margin: 0 0 6px;
    font-size: 26px;
    font-weight: 800;
  }

  .sc-hero p {
    margin: 0;
    opacity: .95;
    max-width: 700px;
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
  }

  .sc-stat b {
    display: block;
    font-size: 28px;
    color: #0d6b33;
  }

  .sc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .sc-card {
    background: #fff;
    border: 1px solid #d6eadc;
    border-radius: 10px;
    padding: 18px 22px;
    margin-bottom: 16px;
  }

  .sc-card h3 {
    margin: 0 0 10px;
    font-size: 17px;
    font-weight: 700;
    color: #0d6b33;
    border-left: 4px solid #16a34a;
    padding-left: 10px;
  }

  .sc-card li {
    margin-bottom: 6px;
  }

  @media (max-width: 900px) {

    .sc-grid,
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
  <div class="sc-hero">
    <img src="assets/img/logo.png" alt="Logo">
    <div>
      <h1>MA Ghoyatul Jihad Karawang</h1>
      <p>MA Ghoyatul Jihad hadir sebagai lembaga pendidikan yang memadukan pendidikan akademik, pembentukan karakter,
        dan nilai-nilai keislaman. Kami berupaya menciptakan lingkungan belajar yang nyaman, disiplin, dan mendorong
        peserta didik untuk berkembang sesuai potensi masing-masing.</p>
    </div>
  </div>
  <div class="sc-stats">
    <div class="sc-stat"><b><?= $jmlSiswa ?></b>Siswa aktif</div>
    <div class="sc-stat"><b><?= $jmlGuru ?></b>Guru &amp; tenaga pendidik</div>
    <div class="sc-stat"><b><?= $jmlKelas ?></b>Jumlah Kelas</div>
    <div class="sc-stat"><b><?= $hadir ?></b>Siswa hadir hari ini</div>
  </div>
  <div class="sc-card">
    <h3>Absensi Siswa Hari Ini per Kelas</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-striped" style="margin:0">
        <thead>
          <tr>
            <th>Kelas</th>
            <th class="text-center">Siswa</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Terlambat</th>
            <th class="text-center">Izin</th>
            <th class="text-center">Sakit</th>
            <th class="text-center">Alpa</th>
            <th class="text-center">Belum Absen</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($perKelas as $k):
            $belum = max(0, $k['total'] - $k['hadir'] - $k['telat'] - $k['izin'] - $k['sakit'] - $k['alpa']); ?>
            <tr>
              <td><b><?= e($k['nama']) ?></b></td>
              <td class="text-center"><?= $k['total'] ?></td>
              <td class="text-center"><?= $k['hadir'] ?></td>
              <td class="text-center"><?= $k['telat'] ?></td>
              <td class="text-center"><?= $k['izin'] ?></td>
              <td class="text-center"><?= $k['sakit'] ?></td>
              <td class="text-center"><?= $k['alpa'] ?></td>
              <td class="text-center"><?= $belum ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include "footer.php"; ?>