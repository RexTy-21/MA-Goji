<?php
$isHelp = ($_GET['page'] ?? '') === 'Help';
include "header.php";
?>
<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left">
      <h3><i class="fa <?= $isHelp ? 'fa-question-circle' : 'fa-book' ?>"></i> <?= $isHelp ? 'Pusat Bantuan & FAQ' : 'Panduan Pengguna (User Guide)' ?></h3>
    </div>
    <div class="title_right">
      <div class="pull-right">
        <a href="?page=User-Guide" class="btn btn-sm <?= !$isHelp ? 'btn-success' : 'btn-default' ?>"><i class="fa fa-book"></i> User Guide</a>
        <a href="?page=Help" class="btn btn-sm <?= $isHelp ? 'btn-success' : 'btn-default' ?>"><i class="fa fa-question-circle"></i> FAQ &amp; Bantuan</a>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>

  <div class="x_panel">
    <div class="x_content">
      <?php if ($isHelp): ?>
        <h4 style="color:#0d6b33;margin-top:0"><i class="fa fa-life-ring"></i> Pertanyaan yang Sering Diajukan (FAQ)</h4>
        <div class="panel-group" id="accordionHelp" style="margin-top:16px">
          
          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionHelp" href="#faq1"><b>1. Bagaimana jika lupa kata sandi (password)?</b></a></h4></div>
            <div id="faq1" class="panel-collapse collapse in"><div class="panel-body">
              Hubungi <b>Administrator</b> madrasah. Administrator dapat mereset kata sandi akun Anda kembali ke kata sandi standar (<code>demo1234</code>) melalui menu <b>Pengaturan &gt; Kelola User</b>. Setelah berhasil login, Anda dapat mengganti kata sandi di halaman <b>Profile</b>.
            </div></div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionHelp" href="#faq2"><b>2. Mengapa kamera scanner QR presensi tidak mau menyala?</b></a></h4></div>
            <div id="faq2" class="panel-collapse collapse"><div class="panel-body">
              Peramban web modern mewajibkan protokol aman untuk mengaktifkan WebCam/kamera.
              <ul>
                <li>Pastikan alamat URL diakses melalui <code>http://localhost/...</code> atau <code>http://127.0.0.1/...</code> (bukan domain lokal seperti <code>.test</code> tanpa SSL).</li>
                <li>Pastikan izin kamera (*Permission*) pada peramban telah diizinkan (*Allow*).</li>
                <li>Jika kamera bermasalah atau perangkat tidak berkamera, gunakan <b>Kolom Input Manual NIS/NIP</b> yang ada tepat di bawah scanner.</li>
              </ul>
            </div></div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionHelp" href="#faq3"><b>3. Bagaimana perhitungan keterlambatan dan pemotongan poin disiplin?</b></a></h4></div>
            <div id="faq3" class="panel-collapse collapse"><div class="panel-body">
              Batas waktu kedatangan standar adalah pukul <b>07:00:00 WIB</b>. Siswa yang melakukan scan setelah jam tersebut akan tercatat berstatus <b>Terlambat</b>. Jika keterlambatan melebihi batas toleransi <b>15 menit (lewat pukul 07:15)</b>, sistem secara otomatis akan memotong <b>5 poin kedisiplinan</b> siswa pada catatan kesiswaan.
            </div></div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionHelp" href="#faq4"><b>4. Mengapa muncul "Akses Ditolak (403)" pada menu tertentu?</b></a></h4></div>
            <div id="faq4" class="panel-collapse collapse"><div class="panel-body">
              Setiap menu dibatasi berdasarkan peran (*Role*). Administrator dapat memberikan izin akses khusus pada menu tersebut melalui fitur <b>Pengaturan &gt; Master Menu</b> baik untuk seluruh peran maupun khusus akun Anda.
            </div></div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordionHelp" href="#faq5"><b>5. Bagaimana cara mencetak Rapor atau Kartu Pelajar?</b></a></h4></div>
            <div id="faq5" class="panel-collapse collapse"><div class="panel-body">
              Buka menu <b>Penilaian &gt; Rapor</b> lalu klik tombol <b>Cetak Rapor</b> pada siswa yang bersangkutan. Untuk kartu pelajar, buka menu <b>Akademik &gt; Data Siswa</b> dan gunakan tombol <b>Cetak Kartu</b> untuk mencetak ID Card berserta QR Code dalam format layout cetak.
            </div></div>
          </div>

        </div>

      <?php else: ?>

        <h4 style="color:#0d6b33;margin-top:0"><i class="fa fa-graduation-cap"></i> Panduan Alur Pengoperasian Sistem (SIMPLE)</h4>
        <p class="text-muted">Panduan ringkas alur kerja bagi Administrator, Tata Usaha, dan Dewan Guru:</p>

        <div class="row" style="margin-top:16px">
          <div class="col-md-6">
            <div class="panel panel-success">
              <div class="panel-heading"><b>1. Pengelolaan Data Akademik</b></div>
              <div class="panel-body" style="font-size:13px">
                <ol>
                  <li><b>Tahun Ajaran:</b> Pastikan Tahun Ajaran dan Semester aktif telah diatur pada menu <i>Tahun Ajaran</i>.</li>
                  <li><b>Data Guru &amp; Kelas:</b> Masukkan data dewan guru, rombel/kelas, dan tentukan Wali Kelas.</li>
                  <li><b>Mata Pelajaran &amp; KKM:</b> Atur kode mata pelajaran, kelompok (Agama, Umum, Peminatan), dan batas KKM.</li>
                  <li><b>Jadwal Pelajaran:</b> Susun alokasi waktu mengajar guru per kelas dan hari agar tidak terjadi bentrok jadwal.</li>
                  <li><b>Data Siswa &amp; Cetak Kartu:</b> Daftarkan siswa ke kelasnya dan cetak kartu pelajar QR code.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="panel panel-success">
              <div class="panel-heading"><b>2. Presensi Digital (Scan QR)</b></div>
              <div class="panel-body" style="font-size:13px">
                <ol>
                  <li><b>Arahkan Kartu:</b> Siswa atau guru mengarahkan QR Code kartu ke kamera di menu <i>Scan Absen</i>.</li>
                  <li><b>Scan Masuk:</b> Scan pertama pada hari berjalan dicatat otomatis sebagai jam masuk.</li>
                  <li><b>Scan Pulang:</b> Scan kedua pada hari yang sama dicatat otomatis sebagai jam pulang.</li>
                  <li><b>Izin / Sakit:</b> Jika siswa tidak masuk dengan keterangan, dapat diajukan di menu <i>Perizinan</i> atau diinput langsung di <i>Absensi Siswa</i>.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="panel panel-info">
              <div class="panel-heading"><b>3. Penilaian &amp; Cetak E-Rapor</b></div>
              <div class="panel-body" style="font-size:13px">
                <ol>
                  <li><b>Input Nilai Guru:</b> Guru mapel mengisi nilai Tugas (30%), UTS (30%), dan UAS (40%) di menu <i>Input Nilai</i>.</li>
                  <li><b>Kalkulasi Otomatis:</b> Nilai akhir dan predikat (A/B/C/D) terhitung otomatis berdasarkan KKM.</li>
                  <li><b>Catatan Wali Kelas:</b> Wali kelas mengisi evaluasi sikap dan motivasi pada menu <i>Rapor</i>.</li>
                  <li><b>Cetak Lembar Rapor:</b> Rapor dicetak dengan kop resmi dan kolom tanda tangan kepala madrasah.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="panel panel-info">
              <div class="panel-heading"><b>4. Kedisiplinan &amp; Kesiswaan</b></div>
              <div class="panel-body" style="font-size:13px">
                <ol>
                  <li><b>Modal Poin:</b> Setiap siswa aktif memiliki 100 poin kedisiplinan awal.</li>
                  <li><b>Pencatatan:</b> Pelanggaran (Ringan, Sedang, Berat) dicatat oleh guru/wali kelas di menu <i>Pelanggaran Siswa</i>.</li>
                  <li><b>Otomasi Keterlambatan:</b> Terlambat &gt;15 menit otomatis memotong poin dari modul presensi.</li>
                  <li><b>Tindakan:</b> Sistem menampilkan status tindakan (Aman, Teguran, Peringatan Tertulis, Panggilan Orang Tua, Sanksi Berat).</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>
</div>
<?php include "footer.php"; ?>
