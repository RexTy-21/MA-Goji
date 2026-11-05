<?php
$isHelp = ($_GET['page'] ?? '') === 'Help';
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title"><div class="title_left"><h3><?= $isHelp ? 'Help' : 'User Guide' ?></h3></div></div><div class="clearfix"></div>
  <div class="x_panel"><div class="x_content">
  <?php if ($isHelp): ?>
    <p><b>Lupa password?</b> Hubungi admin; admin bisa mereset password lewat menu Kelola User.</p>
    <p><b>Kamera scan tidak jalan?</b> Buka aplikasi lewat <code>http://localhost/...</code> (bukan alamat .test), izinkan kamera, atau ketik NIS/NIP di kolom cadangan.</p>
    <p><b>Data kosong?</b> Pastikan database <code>simple_sekolah</code> sudah diimpor (schema.sql lalu seed.sql).</p>
  <?php else: ?>
    <ol>
      <li><b>Data Siswa</b>: filter per kelas, status, dan jenis kelamin. Admin/TU dapat menambah dan mengubah data.</li>
      <li><b>Data Guru</b>: kelola data guru dan tenaga pendidik (admin, kepala, wakil, dan TU).</li>
      <li><b>Data Kelas</b>: daftar rombongan belajar, wali kelas, dan jumlah siswa.</li>
      <li><b>Kartu QR</b>: tombol ikon kartu di tabel menampilkan QR; "Cetak Kartu" mencetak satu kelas sekaligus.</li>
      <li><b>Scan Absen</b>: scan QR siswa atau guru. Scan pertama = masuk (lewat 07:00 = terlambat), scan kedua = pulang.</li>
      <li><b>Absensi Siswa / Guru</b>: rekap per bulan. Izin, sakit, dan alpa diinput manual dari halaman Absensi Siswa.</li>
    </ol>
  <?php endif; ?>
  </div></div>
</div>
<?php include "footer.php"; ?>
